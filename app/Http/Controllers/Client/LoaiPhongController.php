<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\LoaiPhong;
use Illuminate\Http\Request;

class LoaiPhongController extends Controller
{
    public function index()
    {
        // Lấy các loại phòng đang hoạt động
        // Cache loaiPhongs (30 phút) - rarely changes
        $loaiPhongs = \Illuminate\Support\Facades\Cache::remember('loai_phongs_all_active', 1800, function () {
            return LoaiPhong::where('trang_thai', 'hoat_dong')
                ->select('id', 'ten_loai', 'gia_co_ban', 'gia_khuyen_mai', 'trang_thai', 'so_luong_trong')
                ->get();
        });

        return view('client.content.content', compact('loaiPhongs'));
    }
}
