<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\BookingService;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\User;
use App\Models\Phong;
use App\Models\DatPhong;
use App\Exports\InvoiceExport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Services\BookingPriceCalculator;

class InvoiceController extends Controller
{

    public function index(Request $request)
    {
        $query = Invoice::with(['datPhong' => function($q) {
            $q->with('user', 'loaiPhong');
        }]);

        if ($request->filled('user_id')) {
            $query->whereHas('datPhong', function ($q) use ($request) {
                $q->where('nguoi_dung_id', $request->user_id);
            });
        }

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('trang_thai', $request->status);
        }

        // Filter by invoice type (EXTRA or PREPAID). Map legacy types to PREPAID.
        $typeFilter = $request->input('invoice_type', null);
        if ($typeFilter) {
            $t = strtoupper(trim($typeFilter));
            if ($t === 'EXTRA') {
                $query->where('invoice_type', 'EXTRA');
            } elseif ($t === 'PREPAID') {
                // treat several legacy/alternate values as PREPAID
                $query->whereIn('invoice_type', ['PREPAID', 'STANDARD', 'OFFLINE']);
            }
        }
        // Note: Removed default filter to show all invoices unless explicitly filtered

        // Get paginated results
        $invoices = $query->latest()->paginate(5);

        // Force reload each invoice from database to get latest tong_tien and clear any cached accessors
        $invoices->getCollection()->transform(function($inv) {
            $fresh = $inv->fresh();
            // Load relationships fresh to ensure accessors work correctly
            $fresh->load(['datPhong' => function($q) {
                $q->with('user', 'loaiPhong');
            }]);
            return $fresh;
        });

        $users = User::where('vai_tro', 'khach_hang')->get();

        return view('admin.invoices.index', compact('invoices', 'users'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['datPhong' => function($q) {
            $q->with('user', 'loaiPhong');
        }]);

        $booking = $invoice->datPhong;
        $services = [];
        $serviceTotal = 0;
        if ($invoice->invoice_type === 'EXTRA') {
            // Only show services for this invoice
            $services = BookingService::with('service', 'phong')
                ->where('invoice_id', $invoice->id)
                ->get();
            $serviceTotal = $services->sum(function($bs) {
                return ($bs->quantity ?? 0) * ($bs->unit_price ?? 0);
            });
        } else {
            // PREPAID: show all booking services (legacy: invoice_id NULL or matches main invoice)
            $services = BookingService::with('service', 'phong')
                ->where('dat_phong_id', $invoice->dat_phong_id)
                ->where(function($q) use ($invoice) {
                    $q->whereNull('invoice_id')->orWhere('invoice_id', $invoice->id);
                })
                ->get();
            $serviceTotal = $services->sum(function($bs) {
                return ($bs->quantity ?? 0) * ($bs->unit_price ?? 0);
            });
        }
        return view('admin.invoices.show', compact('invoice', 'services', 'serviceTotal'));
    }

    /**
     * Export a combined/summary printable view that includes the main invoice
     * and all PAID EXTRA invoices for the same booking (by invoice id).
     */
    public function exportCombined(Invoice $invoice)
    {
        // Ensure relationships are loaded
        $invoice->load(['datPhong' => function($q) {
            $q->with('user', 'loaiPhong');
        }]);

        // Only allow combined export for the main (non-EXTRA) invoice that is paid
        if ($invoice->isExtra() || $invoice->trang_thai !== 'da_thanh_toan') {
            return redirect()->route('admin.invoices.show', $invoice->id)
                ->with('error', 'Chỉ có thể xuất hóa đơn tổng cho hóa đơn chính đã thanh toán.');
        }

        // Find PAID EXTRA invoices for the same booking
        // Only include EXTRA invoices with trang_thai = 'da_thanh_toan'
        $extras = Invoice::where('dat_phong_id', $invoice->dat_phong_id)
            ->where('invoice_type', 'EXTRA')
            ->where('trang_thai', 'da_thanh_toan')
            ->where('id', '!=', $invoice->id)
            ->orderBy('ngay_tao', 'asc')
            ->get();

        // Combined total: main invoice total + sum of paid extras
        $combinedTotal = $invoice->tong_tien + $extras->sum('tong_tien');

        return view('admin.invoices.combined_print', compact('invoice', 'extras', 'combinedTotal'));
    }

    /**
     * Render the printable invoice view.
     */
    public function print(Invoice $invoice)
    {
        $invoice->load(['datPhong' => function ($q) {
            $q->with('user', 'loaiPhong');
        }]);

        return view('admin.invoices.print', compact('invoice'));
    }

    public function edit($id)
    {
        $invoice = Invoice::findOrFail($id);
        // Prevent editing if invoice already paid or refunded
        if (in_array($invoice->trang_thai, ['da_thanh_toan', 'hoan_tien'])) {
            return redirect()->route('admin.invoices.show', $invoice->id)
                ->with('error', 'Hóa đơn đã thanh toán/hoàn tiền không được phép chỉnh sửa.');
        }
        $invoice->load(['datPhong' => function($q) {
            $q->with('user', 'loaiPhong');
        }]);
        // Load services from booking. For EXTRA invoices, only load services linked to this invoice.
        $booking = $invoice->datPhong;
        $invoice = Invoice::findOrFail($id);
        // Prevent editing if invoice already paid or refunded
        if (in_array($invoice->trang_thai, ['da_thanh_toan', 'hoan_tien'])) {
            return redirect()->route('admin.invoices.show', $invoice->id);
        }
        $invoice->load(['datPhong' => function($q) {
            $q->with('user', 'loaiPhong');
        }]);
        $booking = $invoice->datPhong;
        $bookingServices = [];
        $roomTotalCalculated = 0;
        $currentServiceTotal = 0;
        $voucherDiscount = 0;
        $bookingServicesServer = [];
        if ($invoice->invoice_type === 'EXTRA') {
            // Only show/edit services for this invoice
            $bookingServices = BookingService::with(['service', 'phong'])
                ->where('invoice_id', $invoice->id)
                ->get();
            $currentServiceTotal = $bookingServices->sum(function($bs) {
                return ($bs->quantity ?? 0) * ($bs->unit_price ?? 0);
            });
            // Build JS structure for prefill: group by service_id and date
            foreach ($bookingServices as $bs) {
                $svcId = $bs->service_id;
                if (!isset($bookingServicesServer[$svcId])) {
                    $bookingServicesServer[$svcId] = [ 'entries' => [] ];
                }
                $bookingServicesServer[$svcId]['entries'][] = [
                    'ngay' => $bs->used_at ? date('Y-m-d', strtotime($bs->used_at)) : '',
                    'so_luong' => $bs->quantity,
                    'phong_ids' => $bs->phong_id ? [$bs->phong_id] : [],
                ];
            }
            // No room price for EXTRA
            $roomTotalCalculated = 0;
            $voucherDiscount = 0;
        } else {
            // PREPAID: show/edit all booking services (legacy: invoice_id NULL or matches main invoice)
            $bookingServices = BookingService::with(['service', 'phong'])
                ->where('dat_phong_id', $invoice->dat_phong_id)
                ->where(function($q) use ($invoice) {
                    $q->whereNull('invoice_id')->orWhere('invoice_id', $invoice->id);
                })
                ->get();
            $currentServiceTotal = $bookingServices->sum(function($bs) {
                return ($bs->quantity ?? 0) * ($bs->unit_price ?? 0);
            });
            // Build JS structure for prefill: group by service_id and date
            foreach ($bookingServices as $bs) {
                $svcId = $bs->service_id;
                if (!isset($bookingServicesServer[$svcId])) {
                    $bookingServicesServer[$svcId] = [ 'entries' => [] ];
                }
                $bookingServicesServer[$svcId]['entries'][] = [
                    'ngay' => $bs->used_at ? date('Y-m-d', strtotime($bs->used_at)) : '',
                    'so_luong' => $bs->quantity,
                    'phong_ids' => $bs->phong_id ? [$bs->phong_id] : [],
                ];
            }
            // Calculate room price for PREPAID
            $nights = 1;
            $roomTotalCalculated = 0;
            if ($booking && $booking->ngay_nhan && $booking->ngay_tra) {
                $nights = max(1, \Carbon\Carbon::parse($booking->ngay_tra)->diffInDays(\Carbon\Carbon::parse($booking->ngay_nhan)));
                
                // Calculate room total from room types
                $roomTypes = $booking->getRoomTypes();
                foreach ($roomTypes as $rt) {
                    $soLuong = $rt['so_luong'] ?? 1;
                    $loaiPhongId = $rt['loai_phong_id'] ?? null;
                    if ($loaiPhongId) {
                        $loaiPhong = \App\Models\LoaiPhong::find($loaiPhongId);
                        if ($loaiPhong) {
                            $giaCoBan = $loaiPhong->gia_co_ban ?? 0;
                            $giaKhuyenMai = $loaiPhong->gia_khuyen_mai ?? null;
                            $giaPhong = $giaKhuyenMai && $giaKhuyenMai < $giaCoBan ? $giaKhuyenMai : $giaCoBan;
                            $roomTotalCalculated += $giaPhong * $nights * $soLuong;
                        }
                    }
                }
            }
            // Calculate voucher discount if any
            $voucherDiscount = 0;
            if (!$invoice->isExtra() && $booking && $booking->voucher) {
                $voucher = $booking->voucher;
                $voucherPercent = floatval($voucher->gia_tri ?? 0);
                $voucherLoaiPhongId = $voucher->loai_phong_id ?? null;
                if ($voucherPercent > 0) {
                    $applicableTotal = 0;
                    if ($voucherLoaiPhongId) {
                        $roomTypes = $booking->getRoomTypes();
                        foreach ($roomTypes as $rt) {
                            $lpId = $rt['loai_phong_id'] ?? null;
                            if ($lpId && $lpId == $voucherLoaiPhongId) {
                                $loaiPhong = \App\Models\LoaiPhong::find($lpId);
                                if ($loaiPhong) {
                                    $giaCoBan = $loaiPhong->gia_co_ban ?? 0;
                                    $giaKhuyenMai = $loaiPhong->gia_khuyen_mai ?? null;
                                    $giaPhong = $giaKhuyenMai && $giaKhuyenMai < $giaCoBan ? $giaKhuyenMai : $giaCoBan;
                                    $soLuong = $rt['so_luong'] ?? 1;
                                    $applicableTotal += $giaPhong * $nights * $soLuong;
                                }
                            }
                        }
                    } else {
                        $applicableTotal = $roomTotalCalculated;
                    }
                    if ($applicableTotal > 0) {
                        if ($voucherPercent <= 100) {
                            $voucherDiscount = intval(round($applicableTotal * ($voucherPercent / 100)));
                        } else {
                            $voucherDiscount = intval(min(round($voucherPercent), $applicableTotal));
                        }
                    }
                }
            }
        }
        $services = Service::where('status', 'hoat_dong')->get();
        
        // Build assignedRooms with full room info for JS
        $assignedRooms = [];
        if ($booking) {
            $phongIds = $booking->getPhongIds();
            if (!empty($phongIds)) {
                $phongs = \App\Models\Phong::whereIn('id', $phongIds)
                    ->with('loaiPhong')
                    ->get();
                foreach ($phongs as $phong) {
                    $assignedRooms[] = [
                        'id' => $phong->id,
                        'so_phong' => $phong->so_phong,
                        'ten_loai' => $phong->loaiPhong ? $phong->loaiPhong->ten_loai : 'N/A',
                    ];
                }
            }
        }
        
        // Compute extra guest total from stay_guests (if any)
        $extraGuestTotal = 0;
        try {
            if ($booking) {
                $booking->loadMissing('stayGuests');
                $extraGuestTotal = $booking->stayGuests->sum(function ($g) {
                    return floatval($g->phi_them_nguoi ?? $g->extra_fee ?? $g->phu_phi_them ?? 0);
                });
            }
        } catch (\Throwable $e) {
            $extraGuestTotal = 0;
        }

        // Ensure supporting lookup data exists for view (avoid undefined variables)
        // Load active LoaiPhong collection keyed by id for quick lookups in calculations/views
        $loaiPhongs = \App\Models\LoaiPhong::where('trang_thai', 'hoat_dong')->get()->keyBy('id');

        // Assigned room ids and simple room map for JS/labels used in the edit view
        $assignedPhongIds = [];
        $roomMap = [];
        if ($booking) {
            $assignedPhongIds = $booking->getPhongIds();
            if (is_string($assignedPhongIds)) {
                $assignedPhongIds = json_decode($assignedPhongIds, true);
            }
            if (!is_array($assignedPhongIds)) {
                $assignedPhongIds = [];
            }
            if (!empty($assignedPhongIds)) {
                $phongs = \App\Models\Phong::whereIn('id', $assignedPhongIds)->get();
                foreach ($phongs as $p) {
                    $roomMap[$p->id] = $p->so_phong ?? $p->ten_phong ?? $p->id;
                }
            }
        }

        // Calculate room total properly from booking data (same as show.blade.php)
        $nights = 1;
        $roomTotalCalculated = 0;
        if ($invoice->isExtra()) {
            // For EXTRA invoices, do not include room price
            $roomTotalCalculated = 0;
        } else if ($booking && $booking->ngay_nhan && $booking->ngay_tra) {
            $checkin = \Carbon\Carbon::parse($booking->ngay_nhan);
            $checkout = \Carbon\Carbon::parse($booking->ngay_tra);
            $nights = max(1, $checkin->diffInDays($checkout));
            
            // Get room types and calculate room total using LoaiPhong promotional price
            $roomTypes = $booking->getRoomTypes();
            foreach ($roomTypes as $rt) {
                $soLuong = $rt['so_luong'] ?? 1;
                $loaiPhongId = $rt['loai_phong_id'] ?? null;
                $unit = 0;
                if ($loaiPhongId && isset($loaiPhongs[$loaiPhongId])) {
                    $lp = $loaiPhongs[$loaiPhongId];
                    if (is_object($lp)) {
                        $unit = $lp->gia_khuyen_mai ?? $lp->gia_co_ban ?? 0;
                    } else {
                        $unit = ($lp['gia_khuyen_mai'] ?? $lp['gia_co_ban'] ?? 0);
                    }
                }
                $roomTotalCalculated += $unit * $nights * $soLuong;
            }
        }
        
        // Get current service total from database
        $currentServiceTotal = 0;
        foreach ($bookingServices as $bs) {
            $currentServiceTotal += ($bs->quantity ?? 0) * ($bs->unit_price ?? 0);
        }

        // Calculate voucher discount on room subtotal (if invoice is not EXTRA)
        $voucherDiscount = 0;
        if (!$invoice->isExtra() && $booking && $booking->voucher) {
            $voucher = $booking->voucher;
            $voucherPercent = floatval($voucher->gia_tri ?? 0);
            $voucherLoaiPhongId = $voucher->loai_phong_id ?? null;

            if ($voucherPercent > 0) {
                // If voucher applies to a specific room type, compute subtotal only for that type
                $applicableTotal = 0;
                if ($voucherLoaiPhongId) {
                    $roomTypes = $booking->getRoomTypes();
                    foreach ($roomTypes as $rt) {
                        $lpId = $rt['loai_phong_id'] ?? null;
                        if ($lpId && $lpId == $voucherLoaiPhongId) {
                            $soLuong = $rt['so_luong'] ?? 1;
                            $unit = 0;
                            if ($lpId && isset($loaiPhongs[$lpId])) {
                                $lp = $loaiPhongs[$lpId];
                                if (is_object($lp)) {
                                    $unit = $lp->gia_khuyen_mai ?? $lp->gia_co_ban ?? 0;
                                } else {
                                    $unit = ($lp['gia_khuyen_mai'] ?? $lp['gia_co_ban'] ?? 0);
                                }
                            }
                            $applicableTotal += $unit * $soLuong * $nights;
                        }
                    }
                } else {
                    // Voucher applies to all rooms
                    $applicableTotal = $roomTotalCalculated;
                }

                if ($applicableTotal > 0) {
                    if ($voucherPercent <= 100) {
                        // Percentage discount
                        $voucherDiscount = intval(round($applicableTotal * ($voucherPercent / 100)));
                    } else {
                        // Fixed amount discount (cap at applicable total)
                        $voucherDiscount = intval(min(round($voucherPercent), $applicableTotal));
                    }
                }
            }
        }
        
        return view('admin.invoices.edit', compact(
            'invoice',
            'bookingServices',
            'bookingServicesServer',
            'services',
            'booking',
            'loaiPhongs',
            'assignedPhongIds',
            'roomMap',
            'assignedRooms',
            'nights',
            'roomTotalCalculated',
            'currentServiceTotal',
            'voucherDiscount',
            'extraGuestTotal'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'trang_thai' => 'required|in:cho_thanh_toan,da_thanh_toan,hoan_tien',
        ]);

        $invoice = Invoice::findOrFail($id);
        // Do not allow updating paid/refunded invoices
        if (in_array($invoice->trang_thai, ['da_thanh_toan', 'hoan_tien'])) {
            return redirect()->route('admin.invoices.show', $invoice->id)
                ->with('error', 'Hóa đơn đã thanh toán/hoàn tiền không được phép chỉnh sửa.');
        }
        $booking = $invoice->datPhong;

        // Update invoice status FIRST
        $invoice->trang_thai = $request->input('trang_thai');
        // Ensure invoice_type matches the invoice kind:
        // - EXTRA invoices should keep/remain 'EXTRA'
        // - Non-EXTRA (main) invoices should be set to 'PREPAID'
        if ($invoice->isExtra()) {
            $invoice->invoice_type = 'EXTRA';
        } else {
            $invoice->invoice_type = 'PREPAID';
        }
        $invoice->save();

        // Handle services if provided. Use has() / input check instead of filled()
        $servicesData = $request->input('services_data', null);
        if ($servicesData !== null) {
            // Log incoming services_data size for debugging
            try {
                Log::info('InvoiceController:update - services_data keys', ['invoice_id' => $invoice->id, 'count' => is_array($servicesData) ? count($servicesData) : 0]);
            } catch (\Throwable $e) {}

            if ($invoice->isExtra()) {
                // For EXTRA invoices: only modify services that belong to this invoice
                BookingService::where('dat_phong_id', $booking->id)
                    ->where('invoice_id', $invoice->id)
                    ->delete();

                $totalServices = 0;
                // Create new invoice-scoped service entries
                foreach ($servicesData as $svcId => $data) {
                    $service = Service::find($svcId);
                    if (!$service) continue;
                    $entries = $data['entries'] ?? [];
                    foreach ($entries as $entry) {
                        $ngay = $entry['ngay'] ?? '';
                        $qty = $entry['so_luong'] ?? 0;
                        if (!$ngay || $qty <= 0) continue;
                        
                        // Support per-entry room selection (phong_ids or phong_id)
                        $entryPhongIds = [];
                        if (isset($entry['phong_ids']) && is_array($entry['phong_ids'])) {
                            $entryPhongIds = array_filter($entry['phong_ids']);
                        } elseif (isset($entry['phong_id'])) {
                            $entryPhongIds = array_filter([$entry['phong_id']]);
                        }
                        
                        // If specific rooms are selected, create or accumulate one record per room
                        if (!empty($entryPhongIds)) {
                            foreach ($entryPhongIds as $phongId) {
                                $existing = BookingService::where('invoice_id', $invoice->id)
                                    ->where('dat_phong_id', $booking->id)
                                    ->where('service_id', $service->id)
                                    ->where('used_at', $ngay)
                                    ->where('phong_id', $phongId)
                                    ->first();

                                if ($existing) {
                                    $existing->quantity = ($existing->quantity ?? 0) + $qty;
                                    $existing->unit_price = $service->price ?? $existing->unit_price;
                                    $existing->save();
                                } else {
                                    BookingService::create([
                                        'invoice_id' => $invoice->id,
                                        'dat_phong_id' => $booking->id,
                                        'service_id' => $service->id,
                                        'quantity' => $qty,
                                        'unit_price' => $service->price ?? 0,
                                        'used_at' => $ngay,
                                        'phong_id' => $phongId,
                                    ]);
                                }
                                $totalServices += ($qty * ($service->price ?? 0));
                            }
                        } else {
                            // No specific rooms: apply to all assigned rooms (create one row per room)
                            $assigned = $booking ? $booking->getPhongIds() : [];
                            if (is_string($assigned)) {
                                $assigned = json_decode($assigned, true) ?: [];
                            }
                            $assigned = is_array($assigned) ? array_filter($assigned) : [];

                            if (!empty($assigned)) {
                                // Create one record per assigned room
                                foreach ($assigned as $phongId) {
                                    $existing = BookingService::where('invoice_id', $invoice->id)
                                        ->where('dat_phong_id', $booking->id)
                                        ->where('service_id', $service->id)
                                        ->where('used_at', $ngay)
                                        ->where('phong_id', $phongId)
                                        ->first();

                                    if ($existing) {
                                        $existing->quantity = ($existing->quantity ?? 0) + $qty;
                                        $existing->unit_price = $service->price ?? $existing->unit_price;
                                        $existing->save();
                                    } else {
                                        BookingService::create([
                                            'invoice_id' => $invoice->id,
                                            'dat_phong_id' => $booking->id,
                                            'service_id' => $service->id,
                                            'quantity' => $qty,
                                            'unit_price' => $service->price ?? 0,
                                            'used_at' => $ngay,
                                            'phong_id' => $phongId,
                                        ]);
                                    }
                                    $totalServices += ($qty * ($service->price ?? 0));
                                }
                            } else {
                                // No assigned rooms: still create one aggregate record with phong_id = NULL
                                $existing = BookingService::where('invoice_id', $invoice->id)
                                    ->where('dat_phong_id', $booking->id)
                                    ->where('service_id', $service->id)
                                    ->where('used_at', $ngay)
                                    ->whereNull('phong_id')
                                    ->first();

                                if ($existing) {
                                    $existing->quantity = ($existing->quantity ?? 0) + $qty;
                                    $existing->unit_price = $service->price ?? $existing->unit_price;
                                    $existing->save();
                                } else {
                                    BookingService::create([
                                        'invoice_id' => $invoice->id,
                                        'dat_phong_id' => $booking->id,
                                        'service_id' => $service->id,
                                        'quantity' => $qty,
                                        'unit_price' => $service->price ?? 0,
                                        'used_at' => $ngay,
                                        'phong_id' => null,
                                    ]);
                                }
                                $totalServices += ($qty * ($service->price ?? 0));
                            }
                        }
                    }
                }

                // Compute extra-guest amount in one place and use it once to avoid double-counting.
                $invoiceExtraGuest = 0;
                try {
                    if (\Illuminate\Support\Facades\Schema::hasTable('invoice_items')) {
                        $invoiceExtraGuest = \App\Models\InvoiceItem::where('invoice_id', $invoice->id)
                            ->where('type', 'extra_guest')
                            ->sum('amount');
                    }
                } catch (\Throwable $e) {
                    $invoiceExtraGuest = 0;
                }

                // Allow admin to pass 'phi_phat_sinh' (damage) or 'phi_them_nguoi' (extra guests)
                $submittedPhiPhatSinh = $request->input('phi_phat_sinh', null);
                $submittedPhiThemNguoi = $request->input('phi_them_nguoi', null);

                // Save damage fee separately but DO NOT add it to the service total
                if ($submittedPhiPhatSinh !== null) {
                    $invoice->phi_phat_sinh = $submittedPhiPhatSinh;
                }

                // Decide authoritative extra guest amount (preference order):
                // 1) explicitly submitted value in form
                // 2) invoice_items of type 'extra_guest' (detailed lines)
                // 3) existing invoice.phi_them_nguoi (legacy / persisted)
                // 4) aggregate from booking stay guests
                $extraGuestAmount = 0;
                if ($submittedPhiThemNguoi !== null) {
                    $extraGuestAmount = floatval($submittedPhiThemNguoi);
                } elseif ($invoiceExtraGuest > 0) {
                    $extraGuestAmount = floatval($invoiceExtraGuest);
                } elseif (!empty($invoice->phi_them_nguoi)) {
                    $extraGuestAmount = floatval($invoice->phi_them_nguoi);
                } else {
                    $extraGuestAmount = floatval($extraGuestTotal ?? 0);
                }

                // Persist the chosen extra guest amount on the invoice (do not double-add)
                $invoice->phi_them_nguoi = $extraGuestAmount;

                // Clarify semantics: For EXTRA invoices the official total should be
                // service subtotal + extra guest fee. Damage fees are stored on
                // invoice->phi_phat_sinh but NOT included in the main total here
                // (avoids accidental double-counting). Store service subtotal
                // into tien_dich_vu and compute tong_tien accordingly.
                $invoice->tien_dich_vu = $totalServices; // only service rows
                $invoice->tong_tien = ($totalServices + floatval($invoice->phi_them_nguoi ?? 0));
                $invoice->save();
            } else {
                // Existing behavior for non-EXTRA invoices (booking-scoped services)
                // Delete old booking-level and any existing invoice-scoped entries for this booking
                // to avoid duplicate rows when re-saving the invoice.
                $deletedCount = BookingService::where('dat_phong_id', $booking->id)
                    ->where(function($q) use ($invoice) {
                        $q->whereNull('invoice_id')
                          ->orWhere('invoice_id', $invoice->id);
                    })->delete();
                
                Log::info('InvoiceController:update - Deleted services', ['booking_id' => $booking->id, 'invoice_id' => $invoice->id, 'count' => $deletedCount]);
                $totalServices = 0;
                // Create new booking-scoped service entries. Support per-entry room selection
                foreach ($servicesData as $svcId => $data) {
                    $service = Service::find($svcId);
                    if (!$service) continue;

                    $entries = $data['entries'] ?? [];
                    foreach ($entries as $entry) {
                        $ngay = $entry['ngay'] ?? '';
                        $qty = $entry['so_luong'] ?? 0;
                        if (!$ngay || $qty <= 0) continue;


                        // Support per-entry room selection (phong_ids or phong_id)
                        $entryPhongIds = [];
                        if (isset($entry['phong_ids']) && is_array($entry['phong_ids'])) {
                            $entryPhongIds = array_filter($entry['phong_ids']);
                        } elseif (isset($entry['phong_id'])) {
                            $entryPhongIds = array_filter([$entry['phong_id']]);
                        }

                        Log::info('InvoiceController:update - Processing service entry', [
                            'service_id' => $svcId,
                            'service_price' => $service->price,
                            'ngay' => $ngay,
                            'qty' => $qty,
                            'entryPhongIds' => $entryPhongIds,
                        ]);

                        // Create rows based on room selection:
                        // - If specific rooms selected: one row per room
                        // - Otherwise: one row per assigned room (or NULL if none)
                        // Always check for existing (same date, room, service) and accumulate quantity
                        if (!empty($entryPhongIds)) {
                            // Specific rooms selected: create one row per room
                            foreach ($entryPhongIds as $phongId) {
                                $existing = BookingService::where('dat_phong_id', $booking->id)
                                    ->where('service_id', $service->id)
                                    ->where('used_at', $ngay)
                                    ->where('invoice_id', $invoice->id)
                                    ->where('phong_id', $phongId)
                                    ->first();

                                if ($existing) {
                                    $existing->quantity = ($existing->quantity ?? 0) + $qty;
                                    $existing->unit_price = $service->price ?? $existing->unit_price;
                                    $existing->save();
                                } else {
                                    BookingService::create([
                                        'dat_phong_id' => $booking->id,
                                        'service_id' => $service->id,
                                        'used_at' => $ngay,
                                        'invoice_id' => $invoice->id,
                                        'phong_id' => $phongId,
                                        'quantity' => $qty,
                                        'unit_price' => $service->price ?? 0,
                                    ]);
                                }
                                $totalServices += ($qty * ($service->price ?? 0));
                            }
                        } else {
                            // No specific rooms: apply to all assigned rooms
                            $assigned = $booking ? $booking->getPhongIds() : [];
                            if (is_string($assigned)) {
                                $assigned = json_decode($assigned, true) ?: [];
                            }
                            $assigned = is_array($assigned) ? array_filter($assigned) : [];

                            Log::info('InvoiceController:update - Global mode assigned rooms', ['booking_id' => $booking->id, 'assigned' => $assigned]);

                            if (!empty($assigned)) {
                                // Create one row per assigned room
                                foreach ($assigned as $phongId) {
                                    $existing = BookingService::where('dat_phong_id', $booking->id)
                                        ->where('service_id', $service->id)
                                        ->where('used_at', $ngay)
                                        ->where('invoice_id', $invoice->id)
                                        ->where('phong_id', $phongId)
                                        ->first();

                                    if ($existing) {
                                        $existing->quantity = ($existing->quantity ?? 0) + $qty;
                                        $existing->unit_price = $service->price ?? $existing->unit_price;
                                        $existing->save();
                                    } else {
                                        BookingService::create([
                                            'dat_phong_id' => $booking->id,
                                            'service_id' => $service->id,
                                            'used_at' => $ngay,
                                            'invoice_id' => $invoice->id,
                                            'phong_id' => $phongId,
                                            'quantity' => $qty,
                                            'unit_price' => $service->price ?? 0,
                                        ]);
                                    }
                                    $totalServices += ($qty * ($service->price ?? 0));
                                }
                            } else {
                                // No assigned rooms: create aggregate (phong_id = NULL)
                                $existing = BookingService::where('dat_phong_id', $booking->id)
                                    ->where('service_id', $service->id)
                                    ->where('used_at', $ngay)
                                    ->where('invoice_id', $invoice->id)
                                    ->whereNull('phong_id')
                                    ->first();

                                if ($existing) {
                                    $existing->quantity = ($existing->quantity ?? 0) + $qty;
                                    $existing->unit_price = $service->price ?? $existing->unit_price;
                                    $existing->save();
                                } else {
                                    BookingService::create([
                                        'dat_phong_id' => $booking->id,
                                        'service_id' => $service->id,
                                        'used_at' => $ngay,
                                        'invoice_id' => $invoice->id,
                                        'phong_id' => null,
                                        'quantity' => $qty,
                                        'unit_price' => $service->price ?? 0,
                                    ]);
                                }
                                $totalServices += ($qty * ($service->price ?? 0));
                            }
                        }
                    }
                }
                
                Log::info('InvoiceController:update - Total services calculated', [
                    'booking_id' => $booking->id,
                    'invoice_id' => $invoice->id,
                    'totalServices' => $totalServices,
                ]);
            }
        }

        // Handle total calculation for both invoice types
        if ($invoice->isExtra()) {
            // For EXTRA invoice, we already set tong_tien from services above if services_data provided.
            // If no services_data was provided, compute total from existing invoice-scoped booking_services.
            if (!$request->filled('services_data')) {
                $svcRows = BookingService::where('dat_phong_id', $booking->id)
                    ->where('invoice_id', $invoice->id)
                    ->get();
                $serviceSum = $svcRows->reduce(function($carry, $item){
                    return $carry + (($item->quantity ?? 0) * ($item->unit_price ?? 0));
                }, 0);
                // Use stored phi_them_nguoi if present (may have been provided via form or invoice_items)
                $invoice->tien_dich_vu = $serviceSum;
                $invoice->tong_tien = ($serviceSum + floatval($invoice->phi_them_nguoi ?? 0));
                $invoice->save();
            }
            // If the form submitted a tong_tien (for example editing an EXTRA invoice that only
            // contains invoice_items instead of BookingService rows), prefer the submitted total
            // if services_data wasn't present. This allows admins to edit the total price directly
            // in the form even when the invoice doesn't have BookingService rows.
            if (!$request->filled('services_data') && $request->filled('tong_tien')) {
                $submittedTotal = $request->input('tong_tien', 0);
                $invoice->tong_tien = $submittedTotal;
                // Also store service and extra-guest breakdown if provided in the form
                $invoice->tien_dich_vu = $request->input('tien_dich_vu', 0);
                $invoice->phi_phat_sinh = $request->input('phi_phat_sinh', 0);
                $invoice->save();
                try { Log::info('InvoiceController:update - EXTRA invoice used submitted tong_tien', ['invoice_id' => $invoice->id, 'submitted' => $submittedTotal]); } catch (\Throwable $e) {}
            }
            // For EXTRA invoices, no need to update booking - they don't affect it
        } else {
            // For regular (non-EXTRA) invoices:
            // Use the form-submitted tong_tien (from client JS calculation) instead of recalculating
            // This prevents double-counting of room prices
            if ($request->filled('tong_tien')) {
                $submittedTotal = $request->input('tong_tien', 0);
                $invoice->tong_tien = $submittedTotal;
                $invoice->giam_gia = $request->input('giam_gia', 0);
                $invoice->tien_phong = $request->input('tien_phong', 0);
                $invoice->tien_dich_vu = $request->input('tien_dich_vu', 0);
                $invoice->phi_phat_sinh = $request->input('phi_phat_sinh', 0);
                
                // Update booking totals from invoice (sync invoice values to booking)
                // Extract room price, service price from invoice data
                // Note: giam_gia (voucher discount) belongs to Invoice table, not DatPhong
                // Use update() to let Laravel handle type casting properly
                $booking->update([
                    'tien_phong' => $request->input('tien_phong', 0),
                    'tien_dich_vu' => $request->input('tien_dich_vu', 0),
                    'phi_phat_sinh' => $request->input('phi_phat_sinh', 0),
                    'tong_tien' => $submittedTotal,
                ]);
                
                Log::info('InvoiceController:update - Using submitted totals', [
                    'invoice_id' => $invoice->id,
                    'booking_id' => $booking->id,
                    'tong_tien' => $submittedTotal,
                    'tien_phong' => $request->input('tien_phong', 0),
                    'tien_dich_vu' => $request->input('tien_dich_vu', 0),
                ]);
            } else {
                // If no tong_tien in request, fall back to recalculating
                try {
                    BookingPriceCalculator::recalcTotal($booking);
                    $booking = $booking->fresh();
                    $invoice->tong_tien = $booking->tong_tien;
                    
                    Log::info('InvoiceController:update - Fallback to recalcTotal', [
                        'invoice_id' => $invoice->id,
                        'booking_id' => $booking->id,
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('Recalc booking total failed in InvoiceController:update: ' . $e->getMessage());
                }
            }
            $invoice->save();
        }

        // Đồng bộ trạng thái đặt phòng khi hóa đơn đã thanh toán
        if ($invoice->trang_thai === 'da_thanh_toan' && $invoice->datPhong) {
            if ($invoice->datPhong->trang_thai === 'cho_xac_nhan') {
                $invoice->datPhong->validateStatusTransition('da_xac_nhan');
                $invoice->datPhong->trang_thai = 'da_xac_nhan';
                $invoice->datPhong->save();
            }
        }

        return redirect()->route('admin.invoices.index')->with('success', 'Cập nhật hóa đơn thành công.');
    }

    /**
     * Show form to create an EXTRA invoice (do not persist until user confirms)
     */
    public function createExtra(Invoice $invoice)
    {
        // Only allow creating EXTRA invoice from a paid invoice
        if ($invoice->trang_thai !== 'da_thanh_toan') {
            return redirect()->route('admin.invoices.show', $invoice->id)
                ->with('error', 'Chỉ có thể tạo hóa đơn phát sinh từ hóa đơn đã thanh toán.');
        }

        $booking = $invoice->datPhong;
        $services = Service::where('status', 'hoat_dong')->get();
        
        // Preload existing booking services so the create-extra UI can show already added services
        // Only show services that are part of the booking (not already assigned to another invoice)
        $bookingServices = BookingService::with('service')
            ->where('dat_phong_id', $booking->id)
            ->whereNull('invoice_id')
            ->get()
            ->groupBy('service_id')
            ->map(function($group) {
                return $group->map(function($row) {
                    return ['ngay' => \Carbon\Carbon::parse($row->used_at)->format('Y-m-d'), 'so_luong' => $row->quantity];
                })->values()->toArray();
            })->toArray();

        // Get assigned rooms for this booking with their types (use pivot helper)
        $assignedRooms = [];
        $phongIds = $booking ? $booking->getPhongIds() : [];
        if (is_string($phongIds)) {
            $phongIds = json_decode($phongIds, true);
        }
        if (is_array($phongIds) && !empty($phongIds)) {
            $assignedRooms = Phong::whereIn('id', $phongIds)
                ->with('loaiPhong:id,ten_loai')
                ->get()
                ->map(function($room) {
                    return [
                        'id' => $room->id,
                        'so_phong' => $room->so_phong,
                        'ten_loai' => $room->loaiPhong ? $room->loaiPhong->ten_loai : 'N/A'
                    ];
                })
                ->toArray();
        }

        return view('admin.invoices.create_extra', compact('invoice', 'booking', 'services', 'bookingServices', 'assignedRooms'));
    }

    /**
     * Store the confirmed EXTRA invoice and the selected service entries
     * For EXTRA invoices: only count services selected, DO NOT include room price
     * Services are linked to the invoice (not the booking) to keep them separate
     */
    public function storeExtra(Request $request, Invoice $invoice)
    {
        // Only allow creating EXTRA invoice from a paid invoice
        if ($invoice->trang_thai !== 'da_thanh_toan') {
            return redirect()->route('admin.invoices.show', $invoice->id)
                ->with('error', 'Chỉ có thể tạo hóa đơn phát sinh từ hóa đơn đã thanh toán.');
        }

        $booking = $invoice->datPhong;
        if (!$booking) {
            return redirect()->route('admin.invoices.show', $invoice->id)->with('error', 'Đặt phòng không tồn tại.');
        }

        $totalServices = 0;

        // Create a new invoice record now that the admin confirmed
        $new = Invoice::create([
            'dat_phong_id' => $booking->id,
            'tong_tien' => 0,
            'phuong_thuc' => null,
            'trang_thai' => 'cho_thanh_toan',
            'invoice_type' => 'EXTRA',
            'ngay_tao' => now(),
        ]);

        // Chỉ tạo mới dịch vụ phát sinh cho hóa đơn EXTRA, không di chuyển dịch vụ booking-level
        if ($request->filled('services_data')) {
            $servicesData = $request->input('services_data', []);
            Log::info('storeExtra called - services_data received', ['invoice_id' => $invoice->id, 'services_count' => is_array($servicesData) ? count($servicesData) : 0]);

            // Use DB transaction to ensure atomicity
            DB::beginTransaction();
            try {
                foreach ($servicesData as $svcId => $data) {
                    Log::info('Processing service block', ['svcId' => $svcId, 'data' => $data]);
                    $service = Service::find($svcId);
                    if (!$service) continue;
                    $entries = $data['entries'] ?? [];
                    foreach ($entries as $entry) {
                        Log::info('Processing service entry', ['svcId' => $svcId, 'entry' => $entry]);
                        $ngay = $entry['ngay'] ?? '';
                        $qty = $entry['so_luong'] ?? 0;
                        if (!$ngay || $qty <= 0) continue;

                        // Support per-entry room selection (phong_ids or phong_id)
                        $entryPhongIds = [];
                        if (isset($entry['phong_ids']) && is_array($entry['phong_ids'])) {
                            $entryPhongIds = array_filter($entry['phong_ids']);
                        } elseif (isset($entry['phong_id'])) {
                            $entryPhongIds = array_filter([$entry['phong_id']]);
                        }

                        // Normalize assigned rooms fallback to booking pivot
                        if (empty($entryPhongIds)) {
                            $assigned = $booking ? $booking->getPhongIds() : [];
                            if (is_string($assigned)) {
                                $assigned = json_decode($assigned, true) ?: [];
                            }
                            $entryPhongIds = is_array($assigned) ? array_filter($assigned) : [];
                            Log::info('storeExtra - normalized phong_ids from booking', ['booking_id' => $booking->id, 'phong_ids' => $entryPhongIds]);
                        }

                        // Validate that we have rooms to assign to
                        if (empty($entryPhongIds)) {
                            Log::error('storeExtra - no rooms assigned to booking, cannot create service rows', ['booking_id' => $booking->id, 'service_id' => $svcId, 'entry' => $entry]);
                            DB::rollBack();
                            return redirect()->route('admin.invoices.show', $invoice->id)->with('error', 'Lỗi: Đặt phòng không có phòng được gán. Vui lòng kiểm tra lại thông tin đặt phòng.');
                        }

                        // If we have rooms, create per-room service entries
                        if (!empty($entryPhongIds)) {
                            foreach ($entryPhongIds as $phongId) {
                                // Try to find an existing row with exact same: invoice_id + booking_id + service_id + used_at + phong_id
                                // This allows: cùng ngày cùng phòng => cộng dồn số lượng
                                $existing = BookingService::where('invoice_id', $new->id)
                                    ->where('dat_phong_id', $booking->id)
                                    ->where('service_id', $service->id)
                                    ->where('used_at', $ngay) // exact match, not date compare
                                    ->where('phong_id', $phongId)
                                    ->first();

                                if ($existing) {
                                    // Same invoice + date + room + service => cộng dồn số lượng
                                    $existing->quantity = ($existing->quantity ?? 0) + $qty;
                                    $existing->unit_price = $service->price ?? $existing->unit_price;
                                    $existing->save();
                                    Log::info('storeExtra - Merged (accumulated qty)', [
                                        'bs_id' => $existing->id,
                                        'invoice_id' => $new->id,
                                        'svc_id' => $service->id,
                                        'phong_id' => $phongId,
                                        'ngay' => $ngay,
                                        'qty_added' => $qty,
                                        'qty_total' => $existing->quantity
                                    ]);
                                } else {
                                    // New row: different invoice OR different date OR different room
                                    try {
                                        $bs = BookingService::create([
                                            'invoice_id' => $new->id,
                                            'dat_phong_id' => $booking->id,
                                            'service_id' => $service->id,
                                            'quantity' => $qty,
                                            'unit_price' => $service->price ?? 0,
                                            'used_at' => $ngay,
                                            'phong_id' => $phongId,
                                        ]);
                                        Log::info('storeExtra - Created new row', [
                                            'bs_id' => $bs->id ?? null,
                                            'invoice_id' => $new->id,
                                            'svc_id' => $service->id,
                                            'phong_id' => $phongId,
                                            'ngay' => $ngay,
                                            'qty' => $qty
                                        ]);
                                    } catch (QueryException $qe) {
                                        // Race condition: another request inserted same row; merge instead
                                        Log::warning('storeExtra - Create failed, attempting race-merge', [
                                            'error' => $qe->getMessage(),
                                            'code' => $qe->getCode(),
                                            'invoice_id' => $new->id,
                                            'svc_id' => $service->id
                                        ]);
                                        if ($qe->getCode() == 23000) {
                                            $race = BookingService::where('invoice_id', $new->id)
                                                ->where('dat_phong_id', $booking->id)
                                                ->where('service_id', $service->id)
                                                ->where('used_at', $ngay)
                                                ->where('phong_id', $phongId)
                                                ->first();
                                            if ($race) {
                                                $race->quantity = ($race->quantity ?? 0) + $qty;
                                                $race->unit_price = $service->price ?? $race->unit_price;
                                                $race->save();
                                                Log::info('storeExtra - Race-merged', ['bs_id' => $race->id, 'qty_added' => $qty]);
                                            }
                                        } else {
                                            throw $qe;
                                        }
                                    }
                                }
                                $totalServices += ($qty * ($service->price ?? 0));
                            }
                        }
                    }
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Failed to store extra invoice services: ' . $e->getMessage());
                return redirect()->route('admin.invoices.show', $invoice->id)->with('error', 'Lỗi khi lưu dịch vụ phát sinh: ' . $e->getMessage());
            }
        }

        // Set invoice totals: service subtotal and total = services + extra_guest (if provided)
        $new->tien_dich_vu = $totalServices;
        $submittedExtraGuest = $request->input('phi_them_nguoi', null);
        if ($submittedExtraGuest !== null) {
            $new->phi_them_nguoi = floatval($submittedExtraGuest);
        }
        $new->tong_tien = ($totalServices + floatval($new->phi_them_nguoi ?? 0));
        $new->save();

        return redirect()->route('admin.invoices.show', $new->id)->with('success', 'Hóa đơn phát sinh đã được tạo và lưu (chỉ tính tiền dịch vụ).');
    }
    // (removed unused empty create() method)
}