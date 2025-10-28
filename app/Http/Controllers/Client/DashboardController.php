<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\LoaiPhong;
use App\Models\Comment;
use App\Models\Phong;

class DashboardController extends Controller
{
    public function index()
    {
        // Lấy các loại phòng đang hoạt động
        $loaiPhongs = LoaiPhong::where('trang_thai', 'hoat_dong')
            ->with(['phongs' => function($query) {
                $query->where('trang_thai', 'hien');
            }])
            ->orderBy('diem_danh_gia', 'desc')
            ->get();

        // Lấy 4 phòng hiển thị
        $rooms = Phong::where('trang_thai', 'hien')
            ->orderByDesc('id')
            ->take(4)
            ->get();

        // Lấy 5 đánh giá 5 sao được hiển thị
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

        // Trả về view và truyền các dữ liệu
        return view('client.dashboard', compact('loaiPhongs', 'rooms', 'comments'));
    }
}
