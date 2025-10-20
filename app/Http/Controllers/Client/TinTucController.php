<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\Admin;
use Illuminate\Http\Request;

class TinTucController extends Controller
{
    public function index() 
    {
        $posts = News::published()
            ->with('admin')
            ->orderBy('created_at', 'desc')
            ->paginate(9);
            
        return view('client.content.tintuc', compact('posts')); 
    }

    public function chitiettintuc($slug)
    {
        $post = News::published()
            ->with('admin')
            ->bySlug($slug)
            ->first();

        if (!$post) {
            abort(404, 'Bài viết không tồn tại.');
        }

        // Tăng lượt xem
        $post->incrementViews();

        // Lấy các bài viết liên quan
        $relatedPosts = News::published()
            ->where('id', '!=', $post->id)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        return view('client.content.chitiettintuc', compact('post', 'relatedPosts'));
    }
}
