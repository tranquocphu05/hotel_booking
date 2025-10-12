<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    protected $table = 'room_reviews';

    protected $fillable = [
        'booking_id',
        'room_booking_id',
        'room_id',
        'user_id',
        'rating',
        'content',
        'images',
        'reply',
        'is_active',
        'hidden_reason',
        'is_updated',
        'reply_at',
    ];

    protected $casts = [
        'images' => 'array',
        'is_active' => 'boolean',
        'is_updated' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // public function booking()
    // {
    //     return $this->belongsTo(Booking::class);
    // }

    // public function room_booking()
    // {
    //     return $this->belongsTo(Room_booking::class);
    // }

    // public function user()
    // {
    //     return $this->belongsTo(User::class, 'user_id', 'id');
    // }
}