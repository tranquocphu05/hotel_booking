<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'hoa_don';

    public function items()
    {
        return $this->hasMany(\App\Models\InvoiceItem::class, 'invoice_id');
    }

    const CREATED_AT = 'ngay_tao';
    const UPDATED_AT = null;

    protected $fillable = [
        'dat_phong_id',
        'tong_tien',
        'tien_phong',
        'tien_dich_vu',
        'phi_phat_sinh',
        'phi_them_nguoi',
        'giam_gia',
        'da_thanh_toan',
        'con_lai',
        'phuong_thuc',
        'trang_thai',
        'invoice_type',
        'original_invoice_id',
        // Optional note field added for adjustments/refunds
        'ghi_chu',
    ];

    protected $casts = [
        'tong_tien' => 'decimal:2',
        'tien_phong' => 'decimal:2',
        'tien_dich_vu' => 'decimal:2',
        'phi_phat_sinh' => 'decimal:2',
        'phi_them_nguoi' => 'decimal:2',
        'giam_gia' => 'decimal:2',
        'da_thanh_toan' => 'decimal:2',
        'con_lai' => 'decimal:2',
    ];

    public function datPhong()
    {
        return $this->belongsTo(DatPhong::class, 'dat_phong_id');
    }

    public function thanhToans()
    {
        return $this->hasMany(ThanhToan::class, 'hoa_don_id');
    }

    public function getPhuongThucUiAttribute(): array
    {
        if (empty($this->phuong_thuc)) {
            return [
                'label' => $this->trang_thai === 'cho_thanh_toan' ? 'Chưa chọn' : 'N/A',
                'bg' => 'bg-gray-50',
                'text' => 'text-gray-400'
            ];
        }

        return match ($this->phuong_thuc) {
            'tien_mat'     => ['label' => 'Tiền mặt',      'bg' => 'bg-emerald-50',   'text' => 'text-emerald-700'],
            'vnpay'        => ['label' => 'VNPAY',         'bg' => 'bg-blue-50',      'text' => 'text-blue-700'],
            'momo'         => ['label' => 'MoMo',          'bg' => 'bg-pink-50',      'text' => 'text-pink-700'],
            'chuyen_khoan' => ['label' => 'Chuyển khoản',  'bg' => 'bg-indigo-50',    'text' => 'text-indigo-700'],
            default        => ['label' => 'Khác',          'bg' => 'bg-gray-100',     'text' => 'text-gray-700'],
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

    /**
     * Check if this is an extra service invoice (for additional services after initial booking)
     */
    public function isExtra(): bool
    {
        // invoice_type values in DB may be stored in various cases ('EXTRA', 'extra', etc.).
        // Normalize to lowercase for a reliable check.
        return strtolower((string) ($this->invoice_type ?? '')) === 'extra';
    }

    /**
     * Check if this is a refund invoice
     */
    public function isRefund(): bool
    {
        return strtolower((string) ($this->invoice_type ?? '')) === 'refund';
    }

    /**
     * Get the original invoice (if this is a refund invoice)
     */
    public function originalInvoice()
    {
        return $this->belongsTo(Invoice::class, 'original_invoice_id');
    }

    /**
     * Get refund invoices for this invoice
     */
    public function refundInvoices()
    {
        return $this->hasMany(Invoice::class, 'original_invoice_id');
    }
}
