<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $table = 'invoice_items';

    protected $fillable = [
        'invoice_id',
        'type',
        'description',
        'quantity',
        'unit_price',
        'days',
        'amount',
        'start_date',
        'end_date',
        'meta',
        'created_by',
        'reason',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
        'meta' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
