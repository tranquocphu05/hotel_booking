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

        // Filter theo trạng thái nếu có
        if ($request->filled('status')) {
            $query->where('trang_thai', $request->status);
        }
        // Nếu không có filter, hiển thị tất cả

        $invoices = $query->latest()->paginate(5);
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
        $invoice->load(['datPhong' => function($q) {
            $q->with('user', 'loaiPhong');
        }]);
        // Load services từ booking
        $booking = $invoice->datPhong;
        $bookingServices = BookingService::with('service')
            ->where('dat_phong_id', $booking->id)
            ->get();
    // use the same status value as other controllers ('hoat_dong')
    $services = Service::where('status', 'hoat_dong')->get();
        return view('admin.invoices.edit', compact('invoice', 'bookingServices', 'services', 'booking'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'trang_thai' => 'required|in:cho_thanh_toan,da_thanh_toan,hoan_tien',
        ]);

        $invoice = Invoice::findOrFail($id);
        $booking = $invoice->datPhong;

        // Update invoice status FIRST
        $invoice->trang_thai = $request->input('trang_thai');
        $invoice->save();

        // Handle services if provided
        if ($request->filled('services_data')) {
            $servicesData = $request->input('services_data', []);
            
            // Delete old services for this booking
            BookingService::where('dat_phong_id', $booking->id)->delete();
            
            // Create new service entries
            $totalServicePrice = 0;
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
                    $totalServicePrice += $service->price * $qty;
                }
            }
            
            // Recalculate and update totals
            $nights = \Carbon\Carbon::parse($booking->ngay_nhan)
                ->diffInDays(\Carbon\Carbon::parse($booking->ngay_tra));
            $roomTypes = $booking->getRoomTypes();
            $roomTotal = 0;
            foreach ($roomTypes as $rt) {
                // Historical data: many places store 'gia_rieng' as the subtotal for that room-type
                // (unit_price * nights * so_luong). To avoid double-multiplying here, prefer the
                // stored 'gia_rieng' when present. If missing, fallback to computing from LoaiPhong.
                if (isset($rt['gia_rieng']) && $rt['gia_rieng'] !== null) {
                    // treat as subtotal already
                    $roomTotal += (float) $rt['gia_rieng'];
                } else {
                    // fallback: compute from LoaiPhong unit price
                    $loaiPhongId = $rt['loai_phong_id'] ?? null;
                    $soLuong = $rt['so_luong'] ?? 1;
                    $unit = 0;
                    if ($loaiPhongId) {
                        $lp = \App\Models\LoaiPhong::find($loaiPhongId);
                        if ($lp) $unit = $lp->gia_khuyen_mai ?? $lp->gia_co_ban ?? 0;
                    }
                    $roomTotal += $unit * $nights * $soLuong;
                }
            }
            
            $newTotal = $roomTotal + $totalServicePrice;
            $booking->tong_tien = $newTotal;
            $booking->save();
            
            // Sync invoice total
            $invoice->tong_tien = $newTotal;
            $invoice->save();
        }

        // Recalculate booking totals using central service (will include services we just saved)
        try {
            BookingPriceCalculator::recalcTotal($booking);
            // Ensure invoice reflects booking's canonical total
            $invoice->tong_tien = $booking->tong_tien;
            $invoice->save();
        } catch (\Throwable $e) {
            // Log and continue — do not block status update because of calc error
            \Log::warning('Recalc booking total failed in InvoiceController:update: ' . $e->getMessage());
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
    public function create(){

    }

    public function print(Invoice $invoice)
    {
        $invoice->load(['datPhong' => function($q) {
            $q->with('user', 'loaiPhong', 'voucher');
        }]);
        return view('admin.invoices.print', compact('invoice'));
    }

}

