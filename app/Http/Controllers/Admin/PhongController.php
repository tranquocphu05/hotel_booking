<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Phong;
use App\Models\LoaiPhong;
use Illuminate\Http\Request;

class PhongController extends Controller
{
    // ====================== DANH SÁCH PHÒNG ======================
    public function index(Request $request)
    {
        $query = Phong::with('loaiPhong');

        if ($request->filled('loai_phong_id')) {
            $query->where('loai_phong_id', $request->loai_phong_id);
        }
        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }

        $phongs = $query->orderBy('id', 'desc')->paginate(5);
        $loaiPhongs = LoaiPhong::all();

        return view('admin.phong.index', compact('phongs', 'loaiPhongs'));
    }

    // ====================== FORM THÊM ======================
    public function create()
    {
        $loaiPhongs = LoaiPhong::all();
        return view('admin.phong.create', compact('loaiPhongs'));
    }

    // ====================== LƯU PHÒNG MỚI ======================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'loai_phong_id'   => 'required|exists:loai_phong,id',
            'ten_phong'       => [
                'required',
                'string',
                'max:255',
                'unique:phong,ten_phong',
                'regex:/^(?!\d+$)(?!\d)[\p{L}\p{N}\s]+$/u' // Không toàn số, không bắt đầu bằng số
            ],
            'gia_goc'         => 'required|numeric|min:100000|max:999999999',
            'gia_khuyen_mai'  => 'nullable|numeric|min:100000|max:999999999|lt:gia_goc',
            'co_khuyen_mai'   => 'nullable|boolean',
            'trang_thai'      => 'required|in:hien,an,bao_tri,chong',
            'img'             => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240', // 10MB
            'dich_vu'         => 'nullable|string|max:255'
        ], [
            'loai_phong_id.required' => 'Vui lòng chọn loại phòng cho phòng này.',
            'loai_phong_id.exists'   => 'Loại phòng được chọn không tồn tại.',

            'ten_phong.required' => 'Tên phòng không được để trống.',
            'ten_phong.string'   => 'Tên phòng phải là chuỗi ký tự.',
            'ten_phong.max'      => 'Tên phòng không được vượt quá 255 ký tự.',
            'ten_phong.unique'   => 'Tên phòng đã tồn tại. Vui lòng chọn tên khác.',
            'ten_phong.regex'    => 'Tên phòng không được chỉ chứa số hoặc bắt đầu bằng số.',

            'gia_goc.required' => 'Vui lòng nhập giá gốc cho phòng.',
            'gia_goc.numeric'  => 'Giá gốc phải là một số hợp lệ.',
            'gia_goc.min'      => 'Giá gốc phải lớn hơn hoặc bằng 100000.',
            'gia_goc.max'      => 'Giá gốc không được vượt quá 999,999,999.',

            'gia_khuyen_mai.numeric' => 'Giá khuyến mãi phải là số.',
            'gia_khuyen_mai.min'     => 'Giá khuyến mãi không được âm.',
            'gia_khuyen_mai.max'     => 'Giá khuyến mãi không được vượt quá 999,999,999.',
            'gia_khuyen_mai.lt'      => 'Giá khuyến mãi phải nhỏ hơn giá gốc.',

            'co_khuyen_mai.boolean'  => 'Giá trị "có khuyến mãi" phải là true/false hoặc 0/1.',
            'trang_thai.required' => 'Vui lòng chọn trạng thái phòng.',
            'trang_thai.in'       => 'Trạng thái phòng không hợp lệ.',

            'img.image' => 'Tệp tải lên phải là hình ảnh.',
            'img.mimes' => 'Ảnh phải có định dạng jpg, jpeg, png hoặc webp.',
            'img.max'   => 'Dung lượng ảnh không được vượt quá 10MB.',

            'dich_vu.string' => 'Dịch vụ phải là chuỗi ký tự.',
            'dich_vu.max'    => 'Dịch vụ không được vượt quá 255 ký tự.',
        ]);

        // ⚙️ Nếu chọn “Có khuyến mãi” mà không nhập giá khuyến mãi
        if ($request->input('co_khuyen_mai') == 1 && empty($request->input('gia_khuyen_mai'))) {
            return redirect()->back()
                ->withErrors(['gia_khuyen_mai' => 'Vui lòng nhập giá khuyến mãi khi có khuyến mãi.'])
                ->withInput();
        }

        try {
            $data = $validated;
            $data['co_khuyen_mai'] = $request->input('co_khuyen_mai', 0);

            // Upload ảnh
            if ($request->hasFile('img')) {
                $file = $request->file('img');
                $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                $file->move(public_path('uploads/phong'), $filename);
                $data['img'] = 'uploads/phong/' . $filename;
            }

            $data['gia'] = $data['gia_goc'];

            Phong::create($data);

            return redirect()->route('admin.phong.index')
                ->with('success', 'Thêm phòng thành công!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Lỗi khi thêm phòng: ' . $e->getMessage())
                ->withInput();
        }
    }

    // ====================== FORM CHỈNH SỬA ======================
    public function edit($id)
    {
        $phong = Phong::findOrFail($id);
        $loaiPhongs = LoaiPhong::all();
        return view('admin.phong.edit', compact('phong', 'loaiPhongs'));
    }

    // ====================== CẬP NHẬT PHÒNG ======================
    public function update(Request $request, $id)
    {
        $phong = Phong::findOrFail($id);

        $validated = $request->validate([
            'loai_phong_id'   => 'required|exists:loai_phong,id',
            'ten_phong'       => [
                'required',
                'string',
                'max:255',
                'unique:phong,ten_phong,' . $id,
                'regex:/^(?!\d+$)(?!\d)[\p{L}\p{N}\s]+$/u'
            ],
            'gia_goc'         => 'required|numeric|min:100000|max:999999999',
            'gia_khuyen_mai'  => 'nullable|numeric|min:100000|max:999999999|lt:gia_goc',
            'co_khuyen_mai'   => 'nullable|boolean',
            'trang_thai'      => 'required|in:hien,an,bao_tri,chong',
            'img'             => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'dich_vu'         => 'nullable|string|max:255'
        ], [
            'ten_phong.regex'    => 'Tên phòng không được chỉ chứa số hoặc bắt đầu bằng số.',
            'gia_khuyen_mai.lt'  => 'Giá khuyến mãi phải nhỏ hơn giá gốc.',
        ]);

        // ⚙️ Nếu chọn “Có khuyến mãi” mà không nhập giá khuyến mãi
        if ($request->input('co_khuyen_mai') == 1 && empty($request->input('gia_khuyen_mai'))) {
            return redirect()->back()
                ->withErrors(['gia_khuyen_mai' => 'Vui lòng nhập giá khuyến mãi khi có khuyến mãi.'])
                ->withInput();
        }

        try {
            $data = $validated;
            $data['gia'] = $data['gia_goc'];
            $data['co_khuyen_mai'] = $request->input('co_khuyen_mai', 0);

            if ($request->hasFile('img')) {
                if ($phong->img && file_exists(public_path($phong->img))) {
                    @unlink(public_path($phong->img));
                }
                $file = $request->file('img');
                $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                $file->move(public_path('uploads/phong'), $filename);
                $data['img'] = 'uploads/phong/' . $filename;
            }

            $phong->update($data);

            return redirect()->route('admin.phong.index')
                ->with('success', 'Cập nhật phòng thành công!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Lỗi cập nhật phòng: ' . $e->getMessage())
                ->withInput();
        }
    }

    // ====================== XÓA PHÒNG ======================
    public function destroy($id)
    {
        $phong = Phong::findOrFail($id);

        if ($phong->img && file_exists(public_path($phong->img))) {
            @unlink(public_path($phong->img));
        }

        $phong->delete();

        return redirect()->route('admin.phong.index')
            ->with('success', 'Xóa phòng thành công!');
    }

    // ====================== PHÒNG TRỐNG ======================
    public function available(Request $request)
    {
        $ngayNhan = $request->input('ngay_nhan', now()->format('Y-m-d'));
        $ngayTra = $request->input('ngay_tra', now()->addDay()->format('Y-m-d'));

        $allRooms = Phong::with('loaiPhong')->get();

        $bookedRoomIds = \App\Models\DatPhong::where(function ($query) use ($ngayNhan, $ngayTra) {
            $query->whereBetween('ngay_nhan', [$ngayNhan, $ngayTra])
                ->orWhereBetween('ngay_tra', [$ngayNhan, $ngayTra])
                ->orWhere(function ($subQ) use ($ngayNhan, $ngayTra) {
                    $subQ->where('ngay_nhan', '<=', $ngayNhan)
                         ->where('ngay_tra', '>=', $ngayTra);
                });
        })
            ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
            ->pluck('phong_id')
            ->toArray();

        $availableRooms = $allRooms->filter(function ($room) use ($bookedRoomIds) {
            return !in_array($room->id, $bookedRoomIds)
                && $room->trang_thai === 'hien'
                && $room->trang_thai !== 'chong';
        });

        $loaiPhongs = LoaiPhong::all();
        $bookedCount = count($bookedRoomIds);

        return view('admin.phong.available', compact('availableRooms', 'loaiPhongs', 'ngayNhan', 'ngayTra', 'bookedRoomIds', 'bookedCount'));
    }

    // ====================== CHI TIẾT PHÒNG ======================
    public function show($id)
    {
        $phong = Phong::with('loaiPhong')->findOrFail($id);
        return view('admin.phong.show', compact('phong'));
    }

    // ====================== CHỐNG PHÒNG ======================
    public function blockRoom($id)
    {
        $phong = Phong::findOrFail($id);

        $hasActiveBooking = \App\Models\DatPhong::where('phong_id', $id)
            ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
            ->exists();

        if ($hasActiveBooking) {
            return redirect()->route('admin.phong.index')
                ->with('error', 'Không thể chống phòng đang được đặt.');
        }

        $phong->update(['trang_thai' => 'chong']);

        return redirect()->route('admin.phong.index')
            ->with('success', 'Đã chống phòng thành công! Phòng không thể đặt cho đến khi hủy chống.');
    }
}
