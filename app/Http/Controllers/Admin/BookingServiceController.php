<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingService;
use App\Models\DatPhong;
use App\Services\BookingPriceCalculator;
use Illuminate\Http\Request;

class BookingServiceController extends Controller
{
    // 🔹 Lấy danh sách dịch vụ phát sinh cho 1 đặt phòng
    public function index($datPhongId)
    {
        $services = BookingService::with('service')
            ->where('dat_phong_id', $datPhongId)
            ->orderBy('used_at', 'desc')
            ->get();

        return response()->json($services);
    }

    // 🔹 Thêm dịch vụ vào đặt phòng
    public function store(Request $request)
    {
        $validated = $request->validate([
            'dat_phong_id' => 'required|exists:dat_phong,id',
            'service_id' => 'required|exists:services,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'used_at' => 'required|date',
            'note' => 'nullable|string|max:255',
            'invoice_id' => 'nullable|exists:hoa_don,id',
        ]);

        // If a service entry for the same booking/service/date exists, increment quantity instead
        $existing = BookingService::where('dat_phong_id', $validated['dat_phong_id'])
            ->where('service_id', $validated['service_id'])
            ->where('used_at', $validated['used_at'])
            ->whereNull('invoice_id')
            ->first();

        if ($existing) {
            $existing->quantity = ($existing->quantity ?? 0) + (int) $validated['quantity'];
            $existing->unit_price = $validated['unit_price'];
            $existing->save();
            $bookingService = $existing;
        } else {
            try {
                $bookingService = BookingService::create($validated);
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                // Fallback: someone created the same non-invoice entry right after our check
                $existingFallback = BookingService::where('dat_phong_id', $validated['dat_phong_id'])
                    ->where('service_id', $validated['service_id'])
                    ->where('used_at', $validated['used_at'])
                    ->whereNull('invoice_id')
                    ->first();

                if ($existingFallback) {
                    $existingFallback->quantity = ($existingFallback->quantity ?? 0) + (int) $validated['quantity'];
                    $existingFallback->unit_price = $validated['unit_price'];
                    $existingFallback->save();
                    $bookingService = $existingFallback;
                } else {
                    throw $e;
                }
            }
        }

        // 🔹 Gọi lại hàm tính tổng
        $booking = DatPhong::find($validated['dat_phong_id']);
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
