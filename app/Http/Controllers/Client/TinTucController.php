<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TinTucController extends Controller
{
    public function index() 
    {
        // Cache với key dựa trên page number
        $page = request()->get('page', 1);
        $cacheKey = "news_list_page_{$page}";
        
        $posts = Cache::remember($cacheKey, 600, function () {
            return News::published()
                ->with('admin')
                ->orderBy('created_at', 'desc')
                ->paginate(9);
        });
            
        return view('client.content.tintuc', compact('posts')); 
    }

    public function chitiettintuc($slug)
    {
        // Cache bài viết (30 phút)
        $cacheKey = "news_slug_{$slug}";
        $post = Cache::remember($cacheKey, 1800, function () use ($slug) {
            return News::published()
                ->with('admin')
                ->bySlug($slug)
                ->first();
        });

        if (!$post) {
            abort(404, 'Bài viết không tồn tại.');
        }

        // Tăng lượt xem (không cache vì cần real-time)
        $post->incrementViews();

        // Cache bài viết liên quan (15 phút)
        $relatedCacheKey = "related_news_{$post->id}";
        $relatedPosts = Cache::remember($relatedCacheKey, 900, function () use ($post) {
            return News::published()
                ->where('id', '!=', $post->id)
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();
        });

        return view('client.content.chitiettintuc', compact('post', 'relatedPosts'));
    }
}
