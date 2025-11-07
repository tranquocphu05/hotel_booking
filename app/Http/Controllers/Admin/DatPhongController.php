<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Voucher;
use App\Models\DatPhong;
use App\Models\Phong;
use App\Models\LoaiPhong;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Mail\BookingConfirmed;
use App\Mail\InvoicePaid;
use App\Mail\AdminBookingEvent;

class DatPhongController extends Controller
{
    public function index(Request $request)
    {
        // Lấy tất cả đơn đặt phòng và sắp xếp theo ngày đặt mới nhất
        $query = DatPhong::with(['loaiPhong', 'voucher', 'invoice'])
            ->orderBy('ngay_dat', 'desc');

        // Áp dụng các bộ lọc
        if ($request->search) {
            $query->whereHas('loaiPhong', function ($q) use ($request) {
                $q->where('ten_loai', 'like', '%' . $request->search . '%');
            })
            ->orWhere('username', 'like', '%' . $request->search . '%')
            ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        if ($request->status) {
            $query->where('trang_thai', $request->status);
        }

        if ($request->from_date) {
            $query->whereDate('ngay_dat', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('ngay_dat', '<=', $request->to_date);
        }

        $today = Carbon::today();

        $bookingCounts = [
            'cho_xac_nhan' => DatPhong::where('trang_thai', 'cho_xac_nhan')->whereDate('ngay_dat', $today)->count(),
            'da_xac_nhan'  => DatPhong::where('trang_thai', 'da_xac_nhan')->whereDate('ngay_dat', $today)->count(),
            'da_huy'       => DatPhong::where('trang_thai', 'da_huy')->whereDate('ngay_dat', $today)->count(),
            'da_tra'       => DatPhong::where('trang_thai', 'da_tra')->whereDate('ngay_dat', $today)->count(),
        ];

        // Phân trang, mỗi trang 5 đơn
        $bookings = $query->paginate(5);

        if ($request->ajax()) {
            return view('admin.dat_phong._bookings_list', compact('bookings'))->render();
        }

        return view('admin.dat_phong.index', compact('bookings', 'bookingCounts'));
    }

    public function showCancelForm($id)
    {
        $booking = DatPhong::with(['loaiPhong'])->findOrFail($id);

        // Kiểm tra nếu không phải trạng thái chờ xác nhận thì không cho hủy
        if ($booking->trang_thai !== 'cho_xac_nhan') {
            return redirect()->route('admin.dat_phong.index')
                ->with('error', 'Chỉ có thể hủy đơn đặt phòng đang chờ xác nhận');
        }

        return view('admin.dat_phong.cancel', compact('booking'));
    }

    public function submitCancel(Request $request, $id)
    {
        $booking = DatPhong::findOrFail($id);

        // Validate
        $request->validate([
            'ly_do' => 'required|in:thay_doi_lich_trinh,thay_doi_ke_hoach,khong_phu_hop,ly_do_khac'
        ], [
            'ly_do.required' => 'Vui lòng chọn lý do hủy đặt phòng',
            'ly_do.in' => 'Lý do không hợp lệ'
        ]);

        // Cập nhật trạng thái và lý do hủy, đồng thời giải phóng phòng
        \DB::transaction(function () use ($booking, $request) {
            // Load relationships
            $booking->load(['phong', 'loaiPhong']);
            
            // Update booking status
            $booking->update([
                'trang_thai' => 'da_huy',
                'ngay_huy' => now()
            ]);

            // Free up room via phong_id (legacy)
            if ($booking->phong_id && $booking->phong) {
                // Kiểm tra xem phòng có đang được đặt cho booking khác không
                $hasOtherBooking = DatPhong::where('id', '!=', $booking->id)
                    ->where(function($q) use ($booking) {
                        $q->where('phong_id', $booking->phong_id)
                          ->orWhereJsonContains('phong_ids', $booking->phong_id);
                    })
                    ->where(function($q) use ($booking) {
                        $q->where('ngay_tra', '>', $booking->ngay_nhan)
                          ->where('ngay_nhan', '<', $booking->ngay_tra);
                    })
                    ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
                    ->exists();
                
                if (!$hasOtherBooking) {
                    $booking->phong->update(['trang_thai' => 'trong']);
                }
            }

            // Free up rooms via phong_ids JSON
            $phongIds = $booking->getPhongIds();
            foreach ($phongIds as $phongId) {
                $phong = Phong::find($phongId);
                if ($phong) {
                    // Kiểm tra xem phòng có đang được đặt cho booking khác không
                    $hasOtherBooking = DatPhong::where('id', '!=', $booking->id)
                        ->where(function($q) use ($phongId) {
                            $q->where('phong_id', $phongId)
                              ->orWhereJsonContains('phong_ids', $phongId);
                        })
                        ->where(function($q) use ($booking) {
                            $q->where('ngay_tra', '>', $booking->ngay_nhan)
                              ->where('ngay_nhan', '<', $booking->ngay_tra);
                        })
                        ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
                        ->exists();
                    
                    if (!$hasOtherBooking) {
                        $phong->update(['trang_thai' => 'trong']);
                    }
                }
            }
            
            // Clear phong_ids after freeing rooms
            $booking->phong_ids = [];
            $booking->save();

            // Update so_luong_trong in loai_phong
            if ($booking->loaiPhong) {
                $trongCount = \App\Models\Phong::where('loai_phong_id', $booking->loai_phong_id)
                    ->where('trang_thai', 'trong')
                    ->count();
                $booking->loaiPhong->update(['so_luong_trong' => $trongCount]);
            }
        });

        return redirect()->route('admin.dat_phong.index')
            ->with('success', 'Đã hủy đặt phòng thành công');
    }

    public function show($id)
    {
        $booking = DatPhong::with(['loaiPhong', 'voucher', 'phong'])->findOrFail($id);
        
        // Lấy danh sách phòng trống của loại phòng này cho khoảng thời gian booking
        // Loại trừ các phòng đã được gán cho booking này
        $availableRooms = null;
        if ($booking->loai_phong_id && $booking->ngay_nhan && $booking->ngay_tra) {
            $assignedPhongIds = $booking->getPhongIds();
            $availableRooms = Phong::findAvailableRooms(
                $booking->loai_phong_id,
                $booking->ngay_nhan,
                $booking->ngay_tra,
                20 // Lấy tối đa 20 phòng để hiển thị
            )->reject(function($phong) use ($assignedPhongIds) {
                return in_array($phong->id, $assignedPhongIds);
            })->values();
        }
        
        return view('admin.dat_phong.show', compact('booking', 'availableRooms'));
    }

    public function edit($id)
    {
        $booking = DatPhong::with(['loaiPhong', 'voucher', 'user', 'phong'])->findOrFail($id);

        // Lấy danh sách loại phòng để hiển thị trong form sửa
        $loaiPhongs = \App\Models\LoaiPhong::where('trang_thai', 'hoat_dong')->get();

        // Chỉ cho phép sửa đơn đang chờ xác nhận
        if ($booking->trang_thai !== 'cho_xac_nhan') {
            return redirect()->route('admin.dat_phong.show', $booking->id)
                ->with('error', 'Chỉ có thể sửa đơn đặt phòng đang chờ xác nhận');
        }

        // Tự động điền CCCD từ user nếu booking chưa có CCCD
        if (!$booking->cccd && $booking->user && $booking->user->cccd) {
            $booking->cccd = $booking->user->cccd;
        }

        // Lấy danh sách phòng trống của loại phòng này cho khoảng thời gian booking
        // Bao gồm cả phòng hiện tại đã được gán (để có thể giữ nguyên hoặc đổi)
        $availableRooms = null;
        if ($booking->loai_phong_id && $booking->ngay_nhan && $booking->ngay_tra) {
            $availableRooms = Phong::findAvailableRooms(
                $booking->loai_phong_id,
                $booking->ngay_nhan,
                $booking->ngay_tra,
                20 // Lấy tối đa 20 phòng để hiển thị
            )->values();
            
            // Loại trừ các phòng đã được gán cho booking này
            $assignedPhongIds = $booking->getPhongIds();
            $availableRooms = $availableRooms->reject(function($phong) use ($assignedPhongIds) {
                return in_array($phong->id, $assignedPhongIds);
            })->values();
        }

        return view('admin.dat_phong.edit', compact('booking', 'loaiPhongs', 'availableRooms'));
    }

    public function update(Request $request, $id)
    {
        $booking = DatPhong::findOrFail($id);

        if ($booking->trang_thai !== 'cho_xac_nhan') {
            return redirect()->route('admin.dat_phong.show', $booking->id)
                ->with('error', 'Chỉ có thể sửa đơn đặt phòng đang chờ xác nhận');
        }

        // Validate room_types array
        $request->validate([
            'room_types' => 'required|array|min:1',
            'room_types.*.loai_phong_id' => 'required|exists:loai_phong,id',
            'room_types.*.so_luong' => 'required|integer|min:1|max:10',
            'room_types.*.gia_rieng' => 'required|numeric|min:0',
            'ngay_nhan' => 'required|date|after_or_equal:today',
            'ngay_tra' => 'required|date|after_or_equal:ngay_nhan',
            'so_nguoi' => 'required|integer|min:1',
            'username' => 'required|string|max:255|regex:/^[\p{L}\s]+$/u',
            'email' => 'required|email:rfc,dns|max:255',
            'sdt' => 'required|regex:/^0[0-9]{9}$/',
            'cccd' => 'required|regex:/^[0-9]{12}$/',
            'voucher' => 'nullable|string|exists:voucher,ma_voucher'
        ], [
            'room_types.required' => 'Vui lòng chọn ít nhất một loại phòng',
            'room_types.*.loai_phong_id.required' => 'Vui lòng chọn loại phòng',
            'room_types.*.loai_phong_id.exists' => 'Loại phòng không tồn tại',
            'room_types.*.so_luong.required' => 'Vui lòng nhập số lượng phòng',
            'room_types.*.so_luong.min' => 'Số lượng phòng phải lớn hơn 0',
            'room_types.*.so_luong.max' => 'Số lượng phòng không được vượt quá 10',
            'ngay_nhan.required' => 'Vui lòng chọn ngày nhận phòng',
            'ngay_nhan.after_or_equal' => 'Ngày nhận phòng phải từ hôm nay trở đi',
            'ngay_tra.required' => 'Vui lòng chọn ngày trả phòng',
            'ngay_tra.after_or_equal' => 'Ngày trả phòng phải sau hoặc bằng ngày nhận phòng',
            'so_nguoi.required' => 'Vui lòng nhập số người',
            'so_nguoi.min' => 'Số người phải lớn hơn 0',
            'username.required' => 'Vui lòng nhập họ tên',
            'username.regex' => 'Vui lòng nhập tên của bạn',
            'email.required' => 'Vui lòng nhập email',
            'email.email' => 'Email không hợp lệ',
            'sdt.required' => 'Vui lòng nhập số điện thoại',
            'sdt.regex' => 'Số điện thoại không đúng định dạng',
            'cccd.required' => 'Vui lòng nhập CCCD/CMND',
            'cccd.regex' => 'CCCD/CMND phải gồm 12 chữ số',
        ]);

        $roomTypes = $request->room_types;
        
        // Check for duplicate room types
        $loaiPhongIds = array_column($roomTypes, 'loai_phong_id');
        if (count($loaiPhongIds) !== count(array_unique($loaiPhongIds))) {
            return back()->withErrors(['room_types' => 'Không thể chọn trùng loại phòng.'])->withInput();
        }

        // Validate each room type availability
        // Lấy danh sách phòng đã gán để loại trừ khi kiểm tra availability
        $oldPhongIds = $booking->getPhongIds();
        
        foreach ($roomTypes as $roomType) {
            $loaiPhong = \App\Models\LoaiPhong::find($roomType['loai_phong_id']);
            if (!$loaiPhong || $loaiPhong->trang_thai !== 'hoat_dong') {
                return back()->withErrors(['room_types' => 'Loại phòng ' . ($loaiPhong->ten_loai ?? 'N/A') . ' không khả dụng.'])->withInput();
            }

            // Check availability for the date range (exclude current booking's rooms)
            $availableCount = Phong::countAvailableRooms(
                $roomType['loai_phong_id'],
                $request->ngay_nhan,
                $request->ngay_tra
            );
            
            // Đếm số phòng cũ thuộc loại này và trong danh sách phòng đã gán
            $oldPhongsOfThisType = Phong::whereIn('id', $oldPhongIds)
                ->where('loai_phong_id', $roomType['loai_phong_id'])
                ->count();
            
            // Số phòng cần thiết sau khi trừ đi phòng cũ cùng loại (nếu có)
            $soLuongCanThem = max(0, $roomType['so_luong'] - $oldPhongsOfThisType);
            
            // Nếu cần thêm phòng, kiểm tra availability
            if ($soLuongCanThem > 0 && $availableCount < $soLuongCanThem) {
                return back()->withErrors([
                    'room_types' => 'Loại phòng ' . $loaiPhong->ten_loai . ' chỉ còn ' . $availableCount . ' phòng trống trong khoảng thời gian từ ' . date('d/m/Y', strtotime($request->ngay_nhan)) . ' đến ' . date('d/m/Y', strtotime($request->ngay_tra)) . '. Bạn cần thêm ' . $soLuongCanThem . ' phòng.'
                ])->withInput();
            }
        }

        // Calculate total rooms and price
        $totalSoLuong = array_sum(array_column($roomTypes, 'so_luong'));
        $totalPrice = 0;
        foreach ($roomTypes as $roomType) {
            $totalPrice += ($roomType['gia_rieng'] * $roomType['so_luong']);
        }

        // Get first room type for legacy support
        $firstLoaiPhongId = $roomTypes[0]['loai_phong_id'];

        // Update booking và gán lại phòng trong transaction
        DB::transaction(function () use ($booking, $request, $roomTypes, $totalSoLuong, $firstLoaiPhongId, $oldPhongIds) {
            // 1. Giải phóng tất cả phòng cũ (set về 'trong' nếu không có booking khác)
            foreach ($oldPhongIds as $phongId) {
                $phong = Phong::find($phongId);
                if ($phong) {
                    // Kiểm tra xem phòng có đang được đặt cho booking khác không
                    $hasOtherBooking = DatPhong::where('id', '!=', $booking->id)
                        ->whereJsonContains('phong_ids', $phongId)
                        ->where(function($q) use ($request) {
                            $q->where('ngay_tra', '>', $request->ngay_nhan)
                              ->where('ngay_nhan', '<', $request->ngay_tra);
                        })
                        ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
                        ->exists();
                    
                    if (!$hasOtherBooking) {
                        $phong->update(['trang_thai' => 'trong']);
                    }
                }
            }
            
            // 2. Gán lại phòng mới dựa trên room_types mới
            $newPhongIds = [];
            foreach ($roomTypes as $roomType) {
                $soLuongCan = $roomType['so_luong'];
                
                // Ưu tiên giữ lại phòng cũ nếu cùng loại và còn available
                $oldPhongsOfThisType = Phong::whereIn('id', $oldPhongIds)
                    ->where('loai_phong_id', $roomType['loai_phong_id'])
                    ->get()
                    ->filter(function($phong) use ($request, $booking) {
                        return $phong->isAvailableInPeriod($request->ngay_nhan, $request->ngay_tra, $booking->id);
                    })
                    ->take($soLuongCan);
                
                $keptCount = $oldPhongsOfThisType->count();
                foreach ($oldPhongsOfThisType as $phong) {
                    $newPhongIds[] = $phong->id;
                }
                
                // Nếu cần thêm phòng, tìm phòng mới
                if ($keptCount < $soLuongCan) {
                    $soLuongCanThem = $soLuongCan - $keptCount;
                    $availableRooms = Phong::findAvailableRooms(
                        $roomType['loai_phong_id'],
                        $request->ngay_nhan,
                        $request->ngay_tra,
                        $soLuongCanThem,
                        $booking->id
                    )->reject(function($phong) use ($newPhongIds) {
                        return in_array($phong->id, $newPhongIds);
                    });
                    
                    foreach ($availableRooms as $phong) {
                        $newPhongIds[] = $phong->id;
                        $phong->update(['trang_thai' => 'dang_thue']);
                    }
                }
            }
            
            // 3. Update booking với thông tin mới
            $booking->update([
                'loai_phong_id' => $firstLoaiPhongId, // Legacy support
                'room_types' => $roomTypes, // Store all room types in JSON
                'so_luong_da_dat' => $totalSoLuong,
                'trang_thai' => $request->trang_thai ?? $booking->trang_thai,
                'ngay_nhan' => $request->ngay_nhan,
                'ngay_tra' => $request->ngay_tra,
                'so_nguoi' => $request->so_nguoi,
                'username' => $request->username,
                'email' => $request->email,
                'sdt' => $request->sdt,
                'cccd' => $request->cccd,
                'phong_ids' => $newPhongIds, // Cập nhật danh sách phòng mới
            ]);
            
            // 4. Cập nhật phong_id (legacy support) nếu chỉ có 1 phòng
            if (count($newPhongIds) == 1) {
                $booking->update(['phong_id' => $newPhongIds[0]]);
            } else {
                $booking->update(['phong_id' => null]);
            }
        });

        return redirect()->route('admin.dat_phong.show', $booking->id)
            ->with('success', 'Cập nhật thông tin đặt phòng thành công');
    }

    public function assignRoom(Request $request, $id)
    {
        $booking = DatPhong::with(['loaiPhong', 'phong'])->findOrFail($id);

        // Kiểm tra booking có phải trạng thái cho phép gán phòng không
        if (!in_array($booking->trang_thai, ['cho_xac_nhan', 'da_xac_nhan'])) {
            return redirect()->route('admin.dat_phong.show', $booking->id)
                ->with('error', 'Chỉ có thể gán phòng cho booking đang chờ xác nhận hoặc đã xác nhận.');
        }

        $request->validate([
            'phong_id' => 'required|exists:phong,id',
        ], [
            'phong_id.required' => 'Vui lòng chọn phòng',
            'phong_id.exists' => 'Phòng không tồn tại',
        ]);

        $phongId = $request->phong_id;
        $phong = Phong::find($phongId);
        
        if (!$phong) {
            return redirect()->back()
                ->withErrors(['phong_id' => 'Phòng không tồn tại.'])
                ->withInput();
        }
        
        // Kiểm tra phòng có thuộc loại phòng của booking không
        // Nếu booking có nhiều loại phòng (room_types), kiểm tra phòng có thuộc một trong các loại đó không
        $roomTypes = $booking->getRoomTypes();
        $allowedLoaiPhongIds = [];
        
        if (count($roomTypes) > 1) {
            // Booking có nhiều loại phòng
            $allowedLoaiPhongIds = array_column($roomTypes, 'loai_phong_id');
        } else {
            // Booking chỉ có 1 loại phòng (legacy hoặc single room type)
            $allowedLoaiPhongIds = [$booking->loai_phong_id];
        }
        
        if (!in_array($phong->loai_phong_id, $allowedLoaiPhongIds)) {
            return redirect()->back()
                ->withErrors(['phong_id' => 'Phòng không thuộc loại phòng của booking này.'])
                ->withInput();
        }

        // Kiểm tra phòng có đang bảo trì không
        if ($phong->trang_thai === 'bao_tri') {
            return redirect()->back()
                ->withErrors(['phong_id' => 'Phòng này đang bảo trì, không thể gán cho booking.'])
                ->withInput();
        }

        // Kiểm tra đã gán đủ phòng chưa (nếu booking có số lượng cụ thể)
        $assignedPhongIds = $booking->getPhongIds();
        $assignedCount = count($assignedPhongIds);
        if ($booking->so_luong_da_dat > 1 && $assignedCount >= $booking->so_luong_da_dat) {
            return redirect()->back()
                ->withErrors(['phong_id' => 'Đã gán đủ ' . $booking->so_luong_da_dat . ' phòng cho booking này.'])
                ->withInput();
        }

        // Kiểm tra phòng đã được gán cho booking này chưa
        if (in_array($phongId, $assignedPhongIds)) {
            return redirect()->back()
                ->withErrors(['phong_id' => 'Phòng này đã được gán cho booking này rồi.'])
                ->withInput();
        }
        
        // Kiểm tra phòng có trống trong khoảng thời gian không
        // Method isAvailableInPeriod sẽ tự động kiểm tra cả bookings qua phong_id và qua bảng trung gian
        if (!$phong->isAvailableInPeriod($booking->ngay_nhan, $booking->ngay_tra, $booking->id)) {
            return redirect()->back()
                ->withErrors(['phong_id' => 'Phòng này đã được đặt trong khoảng thời gian từ ' . date('d/m/Y', strtotime($booking->ngay_nhan)) . ' đến ' . date('d/m/Y', strtotime($booking->ngay_tra)) . '.'])
                ->withInput();
        }
        
        // Thêm phòng vào phong_ids JSON
        \DB::transaction(function() use ($booking, $phongId, $phong) {
            // Reload booking để đảm bảo có dữ liệu mới nhất
            $booking->refresh();
            
            // Thêm vào phong_ids JSON bằng cách thủ công để đảm bảo dữ liệu được lưu đúng
            $phongIds = $booking->getPhongIds();
            if (!in_array($phongId, $phongIds)) {
                $phongIds[] = (int)$phongId;
                $booking->phong_ids = $phongIds;
                $booking->save();
            }
            
            // Cập nhật trạng thái phòng thành "đang thuê"
            $phong->refresh();
            if ($phong->trang_thai === 'trong') {
                $phong->update(['trang_thai' => 'dang_thue']);
            }
            
            // Nếu đây là phòng đầu tiên được gán và booking chưa có phong_id, cập nhật phong_id (legacy support)
            $booking->refresh();
            $phongIds = $booking->getPhongIds();
            if (!$booking->phong_id && count($phongIds) == 1) {
                $booking->update(['phong_id' => $phongId]);
            }
        });
        
        // Lấy lại thông tin phòng để hiển thị trong message
        $phong = Phong::find($phongId);
        $phongNumber = $phong ? $phong->so_phong : 'N/A';
        
        // Xác định route redirect dựa trên referer
        $referer = $request->headers->get('referer');
        $redirectRoute = str_contains($referer, '/edit') 
            ? route('admin.dat_phong.edit', $booking->id)
            : route('admin.dat_phong.show', $booking->id);
        
        $booking->refresh();
        $assignedPhongIds = $booking->getPhongIds();
        $assignedCount = count($assignedPhongIds);
        $remainingCount = $booking->so_luong_da_dat - $assignedCount;
        
        $message = 'Gán phòng ' . $phongNumber . ' thành công!';
        if ($booking->so_luong_da_dat > 1) {
            $message .= ' Đã gán ' . $assignedCount . '/' . $booking->so_luong_da_dat . ' phòng';
            if ($remainingCount > 0) {
                $message .= ' (Còn thiếu ' . $remainingCount . ' phòng)';
            } else {
                $message .= ' (Đã gán đủ)';
            }
        }
        
        return redirect($redirectRoute)
            ->with('success', $message);
    }

    public function create()
    {
        // Lấy danh sách loại phòng thay vì phòng cụ thể
        $loaiPhongs = \App\Models\LoaiPhong::where('trang_thai', 'hoat_dong')->get();

        // Lấy danh sách voucher còn hiệu lực
        $vouchers = Voucher::where('trang_thai', 'con_han')
            ->where('so_luong', '>', 0)
            ->whereDate('ngay_ket_thuc', '>=', now())
            ->get();

        return view('admin.dat_phong.create', compact('loaiPhongs', 'vouchers'));
    }

    /**
     * API endpoint để lấy số phòng trống theo khoảng thời gian (AJAX)
     */
    public function getAvailableCount(Request $request)
    {
        $request->validate([
            'loai_phong_id' => 'required|exists:loai_phong,id',
            'checkin' => 'required|date',
            'checkout' => 'required|date|after:checkin',
        ]);

        try {
            $availableCount = Phong::countAvailableRooms(
                $request->loai_phong_id,
                $request->checkin,
                $request->checkout
            );

            return response()->json([
                'success' => true,
                'available_count' => $availableCount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể kiểm tra số phòng trống: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        // Validate room_types array first
        $request->validate([
            'room_types' => 'required|array|min:1',
            'room_types.*' => 'required|integer|exists:loai_phong,id',
            'ngay_nhan' => 'required|date|after_or_equal:today',
            'ngay_tra' => 'required|date|after:ngay_nhan',
            'so_nguoi' => 'required|integer|min:1',
            'username' => 'required|string|max:255|regex:/^[\p{L}\s]+$/u',
            'email' => 'required|email:rfc,dns|max:255',
            'sdt' => 'required|regex:/^0[0-9]{9}$/',
            'cccd' => 'required|regex:/^[0-9]{12}$/',
            'voucher' => 'nullable|string|exists:voucher,ma_voucher'
        ], [
            'room_types.required' => 'Vui lòng chọn ít nhất một loại phòng',
            'room_types.min' => 'Vui lòng chọn ít nhất một loại phòng',
            'room_types.*.exists' => 'Loại phòng không tồn tại',
            'ngay_nhan.required' => 'Vui lòng chọn ngày nhận phòng',
            'ngay_nhan.after_or_equal' => 'Ngày nhận phòng phải từ hôm nay trở đi',
            'ngay_tra.required' => 'Vui lòng chọn ngày trả phòng',
            'ngay_tra.after' => 'Ngày trả phòng phải sau ngày nhận phòng',
            'so_nguoi.required' => 'Vui lòng nhập số người',
            'so_nguoi.min' => 'Số người phải lớn hơn 0',
            'username.required' => 'Vui lòng nhập họ tên',
            'username.regex' => 'Vui lòng nhập tên của bạn',
            'email.required' => 'Vui lòng nhập email',
            'email.email' => 'Email không hợp lệ',
            'sdt.required' => 'Vui lòng nhập số điện thoại',
            'sdt.regex' => 'Số điện thoại không đúng định dạng (phải bắt đầu bằng 0 và có 10 chữ số)',
            'cccd.required' => 'Vui lòng nhập CCCD/CMND',
            'cccd.regex' => 'CCCD/CMND phải gồm 12 chữ số',
        ]);

        // Lọc chỉ lấy các loại phòng đã được chọn
        $selectedRoomTypes = $request->room_types ?? [];
        if (empty($selectedRoomTypes)) {
            return back()->withErrors(['room_types' => 'Vui lòng chọn ít nhất một loại phòng'])->withInput();
        }

        // Validate số lượng cho từng loại phòng đã chọn
        $errors = [];
        foreach ($selectedRoomTypes as $roomTypeId) {
            if (!isset($request->rooms[$roomTypeId])) {
                $errors["rooms.{$roomTypeId}.so_luong"] = "Vui lòng nhập số lượng cho loại phòng này";
                continue;
            }
            
            $room = $request->rooms[$roomTypeId];
            if (!isset($room['so_luong']) || $room['so_luong'] <= 0) {
                $errors["rooms.{$roomTypeId}.so_luong"] = "Số lượng phòng phải lớn hơn 0";
            }
            
            if (!isset($room['loai_phong_id']) || $room['loai_phong_id'] != $roomTypeId) {
                $errors["rooms.{$roomTypeId}.loai_phong_id"] = "Dữ liệu không hợp lệ";
            }
        }
        
        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

        $nights = Carbon::parse($request->ngay_nhan)->diffInDays(Carbon::parse($request->ngay_tra));
        $nights = max(1, $nights);
        
        // Validate each room type and check availability
        $totalPrice = 0;
        $roomDetails = [];
        $validationErrors = [];
        
        foreach ($selectedRoomTypes as $roomTypeId) {
            $room = $request->rooms[$roomTypeId];
            // Additional validation: check if room_type_id matches
            if ($room['loai_phong_id'] != $roomTypeId) {
                $validationErrors[] = "Dữ liệu không hợp lệ cho loại phòng ID: {$roomTypeId}";
                continue;
            }
            
            $loaiPhong = \App\Models\LoaiPhong::find($room['loai_phong_id']);
            
            if (!$loaiPhong) {
                $validationErrors[] = "Loại phòng ID {$room['loai_phong_id']} không tồn tại";
                continue;
            }
            
            // Check if room type is active
            if ($loaiPhong->trang_thai !== 'hoat_dong') {
                $validationErrors[] = "Loại phòng '{$loaiPhong->ten_loai}' hiện không khả dụng";
                continue;
            }
            
            // Validate quantity is positive
            if ($room['so_luong'] < 1) {
                $validationErrors[] = "Số lượng phòng cho loại phòng '{$loaiPhong->ten_loai}' phải lớn hơn 0";
                continue;
            }
            
            // Check availability based on date range (real-time check)
            $availableCount = Phong::countAvailableRooms($loaiPhong->id, $request->ngay_nhan, $request->ngay_tra);
            if ($availableCount < $room['so_luong']) {
                $validationErrors[] = "Loại phòng '{$loaiPhong->ten_loai}' chỉ có {$availableCount} phòng trống trong khoảng thời gian từ " . date('d/m/Y', strtotime($request->ngay_nhan)) . " đến " . date('d/m/Y', strtotime($request->ngay_tra)) . ". Bạn đã chọn {$room['so_luong']} phòng";
                continue;
            }
            
            // Use promotional price if available, otherwise use base price
            $pricePerNight = $loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban ?? 0;
            $roomTotal = $pricePerNight * $nights * $room['so_luong'];
            $totalPrice += $roomTotal;
            
            $roomDetails[] = [
                'loai_phong_id' => $loaiPhong->id, // Thêm ID để dùng trong transaction
                'loai_phong' => $loaiPhong,
                'so_luong' => $room['so_luong'],
                'price' => $roomTotal,
            ];
        }
        
        // Return errors if any validation failed
        if (!empty($validationErrors)) {
            return back()->withErrors(['error' => implode('. ', $validationErrors)])->withInput();
        }
        
        // Additional validation: at least one room must be selected
        if (empty($roomDetails)) {
            return back()->withErrors(['room_types' => 'Vui lòng chọn ít nhất một loại phòng'])->withInput();
        }

        // Xử lý voucher nếu có
        $voucherId = null;
        $finalPrice = $totalPrice;
        if ($request->voucher) {
            $voucher = Voucher::where('ma_voucher', $request->voucher)
                ->where('so_luong', '>', 0)
                ->where('trang_thai', 'con_han')
                ->whereDate('ngay_ket_thuc', '>=', now())
                ->first();

            if ($voucher) {
                $discountPercent = $voucher->gia_tri ?? 0;
                if ($discountPercent > 0 && $discountPercent <= 100) {
                    $finalPrice = $totalPrice * (1 - $discountPercent / 100);
                    $voucherId = $voucher->id;
                    $voucher->decrement('so_luong');
                }
            }
        }

        // Calculate price per room (distribute voucher discount proportionally)
        // Prevent division by zero
        if ($totalPrice <= 0) {
            return back()->withErrors(['error' => 'Tổng giá phòng không hợp lệ. Vui lòng kiểm tra lại.'])->withInput();
        }
        $priceRatio = $finalPrice / $totalPrice;
        
        // Tính tổng số lượng phòng
        $totalSoLuong = array_sum(array_column($roomDetails, 'so_luong'));
        
        // Lấy loại phòng đầu tiên làm loại phòng chính (cho backward compatibility)
        $firstLoaiPhongId = $roomDetails[0]['loai_phong_id'];

        // Chuẩn bị mảng room_types để lưu vào JSON
        $roomTypesArray = [];
        foreach ($roomDetails as $roomDetail) {
            $roomPrice = $roomDetail['price'] * $priceRatio;
            $roomTypesArray[] = [
                'loai_phong_id' => $roomDetail['loai_phong_id'],
                'so_luong' => $roomDetail['so_luong'],
                'gia_rieng' => $roomPrice,
            ];
        }
        
        // Create single booking within transaction to ensure atomicity
        $booking = DB::transaction(function () use ($roomDetails, $priceRatio, $request, $voucherId, $finalPrice, $totalSoLuong, $firstLoaiPhongId, $roomTypesArray) {
            // Validate availability for all room types first
            foreach ($roomDetails as $roomDetail) {
                // Lock and re-check availability inside transaction to prevent race conditions
                $loaiPhong = LoaiPhong::lockForUpdate()->findOrFail($roomDetail['loai_phong_id']);
                
                // Tìm phòng trống TRƯỚC khi kiểm tra để đảm bảo có đủ phòng trong khoảng thời gian cụ thể
                $availableRooms = Phong::findAvailableRooms(
                    $loaiPhong->id,
                    $request->ngay_nhan,
                    $request->ngay_tra,
                    $roomDetail['so_luong'], // Tìm đủ số lượng phòng cần thiết
                    null // Không exclude booking nào (booking chưa tồn tại)
                );
                
                // Kiểm tra xem có đủ phòng không (dựa trên conflict check thực tế)
                if ($availableRooms->count() < $roomDetail['so_luong']) {
                    $availableCount = Phong::countAvailableRooms($loaiPhong->id, $request->ngay_nhan, $request->ngay_tra);
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'error' => "Loại phòng '{$loaiPhong->ten_loai}' chỉ có {$availableCount} phòng trống trong khoảng thời gian từ " . date('d/m/Y', strtotime($request->ngay_nhan)) . " đến " . date('d/m/Y', strtotime($request->ngay_tra)) . ". Bạn đã chọn {$roomDetail['so_luong']} phòng."
                    ]);
                }
            }
            
            // Tạo 1 booking duy nhất chứa tất cả các loại phòng
            $booking = DatPhong::create([
                'nguoi_dung_id' => Auth::id(),
                'loai_phong_id' => $firstLoaiPhongId, // Loại phòng chính (cho backward compatibility)
                'room_types' => $roomTypesArray, // Lưu tất cả loại phòng vào JSON
                'so_luong_da_dat' => $totalSoLuong, // Tổng số lượng phòng
                'phong_id' => null, // Không gán phòng ở đây, sẽ dùng phong_ids JSON
                'ngay_dat' => now(),
                'ngay_nhan' => $request->ngay_nhan,
                'ngay_tra' => $request->ngay_tra,
                'so_nguoi' => $request->so_nguoi,
                'trang_thai' => 'cho_xac_nhan',
                'tong_tien' => $finalPrice, // Tổng tiền của tất cả loại phòng
                'voucher_id' => $voucherId,
                'username' => $request->username,
                'email' => $request->email,
                'sdt' => $request->sdt,
                'cccd' => $request->cccd
            ]);

            // Gán phòng cho tất cả các loại phòng
            $allPhongIds = [];
            foreach ($roomDetails as $roomDetail) {
                $loaiPhong = LoaiPhong::find($roomDetail['loai_phong_id']);
                
                // Tìm và gán phòng tự động
                $availableRooms = Phong::findAvailableRooms(
                    $loaiPhong->id,
                    $request->ngay_nhan,
                    $request->ngay_tra,
                    $roomDetail['so_luong'] // Tìm đủ số lượng phòng cần thiết
                )->values();
                
                // Lưu các phòng vào phong_ids JSON
                foreach ($availableRooms as $phong) {
                    $allPhongIds[] = $phong->id;
                    
                    // Cập nhật trạng thái phòng thành "đang thuê"
                    $phong->update(['trang_thai' => 'dang_thue']);
                }
            }
            
            // Lưu tất cả phong_ids vào JSON column
            $booking->phong_ids = $allPhongIds;
            $booking->save();
            
            // Cập nhật phong_id (legacy support) nếu chỉ có 1 phòng
            if (count($allPhongIds) == 1) {
                $booking->update(['phong_id' => $allPhongIds[0]]);
            }

            // Automatically create invoice with status "cho_thanh_toan" (waiting for payment)
            \App\Models\Invoice::create([
                'dat_phong_id' => $booking->id,
                'tong_tien' => $booking->tong_tien,
                'trang_thai' => 'cho_thanh_toan',
                'phuong_thuc' => null,
            ]);

            // Booking sẽ được tự động hủy bởi AutoCancelExpiredBookings middleware
            // Không cần queue worker - tích hợp trực tiếp vào code
            
            return $booking;
        });

        // Gửi mail cho admin: đơn đặt phòng mới (trạng thái chờ xác nhận)
        try {
            $adminEmails = \App\Models\User::where('vai_tro', 'admin')
                ->where('trang_thai', 'hoat_dong')
                ->pluck('email')
                ->filter()
                ->all();
            if (!empty($adminEmails)) {
                Mail::to($adminEmails)->send(new AdminBookingEvent($booking->load(['loaiPhong']), 'created'));
            }
        } catch (\Throwable $e) {
            Log::warning('Send admin booking created mail failed: ' . $e->getMessage());
        }

        // Lấy danh sách tên loại phòng từ room_types JSON
        $roomTypes = [];
        foreach ($booking->getRoomTypes() as $roomType) {
            $loaiPhong = LoaiPhong::find($roomType['loai_phong_id']);
            if ($loaiPhong) {
                $roomTypes[] = $loaiPhong->ten_loai;
            }
        }
        $roomTypesText = implode(', ', $roomTypes);
        
        return redirect()->route('admin.dat_phong.show', $booking->id)
            ->with('success', 'Đặt phòng thành công! Loại phòng: ' . $roomTypesText);
    }

    public function blockRoom($id)
    {
        $booking = DatPhong::findOrFail($id);

        // Chỉ cho phép chống phòng khi đã xác nhận
        if ($booking->trang_thai !== 'da_xac_nhan') {
            return redirect()->route('admin.dat_phong.index')
                ->with('error', 'Chỉ có thể chống phòng đã xác nhận');
        }

        // Cập nhật trạng thái đặt phòng thành "đã chống"
        // Note: Không còn phòng riêng lẻ, chỉ cập nhật booking status
        $booking->update(['trang_thai' => 'da_chong']);

        return redirect()->route('admin.dat_phong.index')
            ->with('success', 'Đã chống phòng thành công! Phòng không thể đặt được cho đến khi hủy chống.');
    }

    /**
     * Quick confirm booking from index card
     */
    public function quickConfirm($id)
    {
        $booking = DatPhong::findOrFail($id);

        if ($booking->trang_thai !== 'cho_xac_nhan') {
            return redirect()->route('admin.dat_phong.index')
                ->with('error', 'Chỉ xác nhận được đơn đang chờ xác nhận');
        }

        // Nếu chưa có phòng được gán, tự động gán phòng
        $assignedPhongIds = $booking->getPhongIds();
        if (empty($assignedPhongIds)) {
            $allPhongIds = [];
            $roomTypes = $booking->getRoomTypes();
            
            // Nếu booking có nhiều loại phòng (room_types)
            if (count($roomTypes) > 0) {
                foreach ($roomTypes as $roomType) {
                    $soLuongCan = $roomType['so_luong'] ?? 1;
                    $loaiPhongId = $roomType['loai_phong_id'];
                    
                    $availableRooms = Phong::findAvailableRooms(
                        $loaiPhongId,
                        $booking->ngay_nhan,
                        $booking->ngay_tra,
                        $soLuongCan,
                        $booking->id
                    )->reject(function($phong) use ($allPhongIds) {
                        return in_array($phong->id, $allPhongIds);
                    });
                    
                    $count = 0;
                    foreach ($availableRooms as $phong) {
                        if ($count >= $soLuongCan) break;
                        $allPhongIds[] = $phong->id;
                        $phong->update(['trang_thai' => 'dang_thue']);
                        $count++;
                    }
                    
                    // Nếu không đủ phòng, báo lỗi
                    if ($count < $soLuongCan) {
                        $loaiPhong = LoaiPhong::find($loaiPhongId);
                        return redirect()->route('admin.dat_phong.index')
                            ->with('error', "Không đủ phòng cho loại phòng '{$loaiPhong->ten_loai}'. Cần {$soLuongCan} phòng nhưng chỉ có {$count} phòng trống.");
                    }
                }
            } else {
                // Fallback: Booking chỉ có 1 loại phòng (legacy)
                $soLuongCan = $booking->so_luong_da_dat ?? 1;
                $availableRooms = Phong::findAvailableRooms(
                    $booking->loai_phong_id,
                    $booking->ngay_nhan,
                    $booking->ngay_tra,
                    $soLuongCan,
                    $booking->id
                );
                
                if ($availableRooms->count() < $soLuongCan) {
                    return redirect()->route('admin.dat_phong.index')
                        ->with('error', "Không đủ phòng. Cần {$soLuongCan} phòng nhưng chỉ có {$availableRooms->count()} phòng trống.");
                }
                
                foreach ($availableRooms as $phong) {
                    $allPhongIds[] = $phong->id;
                    $phong->update(['trang_thai' => 'dang_thue']);
                }
            }
            
            // Cập nhật phong_ids JSON
            $booking->phong_ids = $allPhongIds;
            
            // Cập nhật phong_id (legacy support) nếu chỉ có 1 phòng
            if (count($allPhongIds) == 1) {
                $booking->phong_id = $allPhongIds[0];
            } else {
                $booking->phong_id = null;
            }
        }

        // Allow confirming even if dates are in the past
        $booking->trang_thai = 'da_xac_nhan';
        $booking->save();

        // Gửi mail xác nhận đặt phòng
        if ($booking->email) {
            try {
                Mail::to($booking->email)->send(new BookingConfirmed($booking->load('loaiPhong')));
            } catch (\Throwable $e) {
                // log but don't break flow
                Log::warning('Send booking confirmed mail failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.dat_phong.index')
            ->with('success', 'Phòng đã được xác nhận thành công!');
    }

    /**
     * Mark booking as paid: create (or update) invoice to 'da_thanh_toan'
     */
    public function markPaid($id)
    {
        $booking = DatPhong::with('invoice')->findOrFail($id);

        // Create invoice if missing
        $invoice = $booking->invoice;
        if (!$invoice) {
            $invoice = \App\Models\Invoice::create([
                'dat_phong_id' => $booking->id,
                'tong_tien' => $booking->tong_tien,
                'phuong_thuc' => 'tien_mat',
                'trang_thai' => 'da_thanh_toan',
            ]);
        } else {
            $invoice->update([
                'trang_thai' => 'da_thanh_toan',
            ]);
        }

        // Optionally confirm booking if still pending
        if ($booking->trang_thai === 'cho_xac_nhan') {
            $booking->trang_thai = 'da_xac_nhan';
            $booking->save();
        }

        // Gửi mail hóa đơn đã thanh toán (khách hàng)
        if ($booking->email) {
            try {
                Mail::to($booking->email)->send(new InvoicePaid($booking->load(['loaiPhong'])));
            } catch (\Throwable $e) {
                Log::warning('Send invoice mail failed: ' . $e->getMessage());
            }
        }

        // Gửi mail cho admin: đơn đã thanh toán
        try {
            $adminEmails = \App\Models\User::where('vai_tro', 'admin')
                ->where('trang_thai', 'hoat_dong')
                ->pluck('email')
                ->filter()
                ->all();
            if (!empty($adminEmails)) {
                Mail::to($adminEmails)->send(new AdminBookingEvent($booking->load(['loaiPhong']), 'paid'));
            }
        } catch (\Throwable $e) {
            Log::warning('Send admin paid mail failed: ' . $e->getMessage());
        }

        return redirect()->route('admin.dat_phong.index')
            ->with('success', 'Đã đánh dấu thanh toán và đồng bộ hóa đơn thành công.');
    }
}
