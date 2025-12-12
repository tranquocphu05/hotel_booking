<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasRolePermissions
{
    /**
     * Kiểm tra quyền của user hiện tại
     */
    protected function checkPermission($action, $resource = null)
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Bạn cần đăng nhập để thực hiện hành động này.');
        }

        $role = $user->vai_tro;

        // Admin có tất cả quyền
        if ($role === 'admin') {
            return true;
        }

        // Kiểm tra quyền theo vai trò
        $permissions = $this->getRolePermissions($role);
        
        if (isset($permissions[$action])) {
            return $permissions[$action];
        }

        return false;
    }

    /**
     * Lấy danh sách quyền theo vai trò
     */
    protected function getRolePermissions($role)
    {
        $permissions = [
            'nhan_vien' => [
                // Loại phòng
                'loai_phong.view' => true,
                'loai_phong.edit' => false,
                'loai_phong.delete' => false,
                'loai_phong.create' => false,
                
                // Phòng
                'phong.view' => true,
                'phong.update_status' => true,
                'phong.assign_service' => true,
                'phong.view_history' => true,
                
                // Dịch vụ
                'service.view' => true,
                'service.add_to_room' => true,
                'service.edit_price' => false,
                
                // Đặt phòng
                'booking.create' => true,
                'booking.confirm_deposit' => true,
                'booking.update_customer' => true,
                'booking.edit_price' => false,
                
                // Hóa đơn
                'invoice.create' => true,
                'invoice.assign_service' => true,
                'invoice.export' => true,
                'invoice.edit_after_lock' => false,
                
                // Doanh thu
                'revenue.view_own' => false, // Nhân viên không xem doanh thu
                'revenue.view_total' => false,
                
                // Khách hàng
                'customer.view' => true,
                'customer.note' => true,
                'customer.delete' => false,
                
                // Đánh giá
                'review.view' => true,
                'review.reply' => true,
                'review.toggle' => true, // Cập nhật trạng thái hiển thị/ẩn
                
                // Voucher
                'voucher.view' => true,
                'voucher.apply' => true,
                'voucher.create' => false,
                'voucher.edit' => false,
                'voucher.delete' => false,
                
                // Yêu cầu đổi phòng
                'room_change.view' => true,
                'room_change.process' => true,
            ],
            'le_tan' => [
                // Loại phòng
                'loai_phong.view' => true,
                'loai_phong.edit' => false,
                'loai_phong.delete' => false,
                'loai_phong.create' => false,
                
                // Phòng
                'phong.view' => true,
                'phong.view_realtime' => true,
                'phong.checkin' => true,
                'phong.checkout' => true,
                'phong.change_room' => true,
                
                // Dịch vụ
                'service.view' => true,
                'service.edit_price' => false,
                
                // Đặt phòng
                'booking.create_direct' => true,
                'booking.checkin_prebooked' => true,
                'booking.receive_phone' => true,
                'booking.edit_price' => false,
                'booking.apply_voucher' => true,
                
                // Hóa đơn
                'invoice.create_at_checkout' => true,
                'invoice.print_vat' => true,
                'invoice.edit_after_lock' => false,
                
                // Doanh thu
                'revenue.view' => false,
                'revenue.view_own_shift' => true, // Lễ tân xem doanh thu ca của chính họ
                'revenue.view_total' => false, // Không xem báo cáo tổng
                
                // Khách hàng
                'customer.view_paying' => true,
                'customer.search_quick' => true,
                
                // Đánh giá
                'review.view' => true,
                'review.reply' => false,
                'review.toggle' => false, // Lễ tân không được cập nhật trạng thái
                
                // Voucher
                'voucher.view' => true,
                'voucher.apply' => true,
                'voucher.create' => false,
                'voucher.edit' => false,
                'voucher.delete' => false,
                
                // Yêu cầu đổi phòng
                'room_change.receive' => true,
                'room_change.transfer' => true,
            ],
        ];

        return $permissions[$role] ?? [];
    }

    /**
     * Abort nếu không có quyền
     */
    protected function authorizePermission($action, $resource = null)
    {
        if (!$this->checkPermission($action, $resource)) {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }
    }

    /**
     * Kiểm tra vai trò
     */
    protected function hasRole($roles)
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        $roles = is_array($roles) ? $roles : [$roles];
        return in_array($user->vai_tro, $roles);
    }
}

