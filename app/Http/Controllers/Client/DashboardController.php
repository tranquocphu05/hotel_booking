<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\LoaiPhong;

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


        // Truyền sang view
        return view('client.dashboard', compact('loaiPhongs'));
    }
}
