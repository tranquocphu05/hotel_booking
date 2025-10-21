<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $comments = Comment::with('user')
            ->when($request->keyword, fn($q) => $q->where('noi_dung', 'like', "%{$request->keyword}%"))
            ->when($request->rating, fn($q) => $q->where('so_sao', $request->rating))
            ->when($request->status, fn($q) => $q->where('trang_thai', $request->status))
            ->orderByDesc('ngay_danh_gia')
            ->paginate(10);

        return view('admin.reviews.index', compact('comments'));
    }

    public function show($id)
    {
        $comment = Comment::with('user')->findOrFail($id);
        return view('admin.reviews.show', compact('comment'));
    }

    public function statusToggle($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->trang_thai = $comment->trang_thai === 'hien_thi' ? 'an' : 'hien_thi';
        $comment->save();

        return redirect()->back()->with('success', 'Đã cập nhật trạng thái đánh giá.');
    }

    public function reply(Request $request, $id)
    {
        $request->validate(['reply' => 'required|string|max:1000']);
        $comment = Comment::findOrFail($id);
        $comment->reply = $request->reply;
        $comment->reply_at = Carbon::now();
        $comment->save();

        return redirect()->back()->with('success', 'Đã phản hồi đánh giá.');
    }

    public function deleteReply($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->reply = null;
        $comment->reply_at = null;
        $comment->save();

        return redirect()->back()->with('success', 'Phản hồi đã được xóa.');
    }
}
