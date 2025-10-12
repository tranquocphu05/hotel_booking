<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CommentController extends Controller
{

    // public function index(Request $request)
    // {
    //     $comments = Comment::with('user')
    //         ->orderBy('id', 'asc')
    //         ->paginate(7);
    //     return view('admin.reviews.index', compact('comments'));
    // }

    public function index(Request $request)
    {
        $keyword = $request->input('keyword');
        $rating = $request->input('rating');
        $isActive = $request->input('is_active');

        $comments = Comment::with('user:id,username,email')
            ->when($keyword, function ($query) use ($keyword) {
                $query->where('content', 'like', "%{$keyword}%")
                    ->orWhereHas('user', function ($q) use ($keyword) {
                        $q->where('username', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%");
                    });
            })
            ->when($rating, function ($query) use ($rating) {
                $query->where('rating', $rating);
            })
            ->when($isActive !== null && $isActive !== '', function ($query) use ($isActive) {
                $query->where('is_active', $isActive);
            })
            ->orderBy('id', 'asc')
            ->paginate(7)
            ->appends($request->all());

        return view('admin.reviews.index', compact('comments'));
    }


    public function show(Comment $comment, $id)
    {
        try {
            // $comment = Comment::with(['user','booking','room_booking'])->findOrFail($id);
            $comment = Comment::with(['user'])->findOrFail($id);
            $avatar = $comment->user?->avatar
                ? $comment->user->avatar
                : 'jpg';
            $data = [
                'id' => $comment->id,
                'avatar' => $avatar,
                'user_type' => $comment->user_id ? 'Tài khoản' : 'Khách đặt phòng',

                'booking' => [
                    'code' => $comment->booking->code ?? null,
                    'status' => $comment->booking->status->name ?? null,
                ],

                'room' => [
                    'name' => $comment->room_booking->name ?? '[Không có]',
                    'image' => $comment->room_booking->image ?? null,
                    'sku' => $comment->room_booking->room_sku ?? null,
                    'price' => $comment->room_booking->unit_price ?? null,
                ],

                'review' => [
                    'rating' => $comment->rating,
                    'content' => $comment->content,
                    'images' => $comment->images ?? [],
                    'is_updated' => $comment->is_updated,
                    'created_at' => $comment->created_at->format('d/m/Y H:i'),
                ],

                'moderation' => [
                    'is_active' => $comment->is_active,
                    'hidden_reason' => $comment->hidden_reason,
                    'reply' => $comment->reply,
                    'reply_at' => $comment->reply_at ? Carbon::parse($comment->reply_at)->format('d/m/Y H:i') : null,
                ]
            ];
            return response()->json([
                'message' => 'Success',
                'data' => $data
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed',
                'error' => $th->getMessage(),
            ], 404);
        }
    }


    public function reply(Request $request, $id)
    {
        $validated = $request->validate([
            'reply' => 'required|string|min:5|max:3000'
        ]);

        $review = Comment::findOrFail($id);

        // Cập nhật phản hồi (dù là lần đầu hay chỉnh sửa)
        $review->update([
            'reply' => $validated['reply'],
            'reply_at' => now(),
        ]);

        return response()->json([
            'message' => 'Phản hồi đã được thêm/cập nhật thành công.',
            'data' => [
                'reply' => $validated['reply'],
                'reply_at' => $review->reply_at->format('d/m/Y H:i'),
            ],
        ], 200);
    }

    // Xóa phản hồi
    public function deleteReply($id)
    {
        $review = Comment::findOrFail($id);

        if (empty($review->reply)) {
            return response()->json(['message' => 'Đánh giá này chưa có phản hồi.'], 400);
        }

        $review->update([
            'reply' => null,
            'reply_at' => null,
        ]);

        return response()->json(['message' => 'Phản hồi đã được xóa thành công.'], 200);
    }




    public function statusToggle(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|boolean',
                'reason' => 'nullable|string|max:1000',
            ], [
                'status.required' => 'Trạng thái không được bỏ trống.',
                'status.boolean' => 'Trạng thái không hợp lệ.',
                'reason.max' => 'Lý do không được vượt quá 1000 ký tự.',
            ]);

            $comment = Comment::find($id);

            if (!$comment) {
                return response()->json([
                    'message' => 'Không tìm thấy đánh giá.'
                ], 404);
            }

            $status = (bool) $request->status;

            // Nếu muốn ẩn, phải có lý do
            if (!$status && empty($request->reason)) {
                return response()->json([
                    'message' => 'Vui lòng nhập lý do khi ẩn đánh giá.'
                ], 422);
            }
            $comment->timestamps = false;
            $comment->is_active = $status;
            $comment->hidden_reason = $status ? null : $request->reason;
            $comment->save();

            return response()->json([
                'message' => 'Success',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failler',
            ], 500);
        }
    }
}
