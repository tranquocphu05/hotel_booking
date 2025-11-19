<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\BookingService;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\User;
use App\Exports\InvoiceExport;
use Illuminate\Support\Facades\Hash;
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
            $bookingServices = BookingService::with('service')
                ->where('dat_phong_id', $booking->id)
                ->where('invoice_id', $invoice->id)
                ->get();
        } else {
            // For regular invoices, load booking-level services (invoice_id NULL) to edit booking-scoped services
            $bookingServices = BookingService::with('service')
                ->where('dat_phong_id', $booking->id)
                ->whereNull('invoice_id')
                ->get();
        }
        
        // Pre-load all LoaiPhong objects used in room_types for efficient calculation in view
        $roomTypes = $booking->getRoomTypes();
        $loaiPhongIds = array_column($roomTypes, 'loai_phong_id');
        $loaiPhongs = \App\Models\LoaiPhong::whereIn('id', $loaiPhongIds)->get()->keyBy('id');
        
        // use the same status value as other controllers ('hoat_dong')
        $services = Service::where('status', 'hoat_dong')->get();
        return view('admin.invoices.edit', compact('invoice', 'bookingServices', 'services', 'booking', 'loaiPhongs'));
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
                        try {
                            BookingService::create([
                                'invoice_id' => $invoice->id,
                                'dat_phong_id' => $booking->id,
                                'service_id' => $service->id,
                                'quantity' => $qty,
                                'unit_price' => $service->price,
                                'used_at' => $ngay,
                            ]);
                        } catch (\Throwable $ex) {
                            Log::error('BookingService create failed in InvoiceController:update', ['invoice_id' => $invoice->id, 'service_id' => $service->id, 'used_at' => $ngay, 'error' => $ex->getMessage()]);
                        }
                        $totalServices += ($qty * ($service->price ?? 0));
                    }
                }

                // Set invoice total to services total ONLY for EXTRA invoices
                $invoice->tong_tien = $totalServices;
                $invoice->save();
            } else {
                // Existing behavior for non-EXTRA invoices (booking-scoped services)
                // Delete old booking-level services BEFORE creating new ones
                BookingService::where('dat_phong_id', $booking->id)->delete();

                // Create new service entries
                foreach ($servicesData as $svcId => $data) {
                    $service = Service::find($svcId);
                    if (!$service) continue;

                    $entries = $data['entries'] ?? [];
                    foreach ($entries as $entry) {
                        $ngay = $entry['ngay'] ?? '';
                        $qty = $entry['so_luong'] ?? 0;
                        if (!$ngay || $qty <= 0) continue;

                        BookingService::create([
                            'dat_phong_id' => $booking->id,
                            'service_id' => $service->id,
                            'quantity' => $qty,
                            'unit_price' => $service->price,
                            'used_at' => $ngay,
                        ]);
                    }
                }
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
        } else {
            // Recalculate booking totals using central service (will include services we just saved)
            $booking = $booking->fresh();
            try {
                BookingPriceCalculator::recalcTotal($booking);
                // Reload both $booking and $invoice from DB to get the updated values
                $booking = $booking->fresh();
                $invoice = $invoice->fresh();
                // Get the latest tong_tien from booking (which was updated by recalcTotal)
                if ($booking->tong_tien !== $invoice->tong_tien) {
                    $invoice->tong_tien = $booking->tong_tien;
                    $invoice->save();
                    // Debug: Log the values for verification
                    Log::info('Invoice ' . $invoice->id . ' updated: tong_tien=' . $invoice->tong_tien . ', booking tong_tien=' . $booking->tong_tien);
                }
            } catch (\Throwable $e) {
                // Log and continue — do not block status update because of calc error
                Log::warning('Recalc booking total failed in InvoiceController:update: ' . $e->getMessage());
            }
        }

        // Đồng bộ trạng thái đặt phòng khi hóa đơn đã thanh toán
        if ($invoice->trang_thai === 'da_thanh_toan' && $invoice->datPhong) {
            if ($invoice->datPhong->trang_thai === 'cho_xac_nhan') {
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

        return view('admin.invoices.create_extra', compact('invoice', 'booking', 'services', 'bookingServices'));
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
                    // Luôn tạo mới dịch vụ cho hóa đơn EXTRA
                    try {
                        BookingService::create([
                            'invoice_id' => $new->id,
                            'dat_phong_id' => $booking->id,
                            'service_id' => $service->id,
                            'quantity' => $qty,
                            'unit_price' => $service->price ?? 0,
                            'used_at' => $ngay,
                        ]);
                    } catch (\Throwable $e) {
                        // Nếu trùng unique index, cộng dồn số lượng
                        $fallback = BookingService::where('dat_phong_id', $booking->id)
                            ->where('service_id', $service->id)
                            ->where('used_at', $ngay)
                            ->where('invoice_id', $new->id)
                            ->first();
                        if ($fallback) {
                            $fallback->quantity = ($fallback->quantity ?? 0) + $qty;
                            $fallback->unit_price = $service->price ?? $fallback->unit_price;
                            $fallback->save();
                        } else {
                            throw $e;
                        }
                    }
                    $totalServices += ($qty * ($service->price ?? 0));
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