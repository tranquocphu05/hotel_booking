<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Invoice;

class BookingService extends Model
{
    use HasFactory; 
    protected $table = 'booking_services';
    protected $fillable = [
        'dat_phong_id',
        'phong_id',
        'invoice_id',
        'service_id',
        'quantity',
        'unit_price',
        'used_at',
        'note',
        'ghi_chu',
    ];

    protected $casts = [
        'used_at' => 'datetime',
        'unit_price' => 'decimal:2',
    ];

    // Mỗi dòng dịch vụ thuộc về một đặt phòng
    public function booking()
    {
        return $this->belongsTo(DatPhong::class, 'dat_phong_id');
    }

    // Dịch vụ có thể gán cho phòng cụ thể (NULL = áp dụng cho tất cả phòng)
    public function phong()
    {
        return $this->belongsTo(Phong::class, 'phong_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    /**
     * Kiểm tra xem dịch vụ này có áp dụng cho phòng cụ thể hay cho tất cả phòng không
     * phong_id = NULL => Áp dụng cho TẤT CẢ phòng trong booking (nhân với số phòng)
     * phong_id = X => Chỉ áp dụng cho phòng cụ thể
     */
    public function isGlobalService(): bool
    {
        return is_null($this->phong_id);
    }

    /**
     * Lấy số lượng nhân (nếu dịch vụ toàn cục, nhân với số phòng)
     */
    public function getEffectiveQuantity(): int
    {
        if ($this->isGlobalService() && $this->booking) {
            // Nhân quantity với tổng số phòng trong booking
            $totalRooms = $this->booking->so_luong_da_dat ?? 1;
            return $this->quantity * $totalRooms;
        }
        return $this->quantity;
    }

    /**
     * Tính tổng tiền cho dịch vụ này (đã nhân quantity)
     */
    public function getTotalPrice(): float
    {
        return (float)$this->unit_price * $this->getEffectiveQuantity();
    }
}

