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
        // Lấy các loại phòng đang hoạt động (cached 15 phút) - select only needed columns
        $loaiPhongs = Cache::remember('dashboard_loai_phongs', 900, function () {
            return LoaiPhong::where('trang_thai', 'hoat_dong')
                ->where('so_luong_trong', '>', 0)
                ->select('id', 'ten_loai', 'gia_co_ban', 'gia_khuyen_mai', 'diem_danh_gia', 'so_luong_trong', 'anh', 'trang_thai')
                ->orderBy('diem_danh_gia', 'desc')
                ->get();
        });

        // Lấy các phòng có khuyến mãi (deal hot) để hiển thị (cached 15 phút) - select only needed columns
        $phongsUuDai = Cache::remember('dashboard_phongs_deal_hot', 900, function () {
            return LoaiPhong::where('trang_thai', 'hoat_dong')
                ->where('so_luong_trong', '>', 0)
                ->whereNotNull('gia_khuyen_mai')
                ->whereColumn('gia_khuyen_mai', '<', 'gia_co_ban')
                ->select('id', 'ten_loai', 'gia_co_ban', 'gia_khuyen_mai', 'diem_danh_gia', 'so_luong_trong', 'anh', 'trang_thai')
                ->orderByRaw('((gia_co_ban - gia_khuyen_mai) / gia_co_ban * 100) DESC')
                ->orderBy('diem_danh_gia', 'desc')
                ->take(4)
                ->get();
        });

        // Nếu không có phòng nào có khuyến mãi, lấy các phòng có đánh giá cao - select only needed columns
        if ($phongsUuDai->isEmpty()) {
            $phongsUuDai = Cache::remember('dashboard_phongs_high_rating', 900, function () {
                return LoaiPhong::where('trang_thai', 'hoat_dong')
                    ->where('so_luong_trong', '>', 0)
                    ->where('diem_danh_gia', '>=', 4.5)
                    ->select('id', 'ten_loai', 'gia_co_ban', 'gia_khuyen_mai', 'diem_danh_gia', 'so_luong_trong', 'anh', 'trang_thai')
                    ->orderBy('diem_danh_gia', 'desc')
                    ->take(4)
                    ->get();
            });
        }

        // Lấy 5 đánh giá 5 sao được hiển thị (cached 10 phút) - select only needed columns
        $comments = Cache::remember('dashboard_comments_5star', 600, function () {
            $comments = Comment::with(['user' => function($q) {
                    $q->select('id', 'ho_ten', 'img');
                }])
                ->where('trang_thai', 'hien_thi')
                ->where('so_sao', 5)
                ->select('id', 'nguoi_dung_id', 'noi_dung', 'so_sao', 'ngay_danh_gia', 'img', 'trang_thai')
                ->orderByDesc('ngay_danh_gia')
                ->take(5)
                ->get();

            // Nếu không có 5 sao, lấy 5 đánh giá bất kỳ
            if ($comments->isEmpty()) {
                $comments = Comment::with(['user' => function($q) {
                        $q->select('id', 'ho_ten', 'img');
                    }])
                    ->where('trang_thai', 'hien_thi')
                    ->select('id', 'nguoi_dung_id', 'noi_dung', 'so_sao', 'ngay_danh_gia', 'img', 'trang_thai')
                    ->orderByDesc('ngay_danh_gia')
                    ->take(5)
                    ->get();
            }

            return $comments;
        });

        // Trả về view và truyền các dữ liệu
        return view('client.dashboard', compact('loaiPhongs', 'comments', 'phongsUuDai'));
    }
}
