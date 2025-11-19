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
    ]; // Mỗi dòng dịch vụ thuộc về một đặt phòng 
    public function booking()
    {
        return $this->belongsTo(DatPhong::class, 'dat_phong_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
