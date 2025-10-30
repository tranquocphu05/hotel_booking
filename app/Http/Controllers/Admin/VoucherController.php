<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\LoaiPhong;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VoucherController extends Controller
{
    // ============================================================
    // 1️⃣ HIỂN THỊ DANH SÁCH VOUCHER + LỌC THEO LOẠI PHÒNG, TRẠNG THÁI
    // ============================================================
    public function index(Request $request)
    {
        $loaiPhongs = LoaiPhong::all();
        $vouchersQuery = Voucher::with('loaiPhong');

        // Lọc loại phòng
        if ($request->filled('loai_phong_id')) {
            $vouchersQuery->where('loai_phong_id', $request->loai_phong_id);
        }

        // Lọc trạng thái
        if ($request->filled('trang_thai')) {
            $vouchersQuery->where('trang_thai', $request->trang_thai);
        }

        // Tự động cập nhật trạng thái hết hạn
        foreach (Voucher::all() as $v) {
            if (Carbon::parse($v->ngay_ket_thuc)->isPast() && $v->trang_thai === 'con_han') {
                $v->update(['trang_thai' => 'het_han']);
            }
        }

        $vouchers = $vouchersQuery->orderBy('id', 'desc')->paginate(10);
        $vouchers->appends($request->all());

        return view('admin.voucher.index', compact('vouchers', 'loaiPhongs'));
    }

    // ============================================================
    // 2️⃣ FORM THÊM MỚI
    // ============================================================
    public function create()
    {
        $loaiPhongs = LoaiPhong::all();
        return view('admin.voucher.create', compact('loaiPhongs'));
    }

    // ============================================================
    // 3️⃣ LƯU VOUCHER MỚI
    // ============================================================
    public function store(Request $request)
    {
        $request->validate([
            'ma_voucher'     => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9_-]+$/', // ✅ Cho phép chữ thường
                'unique:voucher,ma_voucher',
            ],
            'gia_tri'        => 'required|numeric|min:1|max:100',
            'ngay_bat_dau'   => 'required|date|after_or_equal:today',
            'ngay_ket_thuc'  => 'required|date|after:ngay_bat_dau',
            'so_luong'       => 'required|integer|min:1|max:9999',
            'loai_phong_id'  => 'nullable|exists:loai_phong,id',
            'dieu_kien'      => 'required|string|max:255',
            'trang_thai'     => 'required|in:con_han,het_han,huy',
        ], [
            'ma_voucher.required' => '* Không được để trống mã voucher.',
            'ma_voucher.regex'    => '* Mã voucher chỉ được chứa chữ cái, số, dấu gạch dưới hoặc gạch ngang.',
            'ma_voucher.unique'   => '* Mã voucher đã tồn tại.',
            'gia_tri.required'    => '* Vui lòng nhập giá trị giảm.',
            'gia_tri.numeric'     => '* Giá trị giảm phải là số.',
            'gia_tri.min'         => '* Phải lớn hơn 0.',
            'gia_tri.max'         => '* Không được vượt quá 100%.',
            'ngay_bat_dau.required' => '* Vui lòng chọn ngày bắt đầu.',
            'ngay_bat_dau.after_or_equal' => '* Ngày bắt đầu không được nhỏ hơn hôm nay.',
            'ngay_ket_thuc.required' => '* Vui lòng chọn ngày kết thúc.',
            'ngay_ket_thuc.after'    => '* Ngày kết thúc phải sau ngày bắt đầu.',
            'so_luong.required' => '* Vui lòng nhập số lượng phát hành.',
            'so_luong.integer'  => '* Số lượng phải là số nguyên.',
            'so_luong.min'      => '* Số lượng phải lớn hơn 0.',
            'so_luong.max'      => '* Không được vượt quá 9999 voucher.',
            'dieu_kien.required' => '* Vui lòng nhập điều kiện áp dụng.',
            'trang_thai.required' => '* Vui lòng chọn trạng thái.',
            'trang_thai.in' => '* Trạng thái không hợp lệ.',
            'loai_phong_id.exists' => '* Loại phòng không hợp lệ.',
        ]);

        // ✅ Tự động chuyển mã voucher sang chữ hoa (nếu bạn muốn đồng bộ)
        $data = $request->all();

        // Check logic trạng thái
        if ($data['trang_thai'] === 'con_han' && strtotime($data['ngay_ket_thuc']) < time()) {
            return back()->withErrors(['trang_thai' => 'Voucher đã hết hạn, không thể để trạng thái "Còn hạn".'])->withInput();
        }

        Voucher::create($data);

        return redirect()->route('admin.voucher.index')->with('success', 'Thêm voucher thành công!');
    }

    // ============================================================
    // 4️⃣ FORM SỬA
    // ============================================================
    public function edit(Voucher $voucher)
    {
        $loaiPhongs = LoaiPhong::all();
        return view('admin.voucher.edit', compact('voucher', 'loaiPhongs'));
    }

    // ============================================================
    // 5️⃣ CẬP NHẬT VOUCHER
    // ============================================================
    public function update(Request $request, Voucher $voucher)
    {
        $request->validate([
            'ma_voucher'     => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9_-]+$/', // ✅ Cho phép chữ thường
                'unique:voucher,ma_voucher,' . $voucher->id,
            ],
            'gia_tri'        => 'required|numeric|min:1|max:100',
            'ngay_bat_dau'   => 'required|date',
            'ngay_ket_thuc'  => 'required|date|after:ngay_bat_dau',
            'so_luong'       => 'required|integer|min:1|max:9999',
            'loai_phong_id'  => 'nullable|exists:loai_phong,id',
            'dieu_kien'      => 'required|string|max:255',
            'trang_thai'     => 'required|in:con_han,het_han,huy',
        ], [
            'ma_voucher.required' => '* Không được để trống mã voucher.',
            'ma_voucher.regex'    => '* Mã voucher chỉ được chứa chữ cái, số, dấu gạch dưới hoặc gạch ngang.',
            'ma_voucher.unique'   => '* Mã voucher đã tồn tại.',
            'gia_tri.required'    => '* Vui lòng nhập giá trị giảm.',
            'gia_tri.numeric'     => '* Giá trị giảm phải là số.',
            'gia_tri.min'         => '* Phải lớn hơn 0.',
            'gia_tri.max'         => '* Không được vượt quá 100%.',
            'ngay_bat_dau.required' => '* Vui lòng chọn ngày bắt đầu.',
            'ngay_ket_thuc.required' => '* Vui lòng chọn ngày kết thúc.',
            'ngay_ket_thuc.after'    => '* Ngày kết thúc phải sau ngày bắt đầu.',
            'so_luong.required' => '* Vui lòng nhập số lượng phát hành.',
            'so_luong.integer'  => '* Số lượng phải là số nguyên.',
            'so_luong.min'      => '* Số lượng phải lớn hơn 0.',
            'so_luong.max'      => '* Không được vượt quá 9999 voucher.',
            'dieu_kien.required' => '* Vui lòng nhập điều kiện áp dụng.',
            'trang_thai.required' => '* Vui lòng chọn trạng thái.',
            'trang_thai.in' => '* Trạng thái không hợp lệ.',
            'loai_phong_id.exists' => '* Loại phòng không hợp lệ.',
        ]);

        $data = $request->all();

        if (strtotime($data['ngay_ket_thuc']) < strtotime($data['ngay_bat_dau'])) {
            return back()->withErrors(['ngay_ket_thuc' => 'Ngày kết thúc không thể trước ngày bắt đầu.'])->withInput();
        }

        if (Carbon::parse($data['ngay_ket_thuc'])->isPast()) {
            $data['trang_thai'] = 'het_han';
        }

        $voucher->update($data);

        return redirect()->route('admin.voucher.index')->with('success', 'Cập nhật voucher thành công!');
    }

    // ============================================================
    // 6️⃣ XÓA VOUCHER
    // ============================================================
    public function destroy(Voucher $voucher)
    {
        $voucher->delete();
        return redirect()->route('admin.voucher.index')->with('success', 'Xóa voucher thành công!');
    }
}
