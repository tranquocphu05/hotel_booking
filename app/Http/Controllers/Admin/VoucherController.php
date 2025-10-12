<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\LoaiPhong;

use Illuminate\Http\Request;

class VoucherController extends Controller
{

    public function index()
    {
        $vouchers = Voucher::orderBy('id', 'desc')->paginate(10);
        return view('admin.voucher.index', compact('vouchers'));
    }

    public function create()
    {
       $loaiPhongs = LoaiPhong::where('trang_thai', 'hoat_dong')->get();
        return view('admin.voucher.create', compact('loaiPhongs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ma_voucher' => 'required|string|max:50|unique:voucher,ma_voucher',
            'gia_tri' => 'nullable|numeric|min:0',
            'ngay_bat_dau' => 'nullable|date',
            'ngay_ket_thuc' => 'nullable|date|after_or_equal:ngay_bat_dau',
            'so_luong' => 'nullable|integer|min:0',
            'dieu_kien' => 'nullable|string|max:255',
            'trang_thai' => 'required|in:con_han,het_han,huy',
        ]);

        Voucher::create($request->all());

        return redirect()->route('admin.voucher.index')->with('success', 'Tạo voucher thành công!');
    }

    public function edit(Voucher $voucher)
    {
    
  $loaiPhongs = \App\Models\LoaiPhong::all();

    // Trả dữ liệu ra view
    return view('admin.voucher.edit', compact('voucher', 'loaiPhongs'));
    }

    public function update(Request $request, Voucher $voucher)
    {
        $request->validate([
            'ma_voucher' => 'required|string|max:50|unique:voucher,ma_voucher,' . $voucher->id,
            'gia_tri' => 'nullable|numeric|min:0',
            'ngay_bat_dau' => 'nullable|date',
            'ngay_ket_thuc' => 'nullable|date|after_or_equal:ngay_bat_dau',
            'so_luong' => 'nullable|integer|min:0',
            'dieu_kien' => 'nullable|string|max:255',
            'trang_thai' => 'required|in:con_han,het_han,huy',
        ]);

        $voucher->update($request->all());

        return redirect()->route('admin.voucher.index')->with('success', 'Cập nhật voucher thành công!');
    }

    public function destroy(Voucher $voucher)
    {
        $voucher->delete();
        return redirect()->route('admin.voucher.index')->with('success', 'Xóa voucher thành công!');
    }
}
