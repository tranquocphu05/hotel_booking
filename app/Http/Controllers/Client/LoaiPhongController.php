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
        $loaiPhongs = LoaiPhong::where('trang_thai', 'hoat_dong')->get();

        return view('client.content.content', compact('loaiPhongs'));
    }
}
