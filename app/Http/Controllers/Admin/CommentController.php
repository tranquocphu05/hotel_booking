<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Traits\HasRolePermissions;
use App\Http\Controllers\Controller;

class CommentController extends Controller
{
    use HasRolePermissions;

    public function index(Request $request)
    {
        // Nhân viên và Lễ tân: xem đánh giá
        $this->authorizePermission('review.view');

        $comments = Comment::with(['user', 'loaiPhong'])
            ->when($request->keyword, fn($q) => $q->where('noi_dung', 'like', "%{$request->keyword}%"))
            ->when($request->rating, fn($q) => $q->where('so_sao', $request->rating))
            ->when($request->status, fn($q) => $q->where('trang_thai', $request->status))
            ->orderByDesc('ngay_danh_gia')
            ->paginate(5);

        return view('admin.reviews.index', compact('comments'));
    }

    public function show($id)
    {
        // Nhân viên và Lễ tân: xem đánh giá
        $this->authorizePermission('review.view');
        
        $comment = Comment::with('user')->findOrFail($id);
        return view('admin.reviews.show', compact('comment'));
    }

    public function statusToggle($id)
    {
        // Nhân viên: cập nhật trạng thái đánh giá
        // Lễ tân: không được cập nhật
        $this->authorizePermission('review.toggle');
        
        $comment = Comment::findOrFail($id);
        $comment->trang_thai = $comment->trang_thai === 'hien_thi' ? 'an' : 'hien_thi';
        $comment->save();

        return redirect()->back()->with('success', 'Đã cập nhật trạng thái đánh giá.');
    }

    public function reply(Request $request, $id)
    {
        // Nhân viên: trả lời đánh giá
        // Lễ tân: không được trả lời
        $this->authorizePermission('review.reply');
        
        $request->validate(['reply' => 'required|string|max:1000']);
        $comment = Comment::findOrFail($id);
        $comment->reply = $request->reply;
        $comment->reply_at = Carbon::now();
        $comment->save();

        return redirect()->back()->with('success', 'Đã phản hồi đánh giá.');
    }

    public function deleteReply($id)
    {
        // Nhân viên: xóa phản hồi đánh giá
        // Lễ tân: không được xóa
        $this->authorizePermission('review.reply');
        
        $comment = Comment::findOrFail($id);
        $comment->reply = null;
        $comment->reply_at = null;
        $comment->save();

        return redirect()->back()->with('success', 'Phản hồi đã được xóa.');
    }
}
