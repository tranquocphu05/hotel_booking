<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoaiPhong;
use Illuminate\Http\Request;

class LoaiPhongController extends Controller
{
    // Hiển thị danh sách loại phòng
    public function index(Request $request)
    {
        $query = LoaiPhong::query();
        
        // Filter theo trạng thái nếu có
        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }
        
        $loaiPhongs = $query->orderBy('id', 'desc')->get();
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
        $validated = $request->validate([
            'ten_loai' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'gia_co_ban' => 'required|numeric|min:0',
            'trang_thai' => 'required|in:hoat_dong,ngung',
            'anh' => 'nullable|image|max:2048'
        ]);

        // Xử lý upload ảnh
        if ($request->hasFile('anh')) {
            $file = $request->file('anh');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/loai_phong'), $filename);
            $validated['anh'] = 'uploads/loai_phong/' . $filename;
        }

        LoaiPhong::create($validated);

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
        $validated = $request->validate([
            'ten_loai' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'gia_co_ban' => 'required|numeric|min:0',
            'trang_thai' => 'required|in:hoat_dong,ngung',
            'anh' => 'nullable|image|max:2048'
        ]);

        $loaiPhong = LoaiPhong::findOrFail($id);
        
        // Xử lý upload ảnh
        if ($request->hasFile('anh')) {
            // Xóa ảnh cũ nếu có
            if ($loaiPhong->anh && file_exists(public_path($loaiPhong->anh))) {
                unlink(public_path($loaiPhong->anh));
            }
            
            // Upload ảnh mới
            $file = $request->file('anh');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/loai_phong'), $filename);
            $validated['anh'] = 'uploads/loai_phong/' . $filename;
        }
        
        $loaiPhong->update($validated);

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
