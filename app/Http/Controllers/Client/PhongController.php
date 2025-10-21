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

        $rooms = $query->paginate(12)->appends(request()->query());
        $loaiPhongs = LoaiPhong::where('trang_thai', 'hoat_dong')->get();

        return view('client.content.phong', compact('rooms', 'loaiPhongs'));
    }

    // Chi tiết phòng
    public function show($id)
    {
        $room = Phong::with('loaiPhong')
            ->where('id', $id)
            ->where('trang_thai', 'hien')
            ->first();

        if (!$room) {
            abort(404, 'Phòng không tồn tại');
        }

        // Lấy các phòng liên quan (cùng loại)
        $relatedRooms = Phong::with('loaiPhong')
            ->where('loai_phong_id', $room->loai_phong_id)
            ->where('id', '!=', $room->id)
            ->where('trang_thai', 'hien')
            ->limit(4)
            ->get();

        // Lấy comments/reviews cho phòng này
        $comments = Comment::with('user')
            ->where('phong_id', $id)
            ->where('trang_thai', 'hien_thi')
            ->orderBy('ngay_danh_gia', 'desc')
            ->get();

        return view('client.content.show', compact('room', 'relatedRooms', 'comments'));
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
