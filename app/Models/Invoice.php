<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'hoa_don';

    const CREATED_AT = 'ngay_tao';
    const UPDATED_AT = null;

    protected $fillable = [
        'dat_phong_id',
        'tong_tien',
        'tien_phong',
        'tien_dich_vu',
        'giam_gia',
        'phuong_thuc',
        'trang_thai',
        'invoice_type',
    ];

    public function isPrepaid(): bool
    {
        return ($this->invoice_type ?? '') === 'PREPAID';
    }

    public function isExtra(): bool
    {
        return ($this->invoice_type ?? '') === 'EXTRA';
    }

    public function datPhong()
    {
        return $this->belongsTo(DatPhong::class, 'dat_phong_id');
    }
    // app/Models/Invoice.php
public function getPhuongThucUiAttribute(): array
{
    return match ($this->phuong_thuc) {
        'tien_mat'     => ['label' => 'Tiền mặt',      'bg' => 'bg-gray-100',   'text' => 'text-gray-700'],
        'vnpay'        => ['label' => 'VNPAY',         'bg' => 'bg-blue-100',   'text' => 'text-blue-700'],
        'momo'         => ['label' => 'MoMo',          'bg' => 'bg-pink-100',   'text' => 'text-pink-700'],
        'chuyen_khoan' => ['label' => 'Chuyển khoản',  'bg' => 'bg-indigo-100', 'text' => 'text-indigo-700'],
        default        => ['label' => 'Khác',          'bg' => 'bg-gray-100',   'text' => 'text-gray-700'],
    };
}

public function getTrangThaiUiAttribute(): array
{
    return match ($this->trang_thai) {
        'da_thanh_toan'  => ['label' => 'Đã thanh toán',  'bg' => 'bg-green-100',  'text' => 'text-green-700',  'icon' => 'fa-check-circle'],
        'hoan_tien'      => ['label' => 'Hoàn tiền',      'bg' => 'bg-red-100',    'text' => 'text-red-700',    'icon' => 'fa-rotate-left'],
        default          => ['label' => 'Chờ thanh toán', 'bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'icon' => 'fa-clock'],
    };
}



}
