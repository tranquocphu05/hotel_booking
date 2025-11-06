<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\LoaiPhong;
use App\Models\Comment;
use App\Models\Phong;
use Illuminate\Http\Request;

class PhongController extends Controller
{
    // Danh sách loại phòng (thay vì phòng cụ thể)
    public function index(Request $request)
    {
        $query = LoaiPhong::where('trang_thai', 'hoat_dong');

        // Lọc theo giá - xét cả giá khuyến mãi nếu có
        if ($request->has('gia_min') && $request->gia_min) {
            $query->where(function($q) use ($request) {
                $q->where('gia_co_ban', '>=', $request->gia_min)
                  ->orWhere(function($subQ) use ($request) {
                      $subQ->whereNotNull('gia_khuyen_mai')
                           ->where('gia_khuyen_mai', '>=', $request->gia_min);
                  });
            });
        }
        if ($request->has('gia_max') && $request->gia_max) {
            $query->where(function($q) use ($request) {
                $q->where('gia_co_ban', '<=', $request->gia_max)
                  ->orWhere(function($subQ) use ($request) {
                      $subQ->whereNotNull('gia_khuyen_mai')
                           ->where('gia_khuyen_mai', '<=', $request->gia_max);
                  });
            });
        }

        // Sắp xếp
        $sortBy = $request->get('sort', 'diem_danh_gia');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination - Đổi tên biến để giữ tương thích với view
        $phongs = $query->paginate(12)->appends(request()->query());
        $allLoaiPhongs = LoaiPhong::where('trang_thai', 'hoat_dong')->get();

        // Tính availability theo khoảng thời gian (nếu có)
        $checkin = $request->get('checkin');
        $checkout = $request->get('checkout');
        $availabilityMap = [];
        
        if ($checkin && $checkout) {
            foreach ($phongs as $phong) {
                try {
                    $availableCount = Phong::countAvailableRooms($phong->id, $checkin, $checkout);
                    $availabilityMap[$phong->id] = $availableCount;
                } catch (\Exception $e) {
                    $availabilityMap[$phong->id] = null;
                }
            }
        }

        return view('client.content.phong', compact('phongs', 'allLoaiPhongs', 'checkin', 'checkout', 'availabilityMap'));
    }

    // Chi tiết loại phòng theo ID
    public function show($id, Request $request)
    {
        // Tìm loại phòng
        $loaiPhong = LoaiPhong::find($id);
        if (!$loaiPhong) {
            abort(404, 'Loại phòng không tồn tại');
        }

        // Lấy các loại phòng liên quan (cùng trạng thái hoạt động, trừ loại hiện tại)
        $relatedLoaiPhongs = LoaiPhong::where('id', '!=', $loaiPhong->id)
            ->where('trang_thai', 'hoat_dong')
            ->limit(4)
            ->get();

        // Lấy comments của loại phòng này (từ các booking trước)
        $comments = Comment::with('user')
            ->where('loai_phong_id', $loaiPhong->id) // Giả sử Comment có loai_phong_id
            ->where('trang_thai', 'hien_thi')
            ->orderBy('ngay_danh_gia', 'desc')
            ->get();

        // Biến tương thích với view cũ
        $reviewRoom = $loaiPhong; // Dùng loại phòng thay vì phòng cụ thể

        // Tính availability theo khoảng thời gian (nếu có)
        $checkin = $request->get('checkin');
        $checkout = $request->get('checkout');
        $availableCount = null;
        
        if ($checkin && $checkout) {
            try {
                $availableCount = Phong::countAvailableRooms($loaiPhong->id, $checkin, $checkout);
            } catch (\Exception $e) {
                $availableCount = null;
            }
        }

        return view('client.content.show', compact('loaiPhong', 'relatedLoaiPhongs', 'comments', 'reviewRoom', 'checkin', 'checkout', 'availableCount'));
    }

    // API endpoint để lấy danh sách loại phòng (cho AJAX)
    public function getRooms(Request $request)
    {
        $query = LoaiPhong::where('trang_thai', 'hoat_dong');

        // Lọc theo giá - xét cả giá khuyến mãi nếu có
        if ($request->has('gia_min') && $request->gia_min) {
            $query->where(function($q) use ($request) {
                $q->where('gia_co_ban', '>=', $request->gia_min)
                  ->orWhere(function($subQ) use ($request) {
                      $subQ->whereNotNull('gia_khuyen_mai')
                           ->where('gia_khuyen_mai', '>=', $request->gia_min);
                  });
            });
        }
        if ($request->has('gia_max') && $request->gia_max) {
            $query->where(function($q) use ($request) {
                $q->where('gia_co_ban', '<=', $request->gia_max)
                  ->orWhere(function($subQ) use ($request) {
                      $subQ->whereNotNull('gia_khuyen_mai')
                           ->where('gia_khuyen_mai', '<=', $request->gia_max);
                  });
            });
        }

        // Sắp xếp
        $sortBy = $request->get('sort', 'diem_danh_gia');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $rooms = $query->get();

        return response()->json([
            'success' => true,
            'data' => $rooms,
            'total' => $rooms->count()
        ]);
    }
}
