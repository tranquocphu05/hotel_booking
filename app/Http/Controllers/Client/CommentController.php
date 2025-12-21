<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\DatPhong;
use App\Models\LoaiPhong;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    /**
     * Hiển thị chi tiết loại phòng + danh sách đánh giá có lọc sao
     */
    public function showRoomComments(Request $request, $roomId)
    {
        $room = LoaiPhong::findOrFail($roomId);

        $filterStar = $request->query('star'); // Lọc ?star=1..5
        
        // Cache comments for 10 minutes - frequently accessed - select only needed columns
        $cacheKey = 'comments_' . md5($request->fullUrl());
        $comments = Cache::remember($cacheKey, 600, function () use ($roomId, $filterStar) {
            $query = Comment::where('loai_phong_id', $roomId)
                ->where('trang_thai', 'hien_thi')
                ->with(['user' => function($q) {
                    $q->select('id', 'ho_ten', 'img');
                }])
                ->select('id', 'nguoi_dung_id', 'loai_phong_id', 'noi_dung', 'so_sao', 'ngay_danh_gia', 'img', 'trang_thai');
            
            if ($filterStar && in_array($filterStar, [1,2,3,4,5])) {
                $query->where('so_sao', $filterStar);
            }
            
            return $query->latest('ngay_danh_gia')->paginate(10);
        });

        // Cache statistics (10 minutes)
        $statsCacheKey = "comment_stats_{$roomId}";
        $stats = Cache::remember($statsCacheKey, 600, function () use ($roomId) {
            return [
                'averageRating' => Comment::where('loai_phong_id', $roomId)
                    ->where('trang_thai', 'hien_thi')
                    ->avg('so_sao'),
                'totalReviews' => Comment::where('loai_phong_id', $roomId)
                    ->where('trang_thai', 'hien_thi')
                    ->count(),
                'countByStars' => Comment::selectRaw('so_sao, COUNT(*) as total')
                    ->where('loai_phong_id', $roomId)
                    ->where('trang_thai', 'hien_thi')
                    ->groupBy('so_sao')
                    ->pluck('total', 'so_sao'),
            ];
        });
        
        $averageRating = $stats['averageRating'];
        $totalReviews = $stats['totalReviews'];
        $countByStars = $stats['countByStars'];

        $existing = null;
        if (auth()->check()) {
            $existing = Comment::where('loai_phong_id', $roomId)
                ->where('nguoi_dung_id', auth()->id())
                ->first();
        }

        return view('client.content.comment', compact(
            'room', 'comments', 'averageRating', 'totalReviews', 'countByStars', 'filterStar', 'existing'
        ));
    }

    /**
     * Gửi hoặc cập nhật đánh giá
     */
    public function store(Request $request)
    {
        $request->validate([
            'so_sao' => 'required|integer|min:1|max:5',
            'noi_dung' => 'required|string|max:2000',
            'loai_phong_id' => 'required|integer|exists:loai_phong,id',
            'img' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để gửi đánh giá.');
        }

        $userId = Auth::id();

        // ✅ Kiểm tra xem user đã đặt phòng thành công chưa
        $hasBooking = DatPhong::where('nguoi_dung_id', $userId)
            ->where('loai_phong_id', $request->loai_phong_id)
            ->whereIn('trang_thai', ['da_xac_nhan', 'da_tra'])
            ->exists();

        if (!$hasBooking) {
            return redirect()->back()->with('error', 'Bạn chỉ có thể đánh giá sau khi đã đặt phòng thành công.');
        }

        $existing = Comment::where('loai_phong_id', $request->loai_phong_id)
            ->where('nguoi_dung_id', $userId)
            ->first();

        $imgPath = $existing->img ?? null;

        if ($request->hasFile('img')) {
            $imgPath = $request->file('img')->store('uploads/comments/images', 'public');
            if ($existing && $existing->img) {
                Storage::disk('public')->delete($existing->img);
            }
        }

        if ($existing) {
            $existing->update([
                'so_sao' => $request->so_sao,
                'noi_dung' => $request->noi_dung,
                'ngay_danh_gia' => now(),
                'img' => $imgPath,
            ]);
            $this->clearCommentCache($request->loai_phong_id);
            return redirect()->back()->with('success', 'Đánh giá của bạn đã được cập nhật thành công!');
        }

        Comment::create([
            'nguoi_dung_id' => $userId,
            'loai_phong_id' => $request->loai_phong_id,
            'noi_dung' => $request->noi_dung,
            'so_sao' => $request->so_sao,
            'img' => $imgPath,
            'ngay_danh_gia' => now(),
            'trang_thai' => 'hien_thi',
        ]);

        $this->clearCommentCache($request->loai_phong_id);
        return redirect()->back()->with('success', 'Cảm ơn bạn! Đánh giá đã được gửi thành công.');
    }

    /**
     * Cập nhật đánh giá inline
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'so_sao' => 'required|integer|min:1|max:5',
            'noi_dung' => 'required|string|max:2000',
            'img' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $comment = Comment::where('id', $id)
            ->where('nguoi_dung_id', Auth::id())
            ->firstOrFail();

        // ✅ Kiểm tra xem user đã đặt phòng thành công chưa
        $hasBooking = DatPhong::where('nguoi_dung_id', Auth::id())
            ->where('loai_phong_id', $comment->loai_phong_id)
            ->whereIn('trang_thai', ['da_xac_nhan', 'da_tra'])
            ->exists();

        if (!$hasBooking) {
            return redirect()->back()->with('error', 'Bạn chỉ có thể cập nhật đánh giá sau khi đã đặt phòng thành công.');
        }

        $imgPath = $comment->img;

        if ($request->hasFile('img')) {
            if ($comment->img && Storage::disk('public')->exists($comment->img)) {
                Storage::disk('public')->delete($comment->img);
            }
            $imgPath = $request->file('img')->store('uploads/comments/images', 'public');
        }

        $comment->update([
            'so_sao' => $request->so_sao,
            'noi_dung' => $request->noi_dung,
            'img' => $imgPath,
            'ngay_danh_gia' => now(),
        ]);

        $this->clearCommentCache($comment->loai_phong_id);
        return redirect()->back()->with('success', 'Đánh giá của bạn đã được cập nhật thành công!');
    }

    /**
     * Xóa đánh giá
     */
    public function destroy($id)
    {
        $comment = Comment::where('id', $id)
            ->where('nguoi_dung_id', Auth::id())
            ->firstOrFail();

        if ($comment->img) {
            Storage::disk('public')->delete($comment->img);
        }

        $loaiPhongId = $comment->loai_phong_id;
        $comment->delete();
        $this->clearCommentCache($loaiPhongId);

        return redirect()->back()->with('success', 'Đánh giá của bạn đã được xóa.');
    }


    private function clearCommentCache($loaiPhongId)
    {
        Cache::forget("comments_loai_phong_{$loaiPhongId}");
        Cache::forget('dashboard_comments_5star');
    }
}
