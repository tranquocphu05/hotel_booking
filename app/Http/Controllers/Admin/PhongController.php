<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Phong;
use App\Models\LoaiPhong;
use Illuminate\Http\Request;

class PhongController extends Controller
{
    // Danh sách phòng
    public function index(Request $request)
    {
        $query = Phong::with('loaiPhong');
        if ($request->filled('loai_phong_id')) {
            $query->where('loai_phong_id', $request->loai_phong_id);
        }
        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }
        $phongs = $query->orderBy('id', 'desc')->get();
        $loaiPhongs = LoaiPhong::all();
        return view('admin.phong.index', compact('phongs', 'loaiPhongs'));
    }

    // Form thêm
    public function create()
    {
        $loaiPhongs = LoaiPhong::all();
        return view('admin.phong.create', compact('loaiPhongs'));
    }

    // Lưu phòng mới
    public function store(Request $request)
    {
        $request->validate([
            'ten_phong' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'gia' => 'required|numeric|min:0|max:999999999',
            'trang_thai' => 'required|in:hien,an,bao_tri',
            'loai_phong_id' => 'required|exists:loai_phong,id',
            'img' => 'nullable|image|max:2048'
        ]);
        $data = $request->all();
        if ($request->hasFile('img')) {
            $file = $request->file('img');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/phong'), $filename);
            $data['img'] = 'uploads/phong/' . $filename;
        }
        Phong::create($data);
        return redirect()->route('admin.phong.index')->with('success', 'Thêm phòng thành công!');
    }

    // Form chỉnh sửa
    public function edit($id)
    {
        $phong = Phong::findOrFail($id);
        $loaiPhongs = LoaiPhong::all();
        return view('admin.phong.edit', compact('phong', 'loaiPhongs'));
    }

    // Cập nhật phòng
    public function update(Request $request, $id)
    {
        $request->validate([
            'ten_phong' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'gia' => 'required|numeric|min:0|max:999999999',
            'trang_thai' => 'required|in:hien,an,bao_tri',
            'loai_phong_id' => 'required|exists:loai_phong,id',
            'img' => 'nullable|image|max:2048'
        ]);
        $phong = Phong::findOrFail($id);
        $data = $request->all();
        if ($request->hasFile('img')) {
            if ($phong->img && file_exists(public_path($phong->img))) {
                unlink(public_path($phong->img));
            }
            $file = $request->file('img');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/phong'), $filename);
            $data['img'] = 'uploads/phong/' . $filename;
        }
        $phong->update($data);
        return redirect()->route('admin.phong.index')->with('success', 'Cập nhật phòng thành công!');
    }

    // Xóa
    public function destroy($id)
    {
        $phong = Phong::findOrFail($id);
        if ($phong->img && file_exists(public_path($phong->img))) {
            unlink(public_path($phong->img));
        }
        $phong->delete();
        return redirect()->route('admin.phong.index')->with('success', 'Xóa phòng thành công!');
    }

    // Hiển thị phòng trống theo thời gian
    public function available(Request $request)
    {
        $ngayNhan = $request->input('ngay_nhan', now()->format('Y-m-d'));
        $ngayTra = $request->input('ngay_tra', now()->addDay()->format('Y-m-d'));
        
        // Lấy tất cả phòng
        $allRooms = Phong::with('loaiPhong')->get();
        
        // Lấy các phòng đã được đặt trong khoảng thời gian này
        $bookedRoomIds = \App\Models\DatPhong::where(function($query) use ($ngayNhan, $ngayTra) {
            $query->where(function($q) use ($ngayNhan, $ngayTra) {
                // Đặt phòng bắt đầu trong khoảng thời gian
                $q->whereBetween('ngay_nhan', [$ngayNhan, $ngayTra])
                  ->orWhereBetween('ngay_tra', [$ngayNhan, $ngayTra])
                  // Hoặc đặt phòng bao trùm khoảng thời gian
                  ->orWhere(function($subQ) use ($ngayNhan, $ngayTra) {
                      $subQ->where('ngay_nhan', '<=', $ngayNhan)
                           ->where('ngay_tra', '>=', $ngayTra);
                  });
            });
        })
        ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
        ->pluck('phong_id')
        ->toArray();
        
        // Lấy phòng trống (không bị đặt và không bị chống)
        $availableRooms = $allRooms->filter(function($room) use ($bookedRoomIds) {
            return !in_array($room->id, $bookedRoomIds) && 
                   $room->trang_thai === 'hien' && 
                   $room->trang_thai !== 'chong';
        });
        
        // Lấy loại phòng để filter
        $loaiPhongs = LoaiPhong::all();
        
        // Đếm số phòng đã đặt
        $bookedCount = count($bookedRoomIds);
        
        return view('admin.phong.available', compact('availableRooms', 'loaiPhongs', 'ngayNhan', 'ngayTra', 'bookedRoomIds', 'bookedCount'));
    }

    // Hiển thị chi tiết phòng
    public function show($id)
    {
        $phong = Phong::with('loaiPhong')->findOrFail($id);
        return view('admin.phong.show', compact('phong'));
    }

    // Chống phòng trực tiếp
    public function blockRoom($id)
    {
        $phong = Phong::findOrFail($id);
        
        // Kiểm tra phòng có đang được đặt không
        $hasActiveBooking = \App\Models\DatPhong::where('phong_id', $id)
            ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
            ->exists();
            
        if ($hasActiveBooking) {
            return redirect()->route('admin.phong.index')
                ->with('error', 'Không thể chống phòng đang được đặt');
        }
        
        // Cập nhật trạng thái phòng thành "chong"
        $phong->update(['trang_thai' => 'chong']);
        
        return redirect()->route('admin.phong.index')
            ->with('success', 'Đã chống phòng thành công! Phòng không thể đặt được cho đến khi hủy chống.');
    }
}
