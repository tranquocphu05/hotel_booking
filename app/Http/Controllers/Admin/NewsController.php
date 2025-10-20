<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $news = News::with('admin')->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.news.index', compact('news'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.news.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tieu_de' => 'required|string|max:255',
            'tom_tat' => 'required|string|max:500',
            'noi_dung' => 'required|string',
            'hinh_anh' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'trang_thai' => 'required|in:draft,published,archived'
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

        return redirect()->route('admin.news.index')
            ->with('success', 'Tin tức đã được tạo thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $news = News::with('admin')->findOrFail($id);
        return view('admin.news.show', compact('news'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $news = News::findOrFail($id);
        return view('admin.news.edit', compact('news'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $news = News::findOrFail($id);

        $request->validate([
            'tieu_de' => 'required|string|max:255',
            'tom_tat' => 'required|string|max:500',
            'noi_dung' => 'required|string',
            'hinh_anh' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'trang_thai' => 'required|in:draft,published,archived'
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

        $news->update($data);

        return redirect()->route('admin.news.index')
            ->with('success', 'Tin tức đã được cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $news = News::findOrFail($id);

        // Xóa hình ảnh nếu có
        if ($news->hinh_anh && Storage::exists('public/' . $news->hinh_anh)) {
            Storage::delete('public/' . $news->hinh_anh);
        }

        $news->delete();

        return redirect()->route('admin.news.index')
            ->with('success', 'Tin tức đã được xóa thành công!');
    }
}
