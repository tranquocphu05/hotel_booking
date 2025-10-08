<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoaiPhong;
use Illuminate\Http\Request;

class LoaiPhongController extends Controller
{
    // Hiển thị danh sách loại phòng
    public function index()
    {
        $loaiPhongs = LoaiPhong::orderBy('id', 'desc')->get();
        return view('admin.loai_phong.index', compact('loaiPhongs'));
    }

    // Form thêm loại phòng
    public function create()
    {
        return view('admin.loai_phong.create');
    }

    // Lưu loại phòng mới
    public function store(Request $request)
    {
        $request->validate([
            'ten_loai' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'gia_co_ban' => 'required|numeric|min:0',
            'trang_thai' => 'required|in:hoat_dong,ngung',
        ]);

        LoaiPhong::create($request->all());

        return redirect()->route('admin.loai_phong.index')->with('success', 'Thêm loại phòng thành công!');
    }

    // Form chỉnh sửa
    public function edit($id)
    {
        $loaiPhong = LoaiPhong::findOrFail($id);
        return view('admin.loai_phong.edit', compact('loaiPhong'));
    }

    // Cập nhật loại phòng
    public function update(Request $request, $id)
    {
        $request->validate([
            'ten_loai' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'gia_co_ban' => 'required|numeric|min:0',
            'trang_thai' => 'required|in:hoat_dong,ngung',
        ]);

        $loaiPhong = LoaiPhong::findOrFail($id);
        $loaiPhong->update($request->all());

        return redirect()->route('admin.loai_phong.index')->with('success', 'Cập nhật thành công!');
    }

    // Xóa loại phòng
    public function destroy($id)
    {
        $loaiPhong = LoaiPhong::findOrFail($id);
        $loaiPhong->delete();

        return redirect()->route('admin.loai_phong.index')->with('success', 'Xóa loại phòng thành công!');
    }
}
