<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoaiPhong;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Traits\HasRolePermissions;

class LoaiPhongController extends Controller
{
    use HasRolePermissions;

    // Hiển thị danh sách loại phòng
    public function index(Request $request)
    {
        // Nhân viên và Lễ tân: chỉ xem
        $this->authorizePermission('loai_phong.view');
        $query = LoaiPhong::query();

        // Filter theo trạng thái nếu có
        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }

        $loaiPhongs = $query->orderBy('id', 'desc')->paginate(5);
        return view('admin.loai_phong.index', compact('loaiPhongs'));
    }

    // Form thêm loại phòng
    public function create()
    {
        // Chỉ admin mới được tạo mới
        $this->authorizePermission('loai_phong.create');
        return view('admin.loai_phong.create');
    }

    // Lưu loại phòng mới
    public function store(Request $request)
    {
        // Chỉ admin mới được tạo mới
        $this->authorizePermission('loai_phong.create');
        
        $validated = $request->validate([
            'ten_loai' => 'required|string|max:255|unique:loai_phong,ten_loai|regex:/^[\pL\s]+$/u',
            'mo_ta' => 'nullable|string|max:1000|regex:/^[\pL\pN\s.,()!?\-\'":;%&@\/]+$/u',
            'gia_co_ban' => 'required|numeric|min:100000|max:99999999',
            'gia_khuyen_mai' => 'nullable|numeric|min:0|max:99999999|lt:gia_co_ban',
            'so_luong_phong' => 'nullable|integer|min:0',
            'anh' => 'required|image|max:2048'
        ], [
            'ten_loai.required' => 'Tên loại phòng không được để trống.',
            'ten_loai.unique' => 'Tên loại phòng đã tồn tại.',
            'ten_loai.regex' => 'Tên loại phòng chỉ được chứa chữ cái và khoảng trắng.',
            'gia_co_ban.required' => 'Giá cơ bản là bắt buộc.',
            'gia_co_ban.min' => 'Giá cơ bản phải lớn hơn hoặc bằng 100.000đ',
            'gia_co_ban.max' => 'Giá cơ bản không được vượt quá 99.999.999đ',
            'gia_khuyen_mai.min' => 'Giá khuyến mãi phải lớn hơn hoặc bằng 0',
            'gia_khuyen_mai.max' => 'Giá khuyến mãi không được vượt quá 99.999.999đ',
            'gia_khuyen_mai.lt' => 'Giá khuyến mãi phải nhỏ hơn giá cơ bản',
            'so_luong_phong.min' => 'Số lượng phòng phải lớn hơn hoặc bằng 0',
            'anh.required' => 'Ảnh loại phòng là bắt buộc.',
            'anh.image' => 'Tệp tải lên phải là một hình ảnh.',
            'anh.max' => 'Kích thước hình ảnh không được vượt quá 2MB.',
            'mo_ta.max' => 'Mô tả không được vượt quá 1000 ký tự.',
            'mo_ta.regex' => 'Mô tả chỉ được chứa chữ cái, số và các ký tự đặc biệt cơ bản.',
        ]);

        // Xử lý upload ảnh
        if ($request->hasFile('anh')) {
            $file = $request->file('anh');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/loai_phong'), $filename);
            $validated['anh'] = 'uploads/loai_phong/' . $filename;
        }

        // Set so_luong_trong = so_luong_phong when creating new room type
        $validated['so_luong_trong'] = $validated['so_luong_phong'] ?? 0;

        LoaiPhong::create($validated);

        // Clear cache
        $this->clearLoaiPhongCache();

        return redirect()->route('admin.loai_phong.index')->with('success', 'Thêm loại phòng thành công!');
    }

    // Form chỉnh sửa
    public function edit($id)
    {
        // Nhân viên không được chỉnh sửa giá hoặc xóa
        $this->authorizePermission('loai_phong.edit');
        
        $loaiPhong = LoaiPhong::findOrFail($id);
        return view('admin.loai_phong.edit', compact('loaiPhong'));
    }

    // Cập nhật loại phòng
    public function update(Request $request, $id)
    {
        // Nhân viên không được chỉnh sửa giá
        $this->authorizePermission('loai_phong.edit');
        
        // Nếu là nhân viên, không cho sửa giá
        if ($this->hasRole('nhan_vien')) {
            $request->merge([
                'gia_co_ban' => LoaiPhong::findOrFail($id)->gia_co_ban,
                'gia_khuyen_mai' => LoaiPhong::findOrFail($id)->gia_khuyen_mai,
            ]);
        }
        
        $validated = $request->validate([
            'ten_loai' => 'required|string|max:255|regex:/^[\pL\s]+$/u',
            'mo_ta' => 'nullable|string|max:1000|regex:/^[\pL\pN\s.,()!?\-\'":;%&@\/]+$/u',
            'gia_co_ban' => 'required|numeric|min:100000|max:99999999',
            'gia_khuyen_mai' => 'nullable|numeric|min:0|max:99999999|lt:gia_co_ban',
            'so_luong_phong' => 'nullable|integer|min:0',
            'anh' => 'nullable|image|max:2048'
        ], [
            'ten_loai.required' => 'Tên loại phòng không được để trống.',
            'ten_loai.regex' => 'Tên loại phòng chỉ được chứa chữ cái và khoảng trắng.',
            'gia_co_ban.required' => 'Giá cơ bản là bắt buộc.',
            'gia_co_ban.min' => 'Giá cơ bản phải lớn hơn hoặc bằng 100.000đ',
            'gia_co_ban.max' => 'Giá cơ bản không được vượt quá 99.999.999đ',
            'gia_khuyen_mai.min' => 'Giá khuyến mãi phải lớn hơn hoặc bằng 0',
            'gia_khuyen_mai.max' => 'Giá khuyến mãi không được vượt quá 99.999.999đ',
            'gia_khuyen_mai.lt' => 'Giá khuyến mãi phải nhỏ hơn giá cơ bản',
            'so_luong_phong.min' => 'Số lượng phòng phải lớn hơn hoặc bằng 0',
            'anh.image' => 'Tệp tải lên phải là một hình ảnh.',
            'anh.max' => 'Kích thước hình ảnh không được vượt quá 2MB.',
            'mo_ta.max' => 'Mô tả không được vượt quá 1000 ký tự.',
            'mo_ta.regex' => 'Mô tả chỉ được chứa chữ cái, số và các ký tự đặc biệt cơ bản.',
        ]);
        $loaiPhong = LoaiPhong::findOrFail($id);

        // Xử lý upload ảnh
        if ($request->hasFile('anh')) {
            // Xóa ảnh cũ nếu có
            if ($loaiPhong->anh && file_exists(public_path($loaiPhong->anh))) {
                unlink(public_path($loaiPhong->anh));
            }

            // Upload ảnh mới
            $file = $request->file('anh');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/loai_phong'), $filename);
            $validated['anh'] = 'uploads/loai_phong/' . $filename;
        } else {
            // Giữ nguyên ảnh cũ nếu không upload ảnh mới
            unset($validated['anh']);
        }

        // Nếu thay đổi so_luong_phong, điều chỉnh so_luong_trong
        if (isset($validated['so_luong_phong'])) {
            $oldTotal = $loaiPhong->so_luong_phong ?? 0;
            $newTotal = $validated['so_luong_phong'];
            $oldAvailable = $loaiPhong->so_luong_trong ?? 0;
            
            // Tính số phòng đang được đặt
            $bookedRooms = $oldTotal - $oldAvailable;
            
            // Cập nhật so_luong_trong = so_luong_phong - số phòng đã đặt
            $validated['so_luong_trong'] = max(0, $newTotal - $bookedRooms);
        }

        $loaiPhong->update($validated);

        // Clear cache
        $this->clearLoaiPhongCache();

        return redirect()->route('admin.loai_phong.index')->with('success', 'Cập nhật thành công!');
    }

    // Xóa loại phòng
    public function destroy($id)
    {
        // Nhân viên không được xóa
        $this->authorizePermission('loai_phong.delete');
        
        $loaiPhong = LoaiPhong::findOrFail($id);
        // Không xóa dữ liệu; chuyển trạng thái sang "ngung"
        $loaiPhong->update(['trang_thai' => 'ngung']);

        // Clear cache
        $this->clearLoaiPhongCache();

        return redirect()->route('admin.loai_phong.index')->with('success', 'Đã vô hiệu hóa loại phòng (không xóa dữ liệu).');
    }

    // Bật/tắt trạng thái hoạt động của loại phòng
    public function toggleStatus($id)
    {
        // Nhân viên không được thay đổi trạng thái
        $this->authorizePermission('loai_phong.edit');
        
        $loaiPhong = LoaiPhong::findOrFail($id);
        $new = $loaiPhong->trang_thai === 'hoat_dong' ? 'ngung' : 'hoat_dong';
        $loaiPhong->update(['trang_thai' => $new]);

        // Clear cache
        $this->clearLoaiPhongCache();

        return redirect()->route('admin.loai_phong.index')->with('success', $new === 'ngung' ? 'Đã vô hiệu hóa loại phòng.' : 'Đã kích hoạt loại phòng.');
    }

    /**
     * Clear all LoaiPhong related cache
     */
    private function clearLoaiPhongCache()
    {
        Cache::forget('menu_loai_phongs');
        Cache::forget('dashboard_loai_phongs');
        Cache::forget('all_loai_phongs_active');
        
        // Clear all loai_phong detail cache (pattern matching)
        // Note: Laravel cache doesn't support pattern matching natively
        // In production, consider using Redis with tags or cache keys with prefix
    }
}
