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
}
