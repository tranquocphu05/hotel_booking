<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoaiPhong;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LoaiPhongController extends Controller
{
    /**
     * Hiển thị danh sách loại phòng
     */
    public function index()
    {
        $loaiPhongs = LoaiPhong::orderByDesc('id')->get();
        return view('admin.loai_phong.index', compact('loaiPhongs'));
    }

    /**
     * Form thêm loại phòng
     */
    public function create()
    {
        return view('admin.loai_phong.create');
    }

    /**
     * Lưu loại phòng mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ten_loai' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'gia_co_ban' => 'required|numeric|min:0',
            'trang_thai' => 'required|in:hoat_dong,ngung',
            'anh' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Xử lý upload ảnh (nếu có)
        if ($request->hasFile('anh')) {
            $file = $request->file('anh');
            $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());

            // Tạo thư mục nếu chưa có
            $uploadPath = public_path('uploads/loai_phong');
            if (!File::exists($uploadPath)) {
                File::makeDirectory($uploadPath, 0755, true);
            }

            // Di chuyển file
            $file->move($uploadPath, $filename);

            // Lưu đường dẫn tương đối (để asset() hiển thị đúng)
            $validated['anh'] = 'uploads/loai_phong/' . $filename;
        }

        LoaiPhong::create($validated);

        return redirect()->route('admin.loai_phong.index')
            ->with('success', 'Thêm loại phòng thành công!');
    }

    /**
     * Form chỉnh sửa
     */
    public function edit($id)
    {
        $loaiPhong = LoaiPhong::findOrFail($id);
        return view('admin.loai_phong.edit', compact('loaiPhong'));
    }

    /**
     * Cập nhật loại phòng
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'ten_loai' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'gia_co_ban' => 'required|numeric|min:0',
            'trang_thai' => 'required|in:hoat_dong,ngung',
            'anh' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $loaiPhong = LoaiPhong::findOrFail($id);

        // Nếu có upload ảnh mới
        if ($request->hasFile('anh')) {
            // Xoá ảnh cũ nếu có
            if ($loaiPhong->anh && File::exists(public_path($loaiPhong->anh))) {
                File::delete(public_path($loaiPhong->anh));
            }

            $file = $request->file('anh');
            $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $uploadPath = public_path('uploads/loai_phong');

            if (!File::exists($uploadPath)) {
                File::makeDirectory($uploadPath, 0755, true);
            }

            $file->move($uploadPath, $filename);
            $validated['anh'] = 'uploads/loai_phong/' . $filename;
        }

        $loaiPhong->update($validated);

        return redirect()->route('admin.loai_phong.index')
            ->with('success', 'Cập nhật loại phòng thành công!');
    }

    /**
     * Xóa loại phòng
     */
    public function destroy($id)
    {
        $loaiPhong = LoaiPhong::findOrFail($id);

        // Xóa ảnh nếu có
        if ($loaiPhong->anh && File::exists(public_path($loaiPhong->anh))) {
            File::delete(public_path($loaiPhong->anh));
        }

        $loaiPhong->delete();

        return redirect()->route('admin.loai_phong.index')
            ->with('success', 'Xóa loại phòng thành công!');
    }
}
