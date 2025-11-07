<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\LoaiPhong;
use App\Models\Comment;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        // Lấy các loại phòng đang hoạt động (cached 15 phút)
        $loaiPhongs = Cache::remember('dashboard_loai_phongs', 900, function () {
            return LoaiPhong::where('trang_thai', 'hoat_dong')
                ->where('so_luong_trong', '>', 0)
                ->orderBy('diem_danh_gia', 'desc')
                ->get();
        });

        // Lấy 5 đánh giá 5 sao được hiển thị (cached 10 phút)
        $comments = Cache::remember('dashboard_comments_5star', 600, function () {
            $comments = Comment::with('user')
                ->where('trang_thai', 'hien_thi')
                ->where('so_sao', 5)
                ->orderByDesc('ngay_danh_gia')
                ->take(5)
                ->get();

            // Nếu không có 5 sao, lấy 5 đánh giá bất kỳ
            if ($comments->isEmpty()) {
                $comments = Comment::with('user')
                    ->where('trang_thai', 'hien_thi')
                    ->orderByDesc('ngay_danh_gia')
                    ->take(5)
                    ->get();
            }

            return $comments;
        });

        // Trả về view và truyền các dữ liệu
        return view('client.dashboard', compact('loaiPhongs', 'comments'));
    }
}
