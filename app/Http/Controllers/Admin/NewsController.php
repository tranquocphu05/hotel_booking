<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Traits\HasRolePermissions;

class NewsController extends Controller
{
    use HasRolePermissions;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Chỉ admin mới được quản lý tin tức
        if (!$this->hasRole('admin')) {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }
        $news = News::with('admin')->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.news.index', compact('news'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Chỉ admin mới được quản lý tin tức
        if (!$this->hasRole('admin')) {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }
        return view('admin.news.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Chỉ admin mới được quản lý tin tức
        if (!$this->hasRole('admin')) {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }
        $request->validate([
            'tieu_de' => 'required|string|max:255|regex:/^(?=.*\pL)[\pL\pN\s\.\,\!\?\-\_]+$/u',
            'tom_tat' => 'required|string|max:500|regex:/^[\pL\pN\s\.\,\!\?\-\_]+$/u',
            'noi_dung' => 'required|string|regex:/^[\pL\pN\s\.\,\!\?\-\_]+$/u',
            'hinh_anh' => 'required|image|max:2048',
            'trang_thai' => 'required|in:draft,published,archived'
        ], [
            'tieu_de.required' => 'Tiêu đề không được để trống.',
            'tieu_de.regex' => 'Tiêu đề chỉ được chứa chữ cái, số và các ký tự đặc biệt cơ bản.',
            'tom_tat.required' => 'Tóm tắt không được để trống.',
            'tom_tat.regex' => 'Tóm tắt chỉ được chứa chữ cái, số và các ký tự đặc biệt cơ bản.',
            'noi_dung.required' => 'Nội dung không được để trống.',
            'noi_dung.regex' => 'Nội dung chỉ được chứa chữ cái, số và các ký tự đặc biệt cơ bản.',
            'hinh_anh.required' => 'Hình ảnh là bắt buộc.',
            'hinh_anh.image' => 'Tệp tải lên phải là một hình ảnh.',
            'hinh_anh.max' => 'Kích thước hình ảnh không được vượt quá 2MB.',
            'trang_thai.required' => 'Trạng thái là bắt buộc.',
            'trang_thai.in' => 'Trạng thái không hợp lệ.'
        ]);

        $data = $request->all();
        $data['nguoi_dung_id'] = Auth::id();
        $data['slug'] = Str::slug($request->tieu_de);

        // Xử lý upload hình ảnh
        if ($request->hasFile('hinh_anh')) {
            $image = $request->file('hinh_anh');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads'), $imageName);
            $data['hinh_anh'] = 'uploads/' . $imageName;
        }

        News::create($data);

        // Clear news cache
        $this->clearNewsCache();

        return redirect()->route('admin.news.index')
            ->with('success', 'Tin tức đã được tạo thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Chỉ admin mới được quản lý tin tức
        if (!$this->hasRole('admin')) {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }
        $news = News::with('admin')->findOrFail($id);
        return view('admin.news.show', compact('news'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Chỉ admin mới được quản lý tin tức
        if (!$this->hasRole('admin')) {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }
        $news = News::findOrFail($id);
        return view('admin.news.edit', compact('news'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Chỉ admin mới được quản lý tin tức
        if (!$this->hasRole('admin')) {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }
        $news = News::findOrFail($id);

        $request->validate([
            'tieu_de' => 'required|string|max:255|regex:/^(?=.*\pL)[\pL\pN\s\.\,\!\?\-\_]+$/u',
            'tom_tat' => 'required|string|max:500|regex:/^[\pL\pN\s\.\,\!\?\-\_]+$/u',
            'noi_dung' => 'required|string|regex:/^[\pL\pN\s\.\,\!\?\-\_]+$/u',
            'hinh_anh' => 'required|image|max:2048',
            'trang_thai' => 'required|in:draft,published,archived'
        ], [
            'tieu_de.required' => 'Tiêu đề không được để trống.',
            'tieu_de.regex' => 'Tiêu đề chỉ được chứa chữ cái, số và các ký tự đặc biệt cơ bản.',
            'tom_tat.required' => 'Tóm tắt không được để trống.',
            'tom_tat.regex' => 'Tóm tắt chỉ được chứa chữ cái, số và các ký tự đặc biệt cơ bản.',
            'noi_dung.required' => 'Nội dung không được để trống.',
            'noi_dung.regex' => 'Nội dung chỉ được chứa chữ cái, số và các ký tự đặc biệt cơ bản.',
            'hinh_anh.required' => 'Hình ảnh là bắt buộc.',
            'hinh_anh.image' => 'Tệp tải lên phải là một hình ảnh.',
            'hinh_anh.max' => 'Kích thước hình ảnh không được vượt quá 2MB.',
            'trang_thai.required' => 'Trạng thái là bắt buộc.',
            'trang_thai.in' => 'Trạng thái không hợp lệ.'
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->tieu_de);

        // Xử lý upload hình ảnh mới
        if ($request->hasFile('hinh_anh')) {
            // Xóa hình ảnh cũ nếu có
            if ($news->hinh_anh && file_exists(public_path($news->hinh_anh))) {
                unlink(public_path($news->hinh_anh));
            }

            $image = $request->file('hinh_anh');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads'), $imageName);
            $data['hinh_anh'] = 'uploads/' . $imageName;
        }

        $oldSlug = $news->slug;
        $news->update($data);

        // Clear news cache
        $this->clearNewsCache();
        Cache::forget("news_slug_{$oldSlug}");
        if ($news->slug !== $oldSlug) {
            Cache::forget("news_slug_{$news->slug}");
        }
        Cache::forget("related_news_{$news->id}");

        return redirect()->route('admin.news.index')
            ->with('success', 'Tin tức đã được cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Chỉ admin mới được quản lý tin tức
        if (!$this->hasRole('admin')) {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }
        $news = News::findOrFail($id);

        // Xóa hình ảnh nếu có
        if ($news->hinh_anh && Storage::exists('public/' . $news->hinh_anh)) {
            Storage::delete('public/' . $news->hinh_anh);
        }

        $slug = $news->slug;
        $newsId = $news->id;
        $news->delete();

        // Clear news cache
        $this->clearNewsCache();
        Cache::forget("news_slug_{$slug}");
        Cache::forget("related_news_{$newsId}");

        return redirect()->route('admin.news.index')
            ->with('success', 'Tin tức đã được xóa thành công!');
    }

    /**
     * Clear all News related cache
     */
    private function clearNewsCache()
    {
        // Clear paginated news list (clear all pages)
        // Note: In production, consider using cache tags with Redis
        for ($i = 1; $i <= 10; $i++) {
            Cache::forget("news_list_page_{$i}");
        }
    }
}
