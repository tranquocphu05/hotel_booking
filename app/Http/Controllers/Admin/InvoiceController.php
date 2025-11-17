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

        // Filter by status if provided, otherwise show all invoices
        if ($request->filled('status')) {
            $query->where('trang_thai', $request->status);
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
        $invoice->load(['datPhong' => function($q) {
            $q->with('user', 'loaiPhong');
        }]);
        // Load services từ booking
        $booking = $invoice->datPhong;
        $bookingServices = BookingService::with('service')
            ->where('dat_phong_id', $booking->id)
            ->get();
        
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
        $booking = $invoice->datPhong;

        // Update invoice status FIRST
        $invoice->trang_thai = $request->input('trang_thai');
        $invoice->save();

        // Handle services if provided
        if ($request->filled('services_data')) {
            $servicesData = $request->input('services_data', []);
            
            // Delete old services for this booking BEFORE creating new ones
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
        } else {
            // If no services_data provided but there are existing services,
            // they should remain intact (don't delete)
        }

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
                \Log::info('Invoice ' . $invoice->id . ' updated: tong_tien=' . $invoice->tong_tien . ', booking tong_tien=' . $booking->tong_tien);
            }
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

