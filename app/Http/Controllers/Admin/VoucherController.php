<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\LoaiPhong;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    // Hiển thị danh sách Vouchers và xử lý LỌC
    public function index(Request $request)
    {
        // 1. Lấy tất cả Loại phòng để truyền sang view (cho dropdown Lọc)
        $loaiPhongs = LoaiPhong::all();

        // 2. Bắt đầu query Builder cho Voucher
        $vouchersQuery = Voucher::query();

        // Luôn eager load mối quan hệ loaiPhong để hiển thị trong bảng
        $vouchersQuery->with('loaiPhong');

        // =================================================================
        //                 XỬ LÝ LOGIC LỌC TỪ REQUEST
        // =================================================================

        // Lọc theo Loại phòng áp dụng (loai_phong_id)
        if ($request->filled('loai_phong_id')) {
            $vouchersQuery->where('loai_phong_id', $request->loai_phong_id);
        }

        // Lọc theo Trạng thái (trang_thai)
        if ($request->filled('trang_thai')) {
            $vouchersQuery->where('trang_thai', $request->trang_thai);
        }

        // Sắp xếp theo ID giảm dần (mới nhất trước)
        $vouchersQuery->orderBy('id', 'desc');

        // Lấy kết quả đã lọc và phân trang (5 dòng/trang)
        $vouchers = $vouchersQuery->paginate(5);

        // Điều chỉnh lại đường dẫn phân trang để giữ lại các tham số lọc
        $vouchers->appends($request->all());

        // Truyền dữ liệu cần thiết sang view Blade
        return view('admin.voucher.index', compact('vouchers', 'loaiPhongs'));
    }

    // Form thêm mới
    public function create()
    {
        $loaiPhongs = LoaiPhong::all();
        return view('admin.voucher.create', compact('loaiPhongs'));
    }

    // Lưu voucher mới
    public function store(Request $request)
    {
        $request->validate([
            'ma_voucher' => [
                'required',
                'string',
                'max:50',
                'unique:voucher,ma_voucher',
                'regex:/^(?![0-9]+$)[A-Za-z0-9]+$/',
            ],
            'gia_tri'        => 'required|numeric|min:1|max:100',
            'ngay_bat_dau'   => 'required|date',
            'ngay_ket_thuc'  => 'required|date|after_or_equal:ngay_bat_dau',
            'so_luong'       => 'required|integer|min:1|max:9999',
            'loai_phong_id'  => 'nullable|exists:loai_phong,id',
            'dieu_kien'      => 'required|string|max:255',
            'trang_thai'     => 'required|in:con_han,het_han,huy',
        ], [
            'ma_voucher.required' => '* Không được để trống.',
            'ma_voucher.unique'   => '* Mã voucher đã tồn tại.',
            'ma_voucher.regex'    => '* Mã voucher chỉ được chứa chữ và số, và không được toàn là số.',
            'gia_tri.required'    => '* Không được để trống.',
            'gia_tri.numeric'     => '* Phải là số.',
            'gia_tri.min'         => '* Giá trị giảm phải lớn hơn 0.',
            'gia_tri.max'         => '* Giá trị giảm không được vượt quá 100%.',
            'so_luong.required'   => '* Không được để trống số lượng.',
            'so_luong.integer'    => '* Số lượng phải là số nguyên.',
            'so_luong.min'        => '* Số lượng phải lớn hơn 0.',
            'ngay_bat_dau.required'  => '* Vui lòng chọn ngày bắt đầu.',
            'ngay_ket_thuc.required' => '* Vui lòng chọn ngày kết thúc.',
            'ngay_ket_thuc.after_or_equal' => '* Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',
            'dieu_kien.required' => '* Không được để trống.',
            'trang_thai.required' => '* Vui lòng chọn trạng thái.',
        ]);

        Voucher::create($request->all());

        return redirect()->route('admin.voucher.index')->with('success', 'Thêm voucher thành công!');
    }

    // Form sửa voucher
    public function edit(Voucher $voucher)
    {
        $loaiPhongs = LoaiPhong::all();
        return view('admin.voucher.edit', compact('voucher', 'loaiPhongs'));
    }

    // Cập nhật voucher
    public function update(Request $request, Voucher $voucher)
    {
        $request->validate([
            'ma_voucher' => [
                'required',
                'string',
                'max:50',
                'unique:voucher,ma_voucher,' . $voucher->id,
                'regex:/^(?![0-9]+$)[A-Za-z0-9]+$/', // không toàn số, chỉ chữ + số
            ],
            'gia_tri'        => 'required|numeric|min:1|max:100',
            'ngay_bat_dau'   => 'required|date',
            'ngay_ket_thuc'  => 'required|date|after_or_equal:ngay_bat_dau',
            'so_luong'       => 'required|integer|min:1|max:9999',
            'loai_phong_id'  => 'nullable|exists:loai_phong,id',
            'dieu_kien'      => 'required|string|max:255',
            'trang_thai'     => 'required|in:con_han,het_han,huy',
        ], [
            'ma_voucher.required' => '* Không được để trống.',
            'ma_voucher.unique'   => '* Mã voucher đã tồn tại.',
            'ma_voucher.regex'    => '* Mã voucher chỉ được chứa chữ và số, và không được toàn là số.',
            'gia_tri.required'    => '* Không được để trống.',
            'gia_tri.numeric'     => '* Phải là số.',
            'gia_tri.min'         => '* Giá trị giảm phải lớn hơn 0.',
            'gia_tri.max'         => '* Giá trị giảm không được vượt quá 100%.',
            'so_luong.required'   => '* Không được để trống số lượng.',
            'so_luong.integer'    => '* Số lượng phải là số nguyên.',
            'so_luong.min'        => '* Số lượng phải lớn hơn 0.',
            'ngay_bat_dau.required'  => '* Vui lòng chọn ngày bắt đầu.',
            'ngay_ket_thuc.required' => '* Vui lòng chọn ngày kết thúc.',
            'ngay_ket_thuc.after_or_equal' => '* Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',
            'dieu_kien.required' => '* Không được để trống.',
            'trang_thai.required' => '* Vui lòng chọn trạng thái.',
        ]);

        $voucher->update($request->all());

        return redirect()->route('admin.voucher.index')->with('success', 'Cập nhật voucher thành công!');
    }

    // Xóa voucher
    public function destroy(Voucher $voucher)
    {
        $voucher->delete();
        return redirect()->route('admin.voucher.index')->with('success', 'Xóa voucher thành công!');
    }
}
