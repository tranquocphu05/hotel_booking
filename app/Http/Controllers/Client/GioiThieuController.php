<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class GioiThieuController extends Controller
{
    public function index()
    {
        // Cache comments (15 phút) - frequently accessed - select only needed columns
        $comments = \Illuminate\Support\Facades\Cache::remember('gioi_thieu_comments', 900, function () {
            return Comment::with(['user' => function($q) {
                    $q->select('id', 'ho_ten', 'img');
                }])
                ->where('trang_thai', 'hien_thi')
                ->where('so_sao', '>=', 4)
                ->select('id', 'nguoi_dung_id', 'noi_dung', 'so_sao', 'ngay_danh_gia', 'img', 'trang_thai')
                ->latest('ngay_danh_gia')
                ->take(10)
                ->get();
        });
        return view('client.content.gioithieu', compact('comments'));
    }
}
