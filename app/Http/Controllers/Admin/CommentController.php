<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CommentController extends Controller
{
    protected function search($keyword = null, $rating = null, $isActive = null)
    {
        $query = Comment::query();

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('content', 'like', "%{$keyword}%")
                    ->orWhereHas('user', function ($userQuery) use ($keyword) {
                        $userQuery->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%");
                    });
            });
        }

        if ($rating) {
            $query->where('rating', $rating);
        }

        if (in_array($isActive, ['0', '1', 0, 1], true)) {
            $query->where('is_active', (bool) $isActive);
        }

        return $query
            ->with('user:id,name,email')
            ->orderByDesc('id')
            ->paginate(10);
    }



    public function index(Request $request)
    {
        try {
            $keyword = $request->input('keyword');
            $rating = $request->input('rating');
            $isActive = $request->input('is_active');

            $comments = $this->search($keyword, $rating, $isActive);
            $comments->transform(function ($comment) {
                return [
                    'id' => $comment->id,
                    'rating' => $comment->rating,
                    'content_preview' => Str::limit(strip_tags($comment->content), 80),
                    'is_active' => $comment->is_active,
                    'is_updated' => $comment->is_updated,
                    'has_reply' => !empty($comment->reply),
                    'created_at' => Carbon::parse($comment->created_at)->format('d/m/Y H:i'),
                ];
            });
            return response()->json([
                'message' => 'Success',
                'pagination' => [
                    'current_page' => $comments->currentPage(),
                    'last_page' => $comments->lastPage(),
                    'total' => $comments->total(),
                ],
                'data' => $comments->items()
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], 404);
        }
    }

    public function show(Comment $comment, $id)
    {
        try {
            $comment = Comment::with(['user', 'booking', 'room_booking'])->findOrFail($id);
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
        try {
            $validated = $request->validate([
                'reply' => 'required|string|min:5|max:3000'
            ], [
                'reply.required' => 'Nội dung phản hồi không được để trống.',
                'reply.string' => 'Nội dung phản hồi không hợp lệ.',
                'reply.min' => 'Phản hồi quá ngắn (tối thiểu 5 ký tự).',
                'reply.max' => 'Phản hồi không được vượt quá 3000 ký tự.'
            ]);

            $comment = Comment::find($id);

            // dd($comment);

            if (!$comment) {
                return response()->json([
                    'message' => 'Không tìm thấy đánh giá.'
                ], 404);
            }

            if (!empty($comment->reply)) {
                return response()->json([
                    'message' => 'Đánh giá này đã được phản hồi.'
                ], 400);
            }
            $comment->timestamps = false;
            $comment->reply = $validated['reply'];
            $comment->reply_at = now();
            $comment->save();

            return response()->json([
                'message' => 'Success',
                'data' => $comment
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed',
            ], 500);
        }
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
