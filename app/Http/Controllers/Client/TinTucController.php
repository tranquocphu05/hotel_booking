<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TinTucController extends Controller
{
    /**
     * Dữ liệu mô phỏng bài viết (Thực tế sẽ lấy từ Database)
     */
    private function getPostsData()
    {
        return [
            [
                'tag' => 'Chuyến đi Du lịch',
                'title' => 'Tremblant Ở Canada',
                'time' => '15 Tháng Tư, 2019',
                'img' => 'img/blog/blog-1.jpg',
                'slug' => 'tremblant-in-canada'
            ],
            [
                'tag' => 'Cắm trại',
                'title' => 'Chọn một nhà lưu động tĩnh',
                'time' => '15 Tháng Tư, 2019',
                'img' => 'img/blog/blog-2.jpg',
                'slug' => 'choosing-a-static-caravan'
            ],
            [
                'tag' => 'Sự kiện',
                'title' => 'Hẻm núi Đồng (Copper Canyon)',
                'time' => '21 Tháng Tư, 2019',
                'img' => 'img/blog/blog-3.jpg',
                'slug' => 'copper-canyon'
            ],
            [
                'tag' => 'Du lịch Khách sạn',
                'title' => 'Một tấm bưu thiếp du hành thời gian',
                'time' => '22 Tháng Tư, 2019',
                'img' => 'img/blog/blog-4.jpg',
                'slug' => 'a-time-travel-postcard-4'
            ],
            [
                'tag' => 'Cắm trại',
                'title' => 'Một tấm bưu thiếp du hành thời gian',
                'time' => '25 Tháng Tư, 2019',
                'img' => 'img/blog/blog-5.jpg',
                'slug' => 'a-time-travel-postcard-5'
            ],
            [
                'tag' => 'Chuyến đi Du lịch',
                'title' => 'Du lịch Virginia cho trẻ em',
                'time' => '28 Tháng Tư, 2019',
                'img' => 'img/blog/blog-6.jpg',
                'slug' => 'virginia-travel-for-kids'
            ],
            [
                'tag' => 'Chuyến đi Du lịch',
                'title' => 'Hẻm núi Bryce Thật Tuyệt vời',
                'time' => '29 Tháng Tư, 2019',
                'img' => 'img/blog/blog-7.jpg',
                'slug' => 'bryce-canyon-a-stunning'
            ],
            [
                'tag' => 'Sự kiện & Du lịch',
                'title' => 'Motorhome hay Trailer',
                'time' => '30 Tháng Tư, 2019',
                'img' => 'img/blog/blog-8.jpg',
                'slug' => 'motorhome-or-trailer'
            ],
            [
                'tag' => 'Cắm trại',
                'title' => 'Lạc Lối Ở Lagos Bồ Đào Nha',
                'time' => '30 Tháng Tư, 2019',
                'img' => 'img/blog/blog-9.jpg',
                'slug' => 'lost-in-lagos-portugal'
            ],
        ];
    }
    
    public function index() 
    {
        $posts = $this->getPostsData();
        return view('client.content.tintuc', compact('posts')); 
    }

    public function chitiettintuc($slug)
    {
        $allPosts = $this->getPostsData();
        $post = null;

        // TÌM KIẾM BÀI VIẾT DỰA TRÊN SLUG
        foreach ($allPosts as $p) {
            if ($p['slug'] === $slug) {
                // Thêm nội dung chi tiết vào bài viết tìm được
                $p['content'] = 'Đây là nội dung chi tiết của bài viết "' . $p['title'] . '". Bạn có thể thay thế bằng nội dung thực tế.';
                $post = $p;
                break;
            }
        }
        
        // Xử lý nếu không tìm thấy bài viết
        if (!$post) {
            // Tùy chọn: trả về 404 hoặc thông báo lỗi
            abort(404, 'Bài viết không tồn tại.');
        }

        return view('client.content.chitiettintuc', compact('post'));
    }
}
