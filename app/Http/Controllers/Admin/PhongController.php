<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Phong;
use App\Models\LoaiPhong;
use Illuminate\Http\Request;

class PhongController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Phong::with('loaiPhong');

        // Filter theo loại phòng
        if ($request->filled('loai_phong_id')) {
            $query->where('loai_phong_id', $request->loai_phong_id);
        }

        // Filter theo trạng thái
        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }

        // Filter theo tầng
        if ($request->filled('tang')) {
            $query->where('tang', $request->tang);
        }

        // Search theo số phòng hoặc tên phòng
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('so_phong', 'like', '%' . $request->search . '%')
                  ->orWhere('ten_phong', 'like', '%' . $request->search . '%');
            });
        }

        $phongs = $query->orderBy('tang')->orderBy('so_phong')->paginate(15);
        $loaiPhongs = LoaiPhong::where('trang_thai', 'hoat_dong')->get();

        return view('admin.phong.index', compact('phongs', 'loaiPhongs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $loaiPhongs = LoaiPhong::where('trang_thai', 'hoat_dong')->get();
        return view('admin.phong.create', compact('loaiPhongs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'loai_phong_id' => 'required|exists:loai_phong,id',
            'so_phong' => [
                'required',
                'string',
                'max:20',
                'min:1',
                'unique:phong,so_phong',
                'regex:/^[A-Za-z0-9\-\s]+$/',
            ],
            'ten_phong' => 'nullable|string|max:255',
            'tang' => 'nullable|integer|min:1|max:50',
            'huong_cua_so' => 'nullable|in:bien,nui,thanh_pho,san_vuon',
            'co_ban_cong' => 'nullable|boolean',
            'co_view_dep' => 'nullable|boolean',
            'gia_rieng' => 'nullable|numeric|min:0|max:999999999',
            'gia_bo_sung' => 'nullable|numeric|min:0|max:999999999',
            'trang_thai' => 'required|in:trong,dang_thue,dang_don,bao_tri',
            'ghi_chu' => 'nullable|string|max:1000',
        ], [
            'loai_phong_id.required' => 'Vui lòng chọn loại phòng.',
            'loai_phong_id.exists' => 'Loại phòng không tồn tại trong hệ thống.',
            'so_phong.required' => 'Vui lòng nhập số phòng.',
            'so_phong.string' => 'Số phòng phải là chuỗi ký tự.',
            'so_phong.min' => 'Số phòng phải có ít nhất 1 ký tự.',
            'so_phong.max' => 'Số phòng không được vượt quá 20 ký tự.',
            'so_phong.unique' => 'Số phòng này đã tồn tại trong hệ thống. Vui lòng chọn số phòng khác.',
            'so_phong.regex' => 'Số phòng chỉ được chứa chữ cái, số, dấu gạch ngang và khoảng trắng.',
            'ten_phong.string' => 'Tên phòng phải là chuỗi ký tự.',
            'ten_phong.max' => 'Tên phòng không được vượt quá 255 ký tự.',
            'tang.integer' => 'Tầng phải là số nguyên.',
            'tang.min' => 'Tầng phải lớn hơn hoặc bằng 1.',
            'tang.max' => 'Tầng không được vượt quá 50.',
            'huong_cua_so.in' => 'Hướng cửa sổ không hợp lệ. Chỉ chấp nhận: Biển, Núi, Thành phố, Sân vườn.',
            'co_ban_cong.boolean' => 'Trường "Có ban công" phải là true hoặc false.',
            'co_view_dep.boolean' => 'Trường "Có view đẹp" phải là true hoặc false.',
            'gia_rieng.numeric' => 'Giá riêng phải là số.',
            'gia_rieng.min' => 'Giá riêng phải lớn hơn hoặc bằng 0.',
            'gia_rieng.max' => 'Giá riêng không được vượt quá 999,999,999 VNĐ.',
            'gia_bo_sung.numeric' => 'Giá bổ sung phải là số.',
            'gia_bo_sung.min' => 'Giá bổ sung phải lớn hơn hoặc bằng 0.',
            'gia_bo_sung.max' => 'Giá bổ sung không được vượt quá 999,999,999 VNĐ.',
            'trang_thai.required' => 'Vui lòng chọn trạng thái phòng.',
            'trang_thai.in' => 'Trạng thái không hợp lệ. Chỉ chấp nhận: Trống, Đang thuê, Đang dọn, Bảo trì.',
            'ghi_chu.string' => 'Ghi chú phải là chuỗi ký tự.',
            'ghi_chu.max' => 'Ghi chú không được vượt quá 1000 ký tự.',
        ]);

        // Kiểm tra loại phòng có đang hoạt động không
        $loaiPhong = LoaiPhong::find($validated['loai_phong_id']);
        if (!$loaiPhong) {
            return back()->withErrors(['loai_phong_id' => 'Loại phòng không tồn tại.'])->withInput();
        }

        if ($loaiPhong->trang_thai !== 'hoat_dong') {
            return back()->withErrors(['loai_phong_id' => 'Loại phòng này không đang hoạt động. Vui lòng chọn loại phòng khác.'])->withInput();
        }

        // Xử lý checkbox boolean
        $validated['co_ban_cong'] = $request->has('co_ban_cong') ? true : false;
        $validated['co_view_dep'] = $request->has('co_view_dep') ? true : false;

        // Trim whitespace
        $validated['so_phong'] = trim($validated['so_phong']);
        if (isset($validated['ten_phong'])) {
            $validated['ten_phong'] = trim($validated['ten_phong']) ?: null;
        }
        if (isset($validated['ghi_chu'])) {
            $validated['ghi_chu'] = trim($validated['ghi_chu']) ?: null;
        }

        try {
            $phong = Phong::create($validated);

            // Cập nhật so_luong_phong và so_luong_trong của loại phòng
            $loaiPhong->increment('so_luong_phong');
            if ($validated['trang_thai'] === 'trong') {
                $loaiPhong->increment('so_luong_trong');
            }

            return redirect()->route('admin.phong.index')
                ->with('success', 'Thêm phòng "' . $phong->so_phong . '" thành công!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Có lỗi xảy ra khi thêm phòng: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $phong = Phong::with(['loaiPhong', 'datPhongs' => function($query) {
            $query->orderBy('ngay_nhan', 'desc')->limit(10);
        }])->findOrFail($id);

        return view('admin.phong.show', compact('phong'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $phong = Phong::findOrFail($id);
        $loaiPhongs = LoaiPhong::where('trang_thai', 'hoat_dong')->get();
        return view('admin.phong.edit', compact('phong', 'loaiPhongs'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $phong = Phong::findOrFail($id);
        $oldLoaiPhongId = $phong->loai_phong_id;
        $oldTrangThai = $phong->trang_thai;

        // Kiểm tra xem phòng có đang được sử dụng trong booking đang hoạt động không
        $hasActiveBooking = $phong->datPhongs()
            ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan', 'da_thanh_toan'])
            ->exists();
        
        // Kiểm tra qua pivot table
        $hasActivePivotBooking = false;
        if (method_exists($phong, 'datPhongPhongs')) {
            $hasActivePivotBooking = $phong->datPhongPhongs()
                ->whereHas('datPhong', function($q) {
                    $q->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan', 'da_thanh_toan']);
                })
                ->exists();
        }

        if ($hasActiveBooking || $hasActivePivotBooking) {
            // Nếu có booking đang hoạt động, chỉ cho phép thay đổi một số trường (không cho đổi số phòng)
            $validated = $request->validate([
                'loai_phong_id' => 'required|exists:loai_phong,id',
                'ten_phong' => 'nullable|string|max:255',
                'tang' => 'nullable|integer|min:1|max:50',
                'huong_cua_so' => 'nullable|in:bien,nui,thanh_pho,san_vuon',
                'co_ban_cong' => 'nullable|boolean',
                'co_view_dep' => 'nullable|boolean',
                'gia_rieng' => 'nullable|numeric|min:0|max:999999999',
                'gia_bo_sung' => 'nullable|numeric|min:0|max:999999999',
                'trang_thai' => 'required|in:trong,dang_thue,dang_don,bao_tri',
                'ghi_chu' => 'nullable|string|max:1000',
            ], [
                'loai_phong_id.required' => 'Vui lòng chọn loại phòng.',
                'loai_phong_id.exists' => 'Loại phòng không tồn tại trong hệ thống.',
                'ten_phong.string' => 'Tên phòng phải là chuỗi ký tự.',
                'ten_phong.max' => 'Tên phòng không được vượt quá 255 ký tự.',
                'tang.integer' => 'Tầng phải là số nguyên.',
                'tang.min' => 'Tầng phải lớn hơn hoặc bằng 1.',
                'tang.max' => 'Tầng không được vượt quá 50.',
                'huong_cua_so.in' => 'Hướng cửa sổ không hợp lệ. Chỉ chấp nhận: Biển, Núi, Thành phố, Sân vườn.',
                'co_ban_cong.boolean' => 'Trường "Có ban công" phải là true hoặc false.',
                'co_view_dep.boolean' => 'Trường "Có view đẹp" phải là true hoặc false.',
                'gia_rieng.numeric' => 'Giá riêng phải là số.',
                'gia_rieng.min' => 'Giá riêng phải lớn hơn hoặc bằng 0.',
                'gia_rieng.max' => 'Giá riêng không được vượt quá 999,999,999 VNĐ.',
                'gia_bo_sung.numeric' => 'Giá bổ sung phải là số.',
                'gia_bo_sung.min' => 'Giá bổ sung phải lớn hơn hoặc bằng 0.',
                'gia_bo_sung.max' => 'Giá bổ sung không được vượt quá 999,999,999 VNĐ.',
                'trang_thai.required' => 'Vui lòng chọn trạng thái phòng.',
                'trang_thai.in' => 'Trạng thái không hợp lệ. Chỉ chấp nhận: Trống, Đang thuê, Đang dọn, Bảo trì.',
                'ghi_chu.string' => 'Ghi chú phải là chuỗi ký tự.',
                'ghi_chu.max' => 'Ghi chú không được vượt quá 1000 ký tự.',
            ]);
            // Giữ nguyên số phòng nếu có booking đang hoạt động
            $validated['so_phong'] = $phong->so_phong;
        } else {
            // Nếu không có booking, cho phép thay đổi tất cả
            $validated = $request->validate([
                'loai_phong_id' => 'required|exists:loai_phong,id',
                'so_phong' => [
                    'required',
                    'string',
                    'max:20',
                    'min:1',
                    'unique:phong,so_phong,' . $id,
                    'regex:/^[A-Za-z0-9\-\s]+$/',
                ],
                'ten_phong' => 'nullable|string|max:255',
                'tang' => 'nullable|integer|min:1|max:50',
                'huong_cua_so' => 'nullable|in:bien,nui,thanh_pho,san_vuon',
                'co_ban_cong' => 'nullable|boolean',
                'co_view_dep' => 'nullable|boolean',
                'gia_rieng' => 'nullable|numeric|min:0|max:999999999',
                'gia_bo_sung' => 'nullable|numeric|min:0|max:999999999',
                'trang_thai' => 'required|in:trong,dang_thue,dang_don,bao_tri',
                'ghi_chu' => 'nullable|string|max:1000',
            ], [
                'loai_phong_id.required' => 'Vui lòng chọn loại phòng.',
                'loai_phong_id.exists' => 'Loại phòng không tồn tại trong hệ thống.',
                'so_phong.required' => 'Vui lòng nhập số phòng.',
                'so_phong.string' => 'Số phòng phải là chuỗi ký tự.',
                'so_phong.min' => 'Số phòng phải có ít nhất 1 ký tự.',
                'so_phong.max' => 'Số phòng không được vượt quá 20 ký tự.',
                'so_phong.unique' => 'Số phòng này đã tồn tại trong hệ thống. Vui lòng chọn số phòng khác.',
                'so_phong.regex' => 'Số phòng chỉ được chứa chữ cái, số, dấu gạch ngang và khoảng trắng.',
                'ten_phong.string' => 'Tên phòng phải là chuỗi ký tự.',
                'ten_phong.max' => 'Tên phòng không được vượt quá 255 ký tự.',
                'tang.integer' => 'Tầng phải là số nguyên.',
                'tang.min' => 'Tầng phải lớn hơn hoặc bằng 1.',
                'tang.max' => 'Tầng không được vượt quá 50.',
                'huong_cua_so.in' => 'Hướng cửa sổ không hợp lệ. Chỉ chấp nhận: Biển, Núi, Thành phố, Sân vườn.',
                'co_ban_cong.boolean' => 'Trường "Có ban công" phải là true hoặc false.',
                'co_view_dep.boolean' => 'Trường "Có view đẹp" phải là true hoặc false.',
                'gia_rieng.numeric' => 'Giá riêng phải là số.',
                'gia_rieng.min' => 'Giá riêng phải lớn hơn hoặc bằng 0.',
                'gia_rieng.max' => 'Giá riêng không được vượt quá 999,999,999 VNĐ.',
                'gia_bo_sung.numeric' => 'Giá bổ sung phải là số.',
                'gia_bo_sung.min' => 'Giá bổ sung phải lớn hơn hoặc bằng 0.',
                'gia_bo_sung.max' => 'Giá bổ sung không được vượt quá 999,999,999 VNĐ.',
                'trang_thai.required' => 'Vui lòng chọn trạng thái phòng.',
                'trang_thai.in' => 'Trạng thái không hợp lệ. Chỉ chấp nhận: Trống, Đang thuê, Đang dọn, Bảo trì.',
                'ghi_chu.string' => 'Ghi chú phải là chuỗi ký tự.',
                'ghi_chu.max' => 'Ghi chú không được vượt quá 1000 ký tự.',
            ]);
        }

        // Kiểm tra loại phòng có đang hoạt động không
        $loaiPhong = LoaiPhong::find($validated['loai_phong_id']);
        if (!$loaiPhong) {
            return back()->withErrors(['loai_phong_id' => 'Loại phòng không tồn tại.'])->withInput();
        }

        if ($loaiPhong->trang_thai !== 'hoat_dong') {
            return back()->withErrors(['loai_phong_id' => 'Loại phòng này không đang hoạt động. Vui lòng chọn loại phòng khác.'])->withInput();
        }

        // Xử lý checkbox boolean
        $validated['co_ban_cong'] = $request->has('co_ban_cong') ? true : false;
        $validated['co_view_dep'] = $request->has('co_view_dep') ? true : false;

        // Trim whitespace
        $validated['so_phong'] = trim($validated['so_phong']);
        if (isset($validated['ten_phong'])) {
            $validated['ten_phong'] = trim($validated['ten_phong']) ?: null;
        }
        if (isset($validated['ghi_chu'])) {
            $validated['ghi_chu'] = trim($validated['ghi_chu']) ?: null;
        }

        try {
            $phong->update($validated);

            // Cập nhật so_luong_phong nếu đổi loại phòng
            if ($oldLoaiPhongId != $validated['loai_phong_id']) {
                $oldLoaiPhong = LoaiPhong::find($oldLoaiPhongId);
                $newLoaiPhong = LoaiPhong::find($validated['loai_phong_id']);
                
                if ($oldLoaiPhong) {
                    if ($oldLoaiPhong->so_luong_phong > 0) {
                        $oldLoaiPhong->decrement('so_luong_phong');
                    }
                    if ($oldTrangThai === 'trong' && $oldLoaiPhong->so_luong_trong > 0) {
                        $oldLoaiPhong->decrement('so_luong_trong');
                    }
                }
                
                if ($newLoaiPhong) {
                    $newLoaiPhong->increment('so_luong_phong');
                    if ($validated['trang_thai'] === 'trong') {
                        $newLoaiPhong->increment('so_luong_trong');
                    }
                }
            } else {
                // Cập nhật so_luong_trong nếu trạng thái thay đổi
                if ($oldTrangThai === 'trong' && $validated['trang_thai'] !== 'trong') {
                    if ($loaiPhong->so_luong_trong > 0) {
                        $loaiPhong->decrement('so_luong_trong');
                    }
                } elseif ($oldTrangThai !== 'trong' && $validated['trang_thai'] === 'trong') {
                    $loaiPhong->increment('so_luong_trong');
                }
            }

            return redirect()->route('admin.phong.index')
                ->with('success', 'Cập nhật phòng "' . $phong->so_phong . '" thành công!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Có lỗi xảy ra khi cập nhật phòng: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $phong = Phong::findOrFail($id);
            $loaiPhongId = $phong->loai_phong_id;
            $trangThai = $phong->trang_thai;
            $soPhong = $phong->so_phong;

            // Kiểm tra xem phòng có đang được sử dụng không (legacy)
            $hasActiveBooking = $phong->datPhongs()
                ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan', 'da_thanh_toan'])
                ->exists();

            // Kiểm tra qua pivot table
            $hasActivePivotBooking = false;
            if (method_exists($phong, 'datPhongPhongs')) {
                $hasActivePivotBooking = $phong->datPhongPhongs()
                    ->whereHas('datPhong', function($q) {
                        $q->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan', 'da_thanh_toan']);
                    })
                    ->exists();
            }

            if ($hasActiveBooking || $hasActivePivotBooking) {
                return redirect()->route('admin.phong.index')
                    ->with('error', 'Không thể xóa phòng "' . $soPhong . '" vì đang có booking đang hoạt động!');
            }

            $phong->delete();

            // Cập nhật so_luong_phong và so_luong_trong của loại phòng
            $loaiPhong = LoaiPhong::find($loaiPhongId);
            if ($loaiPhong) {
                if ($loaiPhong->so_luong_phong > 0) {
                    $loaiPhong->decrement('so_luong_phong');
                }
                if ($trangThai === 'trong' && $loaiPhong->so_luong_trong > 0) {
                    $loaiPhong->decrement('so_luong_trong');
                }
            }

            return redirect()->route('admin.phong.index')
                ->with('success', 'Xóa phòng "' . $soPhong . '" thành công!');
        } catch (\Exception $e) {
            return redirect()->route('admin.phong.index')
                ->with('error', 'Có lỗi xảy ra khi xóa phòng: ' . $e->getMessage());
        }
    }

    /**
     * Update room status quickly
     */
    public function updateStatus(Request $request, $id)
    {
        $phong = Phong::findOrFail($id);
        $oldTrangThai = $phong->trang_thai;

        $request->validate([
            'trang_thai' => 'required|in:trong,dang_thue,dang_don,bao_tri',
        ]);

        $phong->update(['trang_thai' => $request->trang_thai]);

        // Cập nhật so_luong_trong của loại phòng
        $loaiPhong = LoaiPhong::find($phong->loai_phong_id);
        if ($loaiPhong) {
            if ($oldTrangThai === 'trong' && $request->trang_thai !== 'trong') {
                $loaiPhong->decrement('so_luong_trong');
            } elseif ($oldTrangThai !== 'trong' && $request->trang_thai === 'trong') {
                $loaiPhong->increment('so_luong_trong');
            }
        }

        return redirect()->back()
            ->with('success', 'Cập nhật trạng thái phòng thành công!');
    }
}
