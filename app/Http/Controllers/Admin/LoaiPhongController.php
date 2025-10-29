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
            'ten_loai' => 'required|string|max:255|unique:loai_phong,ten_loai|regex:/^[\pL\s]+$/u',
            'mo_ta' => 'nullable|string|max:1000|regex:/^[\pL\pN\s.,()!?\-\'":;%&@\/]+$/u',
            'gia_co_ban' => 'required|numeric|min:100000|max:99999999',
            'anh' => 'required|image|max:2048'
        ], [
            'ten_loai.required' => 'Tên loại phòng không được để trống.',
            'ten_loai.unique' => 'Tên loại phòng đã tồn tại.',
            'ten_loai.regex' => 'Tên loại phòng chỉ được chứa chữ cái và khoảng trắng.',
            'gia_co_ban.required' => 'Giá cơ bản là bắt buộc.',
            'gia_co_ban.min' => 'Giá cơ bản phải lớn hơn hoặc bằng 100.000đ',
            'gia_co_ban.max' => 'Giá cơ bản không được vượt quá 99.999.999đ',
            'anh.required' => 'Ảnh loại phòng là bắt buộc.',
            'anh.image' => 'Tệp tải lên phải là một hình ảnh.',
            'anh.max' => 'Kích thước hình ảnh không được vượt quá 2MB.',
            'mo_ta.max' => 'Mô tả không được vượt quá 1000 ký tự.',
            'mo_ta.regex' => 'Mô tả chỉ được chứa chữ cái, số và các ký tự đặc biệt cơ bản.',
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
            'ten_loai' => 'required|string|max:255|regex:/^[\pL\s]+$/u',
            'mo_ta' => 'nullable|string|max:1000|regex:/^[\pL\pN\s.,()!?\-\'":;%&@\/]+$/u',
            'gia_co_ban' => 'required|numeric|min:100000|max:99999999',
            'anh' => 'required|image|max:2048'
        ], [
            'ten_loai.required' => 'Tên loại phòng không được để trống.',
            'ten_loai.regex' => 'Tên loại phòng chỉ được chứa chữ cái và khoảng trắng.',
            'gia_co_ban.required' => 'Giá cơ bản là bắt buộc.',
            'gia_co_ban.min' => 'Giá cơ bản phải lớn hơn hoặc bằng 100.000đ',
            'gia_co_ban.max' => 'Giá cơ bản không được vượt quá 99.999.999đ',
            'anh.required' => 'Ảnh loại phòng là bắt buộc.',
            'anh.image' => 'Tệp tải lên phải là một hình ảnh.',
            'anh.max' => 'Kích thước hình ảnh không được vượt quá 2MB.',
            'mo_ta.max' => 'Mô tả không được vượt quá 1000 ký tự.',
            'mo_ta.regex' => 'Mô tả chỉ được chứa chữ cái, số và các ký tự đặc biệt cơ bản.',
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
