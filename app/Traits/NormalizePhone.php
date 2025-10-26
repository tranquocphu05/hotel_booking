<?php

namespace App\Traits;

trait NormalizePhone
{
    /**
     * Chuẩn hóa số điện thoại về dạng 0xxxxxxxxx.
     * Tự động chuyển +84xxxxxx -> 0xxxxxx và loại bỏ ký tự không hợp lệ.
     */
    protected function normalizePhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        // Xóa khoảng trắng, chỉ giữ số và dấu +
        $phone = preg_replace('/\s+/', '', $phone);
        $phone = preg_replace('/[^0-9\+]/', '', $phone);

        // Nếu là +84xxxx => đổi thành 0xxxx
        if (str_starts_with($phone, '+84')) {
            $phone = '0' . substr($phone, 3);
        }

        return $phone;
    }

    /**
     * Tự động merge dữ liệu sdt đã chuẩn hóa vào request
     * @param string $field Tên trường cần chuẩn hóa (mặc định là 'sdt')
     */
    protected function normalizePhoneField(string $field = 'sdt'): void
    {
        if ($this->has($field)) {
            $this->merge([
                $field => $this->normalizePhone($this->input($field))
            ]);
        }
    }
}
