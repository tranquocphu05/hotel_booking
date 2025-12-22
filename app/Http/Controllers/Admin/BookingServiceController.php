<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingService;
use App\Models\DatPhong;
use App\Services\BookingPriceCalculator;
use Illuminate\Http\Request;
use App\Traits\HasRolePermissions;

class BookingServiceController extends Controller
{
    use HasRolePermissions;

    // 🔹 Lấy danh sách dịch vụ phát sinh cho 1 đặt phòng
    public function index($datPhongId)
    {
        // Nhân viên: xem dịch vụ
        $this->authorizePermission('service.view');
        $services = BookingService::with('service')
            ->where('dat_phong_id', $datPhongId)
            ->orderBy('used_at', 'desc')
            ->get();

        return response()->json($services);
    }

    // 🔹 Thêm dịch vụ vào đặt phòng
    // Nhân viên: Thêm dịch vụ phát sinh vào phòng đang ở
    public function store(Request $request)
    {
        // Nhân viên: thêm dịch vụ phát sinh vào phòng đang ở
        $this->authorizePermission('service.add_to_room');
        
        $validated = $request->validate([
            'dat_phong_id' => 'required|exists:dat_phong,id',
            'service_id' => 'required|exists:services,id',
            'phong_id' => 'nullable|exists:phongs,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'used_at' => 'nullable|date',
            'note' => 'nullable|string|max:255',
            'ghi_chu' => 'nullable|string|max:500',
        ], [
            'dat_phong_id.required' => 'Vui lòng chọn booking',
            'service_id.required' => 'Vui lòng chọn dịch vụ',
            'quantity.required' => 'Vui lòng nhập số lượng',
            'quantity.min' => 'Số lượng tối thiểu là 1',
            'unit_price.required' => 'Vui lòng nhập đơn giá',
        ]);

        // Validate booking can request service
        $booking = DatPhong::findOrFail($validated['dat_phong_id']);
        if (!$booking->canRequestService()) {
            return response()->json([
                'message' => 'Chỉ có thể thêm dịch vụ khi khách đang ở (đã check-in, chưa check-out)',
            ], 422);
        }

        // Set used_at to now if not provided
        if (!isset($validated['used_at'])) {
            $validated['used_at'] = now();
        }

        // If booking already has an invoice (e.g., confirmed), link the new service to it
        if ($booking->invoice) {
            $validated['invoice_id'] = $booking->invoice->id;
        }

        $bookingService = BookingService::create($validated);

        // 🔹 Gọi lại hàm tính tổng
        BookingPriceCalculator::recalcTotal($booking);

        return response()->json([
            'message' => 'Thêm dịch vụ thành công',
            'data' => $bookingService->load('service'),
        ], 201);
    }


    // 🔹 Cập nhật thông tin dịch vụ đã thêm
    public function update(Request $request, $id)
    {
        $bookingService = BookingService::findOrFail($id);

        $validated = $request->validate([
            'quantity' => 'sometimes|integer|min:1',
            'unit_price' => 'sometimes|numeric|min:0',
            'used_at' => 'sometimes|date',
            'note' => 'nullable|string|max:255',
        ]);

        $bookingService->update($validated);

        // Cập nhật tổng tiền đặt phòng
        BookingPriceCalculator::recalcTotal($bookingService->booking);

        return response()->json([
            'message' => 'Cập nhật dịch vụ thành công',
            'data' => $bookingService->fresh('service'), 
        ]);
    }


    // 🔹 Xóa dịch vụ khỏi đặt phòng
    public function destroy($id)
    {
        $bookingService = BookingService::findOrFail($id);
        $booking = $bookingService->booking;
        $bookingService->delete();

        // Cập nhật tổng tiền đặt phòng
        BookingPriceCalculator::recalcTotal($booking);

        return response()->json(['message' => 'Xóa dịch vụ thành công']);
    }
}
