<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CommentController extends Controller
{
    // Hiển thị danh sách đánh giá (nếu có trang riêng)
    public function index()
    {
        $comments = Comment::where('trang_thai', 'hien_thi')
            ->latest('ngay_danh_gia')
            ->paginate(10);

        return view('client.content.comment', compact('comments'));
    }

    // Gửi hoặc cập nhật đánh giá
    public function store(Request $request)
    {
        $request->validate([
            'so_sao' => 'required|integer|min:1|max:5',
            'noi_dung' => 'required|string|max:2000',
            'phong_id' => 'required|integer|exists:phong,id',
            'img' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ], [
            'img.image' => 'Tệp tải lên phải là hình ảnh.',
            'img.mimes' => 'Chỉ chấp nhận các định dạng: jpg, jpeg, png, webp.',
            'img.max' => 'Ảnh không được vượt quá 4MB.',
        ]);

        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để gửi đánh giá.');
        }

        $userId = Auth::id();
        $existing = Comment::where('phong_id', $request->phong_id)
            ->where('nguoi_dung_id', $userId)
            ->first();

        $imgPath = $existing->img ?? null;

        if ($request->hasFile('img')) {
            $imgPath = $request->file('img')->store('uploads/comments/images', 'public');

            // Nếu có ảnh cũ thì xóa
            if ($existing && $existing->img) {
                Storage::disk('public')->delete($existing->img);
            }
        }

        if ($existing) {
            // Cập nhật đánh giá cũ
            $existing->update([
                'so_sao' => $request->so_sao,
                'noi_dung' => $request->noi_dung,
                'ngay_danh_gia' => now(),
                'img' => $imgPath,
            ]);
            return redirect()->back()->with('success', 'Đánh giá của bạn đã được cập nhật thành công!');
        }

        // Tạo mới
        Comment::create([
            'nguoi_dung_id' => $userId,
            'phong_id' => $request->phong_id,
            'noi_dung' => $request->noi_dung,
            'so_sao' => $request->so_sao,
            'img' => $imgPath,
            'ngay_danh_gia' => now(),
            'trang_thai' => 'hien_thi',
        ]);

        return redirect()->back()->with('success', 'Cảm ơn bạn! Đánh giá đã được gửi thành công.');
    }

    // Xóa đánh giá
    public function destroy($id)
    {
        $comment = Comment::where('id', $id)
            ->where('nguoi_dung_id', Auth::id())
            ->firstOrFail();

        if ($comment->img) {
            Storage::disk('public')->delete($comment->img);
        }

        $comment->delete();

        return redirect()->back()->with('success', 'Đánh giá của bạn đã được xóa.');
    }
    // Cập nhật đánh giá (cho phần chỉnh sửa inline)
    public function update(Request $request, $id)
    {
        $request->validate([
            'so_sao' => 'required|integer|min:1|max:5',
            'noi_dung' => 'required|string|max:2000',
            'img' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ], [
            'img.image' => 'Tệp tải lên phải là hình ảnh.',
            'img.mimes' => 'Chỉ chấp nhận: jpg, jpeg, png, webp.',
            'img.max' => 'Ảnh không được vượt quá 4MB.',
        ]);

        $comment = Comment::where('id', $id)
            ->where('nguoi_dung_id', Auth::id())
            ->firstOrFail();

        $imgPath = $comment->img; // Giữ ảnh cũ nếu không upload mới

        if ($request->hasFile('img')) {
            // Xóa ảnh cũ nếu có
            if ($comment->img && Storage::disk('public')->exists($comment->img)) {
                Storage::disk('public')->delete($comment->img);
            }

            // Upload ảnh mới
            $imgPath = $request->file('img')->store('uploads/comments/images', 'public');
        }

        // Cập nhật dữ liệu
        $comment->update([
            'so_sao' => $request->so_sao,
            'noi_dung' => $request->noi_dung,
            'img' => $imgPath,
            'ngay_danh_gia' => now(),
        ]);

        return redirect()->back()->with('success', 'Đánh giá của bạn đã được cập nhật thành công!');
    }

}
