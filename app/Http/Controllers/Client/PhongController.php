<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Phong;
use App\Models\LoaiPhong;
use App\Models\Comment;
use Illuminate\Http\Request;

class PhongController extends Controller
{
    // Danh sách phòng
    public function index(Request $request)
    {
        $query = Phong::with('loaiPhong')
            ->where('trang_thai', 'hien');

        // Lọc theo loại phòng
        if ($request->has('loai_phong') && $request->loai_phong) {
            $query->where('loai_phong_id', $request->loai_phong);
        }

        // Lọc theo giá
        if ($request->has('gia_min') && $request->gia_min) {
            $query->where('gia', '>=', $request->gia_min);
        }
        if ($request->has('gia_max') && $request->gia_max) {
            $query->where('gia', '<=', $request->gia_max);
        }

        // Sắp xếp
        $sortBy = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $phongs = $query->paginate(12)->appends(request()->query());
        $allLoaiPhongs = LoaiPhong::where('trang_thai', 'hoat_dong')->get();

        return view('client.content.phong', compact('phongs', 'allLoaiPhongs'));
    }

    // Chi tiết phòng theo ID phòng
    public function show($id)
    {
        $phong = Phong::with('loaiPhong')->find($id);
        if (!$phong) {
            abort(404, 'Phòng không tồn tại');
        }

        $loaiPhong = $phong->loaiPhong; // dùng lại view hiện tại nhưng dữ liệu gốc là phòng

        // Lấy các loại phòng liên quan (cùng trạng thái hoạt động, trừ loại hiện tại)
        $relatedLoaiPhongs = LoaiPhong::with(['phongs' => function($query) {
                $query->where('trang_thai', 'hien');
            }])
            ->where('id', '!=', $loaiPhong->id)
            ->where('trang_thai', 'hoat_dong')
            ->limit(4)
            ->get();

        // Phòng dùng cho form đánh giá chính là phòng hiện tại
        $reviewRoom = $phong;

        // Lấy comments của phòng hiện tại
        $comments = Comment::with('user')
            ->where('phong_id', $phong->id)
            ->where('trang_thai', 'hien_thi')
            ->orderBy('ngay_danh_gia', 'desc')
            ->get();

        return view('client.content.show', compact('loaiPhong', 'relatedLoaiPhongs', 'comments', 'reviewRoom'));
    }

    // API endpoint để lấy danh sách phòng (cho AJAX)
    public function getRooms(Request $request)
    {
        $query = Phong::with('loaiPhong')
            ->where('trang_thai', 'hien');

        // Lọc theo loại phòng
        if ($request->has('loai_phong') && $request->loai_phong) {
            $query->where('loai_phong_id', $request->loai_phong);
        }

        // Lọc theo giá
        if ($request->has('gia_min') && $request->gia_min) {
            $query->where('gia', '>=', $request->gia_min);
        }
        if ($request->has('gia_max') && $request->gia_max) {
            $query->where('gia', '<=', $request->gia_max);
        }

        // Sắp xếp
        $sortBy = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $rooms = $query->get();

        return response()->json([
            'success' => true,
            'data' => $rooms,
            'total' => $rooms->count()
        ]);
    }
}
