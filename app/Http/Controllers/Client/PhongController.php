<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\LoaiPhong;
use App\Models\Comment;
use App\Models\Phong;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PhongController extends Controller
{
    // Danh sách loại phòng (thay vì phòng cụ thể)
    public function index(Request $request)
    {
        $query = LoaiPhong::where('trang_thai', 'hoat_dong');

        // Lọc theo loại phòng
        if ($request->has('loai_phong') && $request->loai_phong) {
            $query->where('id', $request->loai_phong);
        }

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
        
        // Cache allLoaiPhongs (15 phút)
        $allLoaiPhongs = Cache::remember('all_loai_phongs_active', 900, function () {
            return LoaiPhong::where('trang_thai', 'hoat_dong')->get();
        });

        // Tính availability theo khoảng thời gian (nếu có)
        // KHÔNG cache vì dữ liệu availability cần real-time chính xác
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
        // Cache loại phòng (30 phút)
        $loaiPhong = Cache::remember("loai_phong_{$id}", 1800, function () use ($id) {
            return LoaiPhong::find($id);
        });

        if (!$loaiPhong) {
            abort(404, 'Loại phòng không tồn tại');
        }

        // Cache related loại phòng (15 phút)
        $relatedLoaiPhongs = Cache::remember("related_loai_phongs_{$id}", 900, function () use ($loaiPhong) {
            return LoaiPhong::where('id', '!=', $loaiPhong->id)
                ->where('trang_thai', 'hoat_dong')
                ->limit(4)
                ->get();
        });

        // Cache comments (10 phút)
        $comments = Cache::remember("comments_loai_phong_{$id}", 600, function () use ($id) {
            return Comment::with('user')
                ->where('loai_phong_id', $id)
                ->where('trang_thai', 'hien_thi')
                ->orderBy('ngay_danh_gia', 'desc')
                ->get();
        });

        // Biến tương thích với view cũ
        $reviewRoom = $loaiPhong;

        // Tính availability theo khoảng thời gian (nếu có)
        // KHÔNG cache vì dữ liệu availability cần real-time chính xác
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

        // Lọc theo loại phòng
        if ($request->has('loai_phong') && $request->loai_phong) {
            $query->where('id', $request->loai_phong);
        }

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
