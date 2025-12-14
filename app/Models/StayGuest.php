<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\InvoiceItem;
use App\Models\User;

class StayGuest extends Model
{
    use HasFactory;

    protected $table = 'stay_guests';

    public $timestamps = false; // we have created_at only

    protected $fillable = [
        'dat_phong_id',
        'phong_id',
        // Compatibility with older schema
        'full_name',
        'ten_khach',
        'dob',
        'age',
        'extra_fee',
        'phu_phi_them',
        'phi_them_nguoi',
        'created_by',
        'nguoi_them',
        'created_at',
        // Date range & relation to invoice item
        'start_date',
        'end_date',
        'invoice_item_id',
    ];

    protected $casts = [
        'dob' => 'date',
        'extra_fee' => 'decimal:2',
        'phu_phi_them' => 'decimal:2',
        'phi_them_nguoi' => 'decimal:2',
        'ngay_them' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function datPhong()
    {
        return $this->belongsTo(DatPhong::class, 'dat_phong_id');
    }

    public function phong()
    {
        return $this->belongsTo(Phong::class, 'phong_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function invoiceItem()
    {
        return $this->belongsTo(InvoiceItem::class, 'invoice_item_id');
    }
}
