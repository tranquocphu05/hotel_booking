<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefundService extends Model
{
    protected $table = 'refund_services';

    protected $fillable = [
        'hoa_don_id',
        'dat_phong_id',
        'booking_service_id',
        'booking_room_ids',
        'total_refund',
        'refund_method',
        'refund_status',
        'bank_account_number',
        'bank_account_name',
        'bank_name',
        'note',
        'created_by',
    ];

    protected $casts = [
        'booking_room_ids' => 'array',
        'total_refund' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'hoa_don_id');
    }

    public function bookingService()
    {
        return $this->belongsTo(BookingService::class, 'booking_service_id');
    }
}
