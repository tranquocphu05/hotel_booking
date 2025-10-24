<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class GioiThieuController extends Controller
{
    public function index()
    {
        $comments = Comment::with('user')
            ->where('trang_thai', 'hien_thi')
            ->where('so_sao', '>=', 4)
            ->latest('ngay_danh_gia')
            ->take(10)
            ->get();
        return view('client.content.gioithieu', compact('comments'));
    }
}
