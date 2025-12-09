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
        return view('admin.invoices.show', compact('invoice'));
    }

    public function export(Invoice $invoice)
    {
        $export = new InvoiceExport($invoice);
        $fileName = 'hoa_don_' . $invoice->id . '_' . date('dmY_His') . '.xlsx';

        return response($export->generate(), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
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
        if ($invoice->isExtra()) {
            $bookingServices = BookingService::with(['service', 'phong'])
                ->where('dat_phong_id', $booking->id)
                ->where('invoice_id', $invoice->id)
                ->get();
        } else {
            // For regular invoices, load booking-level services (invoice_id NULL) to edit booking-scoped services
                // Include services that are either booking-scoped (invoice_id NULL)
                // or already attached to this invoice (invoice_id == $invoice->id).
                // This ensures when an invoice already has booking services moved to it,
                // the edit form still shows them for the PREPAID/main invoice.
                $bookingServices = BookingService::with(['service', 'phong'])
                    ->where('dat_phong_id', $booking->id)
                    ->where(function($q) use ($invoice) {
                        $q->whereNull('invoice_id')
                          ->orWhere('invoice_id', $invoice->id);
                    })
                    ->get();
        }
        
        // Build a JS-friendly structure grouped by service_id and date (same as DatPhongController)
        $bookingServicesServer = [];
        $roomMap = []; // map room_id => room label (so_phong/ten_phong)
        
        foreach ($bookingServices as $bs) {
            $svcId = $bs->service_id;
            if (!isset($bookingServicesServer[$svcId])) {
                $bookingServicesServer[$svcId] = [
                    'service' => $bs->service ? $bs->service->only(['id', 'name', 'price', 'unit']) : null,
                    'entries' => [], // each entry: ['ngay'=>'Y-m-d','so_luong'=>int,'phong_ids'=>[]]
                ];
            }
            
            $ngay = $bs->used_at ? (is_string($bs->used_at) ? date('Y-m-d', strtotime($bs->used_at)) : $bs->used_at->format('Y-m-d')) : ($booking->ngay_nhan ? date('Y-m-d', strtotime($booking->ngay_nhan)) : null);
            
            // Each BookingService record is 1 entry
            // If phong_id present => specific room, otherwise applies to all
            $phongIds = $bs->phong_id ? [$bs->phong_id] : [];
            
            $bookingServicesServer[$svcId]['entries'][] = [
                'ngay' => $ngay,
                'so_luong' => $bs->quantity ?? 1,
                'phong_ids' => $phongIds,
            ];
            
            if ($bs->phong) {
                $roomMap[$bs->phong->id] = $bs->phong->so_phong ?? $bs->phong->ten_phong ?? $bs->phong->id;
            }
        }
        
        // Ensure roomMap contains labels for all currently assigned rooms
        $assignedIdsForMapping = $booking->getPhongIds();
        if (!empty($assignedIdsForMapping)) {
            $missing = array_diff($assignedIdsForMapping, array_keys($roomMap));
            if (!empty($missing)) {
                $roomsForMap = Phong::whereIn('id', $missing)->get();
                foreach ($roomsForMap as $r) {
                    if (!isset($roomMap[$r->id])) {
                        $roomMap[$r->id] = $r->so_phong ?? $r->ten_phong ?? $r->id;
                    }
                }
            }
        }
        
        // Assigned room ids for this booking (use pivot helper)
        $assignedPhongIds = $booking ? $booking->getPhongIds() : [];

        // Pre-load all LoaiPhong objects used in room_types for efficient calculation in view
        $roomTypes = $booking->getRoomTypes(); // Returns array of room types
        $loaiPhongIds = array_column($roomTypes, 'loai_phong_id');
        $loaiPhongs = !empty($loaiPhongIds) ? \App\Models\LoaiPhong::whereIn('id', $loaiPhongIds)->get()->keyBy('id') : collect();

        // use the same status value as other controllers ('hoat_dong')
        $services = Service::where('status', 'hoat_dong')->get();
        
        // Get assigned rooms list with loaiPhong info for the JS
        // Use the booking pivot helper so we correctly read booking_rooms (or legacy phong_ids)
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
                        'ten_loai' => $room->loaiPhong ? $room->loaiPhong->ten_loai : 'N/A',
                    ];
                })
                ->toArray();
        }

        // Also include any rooms referenced directly by booking services (phong_id)
        // so the UI can render checkboxes for rooms that may not be attached via pivot.
        try {
            $bsRoomIds = $bookingServices->pluck('phong_id')->filter()->unique()->toArray();
            $existingIds = array_column($assignedRooms, 'id') ?: [];
            $missing = array_diff($bsRoomIds, $existingIds);
            if (!empty($missing)) {
                $more = Phong::whereIn('id', $missing)->with('loaiPhong:id,ten_loai')->get()->map(function($room) {
                    return [
                        'id' => $room->id,
                        'so_phong' => $room->so_phong,
                        'ten_loai' => $room->loaiPhong ? $room->loaiPhong->ten_loai : 'N/A',
                    ];
                })->toArray();
                $assignedRooms = array_merge($assignedRooms, $more);
            }
        } catch (\Throwable $e) {
            // ignore if bookingServices is not a collection or other issues
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
                    $unit = $lp->gia_khuyen_mai ?? $lp->gia_co_ban ?? 0;
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
                                $unit = $lp->gia_khuyen_mai ?? $lp->gia_co_ban ?? 0;
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
            'voucherDiscount'
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
        // If invoice is set to unpaid ('cho_thanh_toan'), mark invoice_type as PREPAID
        // Only change invoice_type for non-EXTRA invoices — EXTRA invoices must keep their type
        if ($invoice->trang_thai === 'cho_thanh_toan' && !$invoice->isExtra()) {
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

                // Set invoice total to services total ONLY for EXTRA invoices
                $invoice->tong_tien = $totalServices;
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

        // Recalculate totals
        if ($invoice->isExtra()) {
            // For EXTRA invoice, we already set tong_tien from services above if services_data provided.
            // If no services_data was provided, compute total from existing invoice-scoped booking_services.
            if (!$request->filled('services_data')) {
                $svcRows = BookingService::where('dat_phong_id', $booking->id)
                    ->where('invoice_id', $invoice->id)
                    ->get();
                $totalServices = $svcRows->reduce(function($carry, $item){
                    return $carry + (($item->quantity ?? 0) * ($item->unit_price ?? 0));
                }, 0);
                $invoice->tong_tien = $totalServices;
                $invoice->save();
            }
            // For EXTRA invoices, no need to update booking - they don't affect it
        } else {
            // For regular (non-EXTRA) invoices, recalculate the booking totals
            // This will update booking tien_phong, tien_dich_vu, giam_gia, tong_tien
            // And also sync the invoice with the same values via recalcTotal
            $booking = $booking->fresh();
            try {
                BookingPriceCalculator::recalcTotal($booking);
                // Reload both $booking and $invoice from DB to get the updated values
                $booking = $booking->fresh();
                $invoice = $invoice->fresh();
            } catch (\Throwable $e) {
                // Log and continue — do not block status update because of calc error
                Log::warning('Recalc booking total failed in InvoiceController:update: ' . $e->getMessage());
            }
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
                    
                    // Create rows based on room selection:
                    // - If specific rooms selected: one row per room
                    // - Otherwise: one row per assigned room (or NULL if none)
                    if (!empty($entryPhongIds)) {
                        // Specific rooms: create one row per room
                        foreach ($entryPhongIds as $phongId) {
                            $existing = BookingService::where('invoice_id', $new->id)
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
                                    'invoice_id' => $new->id,
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
                        // No specific rooms: apply to all assigned rooms
                        $assigned = $booking ? $booking->getPhongIds() : [];
                        if (is_string($assigned)) {
                            $assigned = json_decode($assigned, true) ?: [];
                        }
                        $assigned = is_array($assigned) ? array_filter($assigned) : [];

                        if (!empty($assigned)) {
                            // Create one row per assigned room
                            foreach ($assigned as $phongId) {
                                $existing = BookingService::where('invoice_id', $new->id)
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
                                        'invoice_id' => $new->id,
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
                            // No assigned rooms: create aggregate (phong_id = NULL)
                            $existing = BookingService::where('invoice_id', $new->id)
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
                                    'invoice_id' => $new->id,
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
        }

        // Set invoice total to services total ONLY (do not include room price)
        $new->tong_tien = $totalServices;
        $new->save();

        return redirect()->route('admin.invoices.show', $new->id)->with('success', 'Hóa đơn phát sinh đã được tạo và lưu (chỉ tính tiền dịch vụ).');
    }
    // (removed unused empty create() method)
}
