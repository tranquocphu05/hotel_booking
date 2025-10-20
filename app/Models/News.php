<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class News extends Model
{
    protected $fillable = [
        'tieu_de',
        'slug',
        'tom_tat',
        'noi_dung',
        'hinh_anh',
        'trang_thai',
        'luot_xem',
        'nguoi_dung_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Tự động tạo slug từ tiêu đề
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($news) {
            if (empty($news->slug)) {
                $news->slug = Str::slug($news->tieu_de);
            }
        });
    }

    /**
     * Quan hệ với Admin (nguoi_dung)
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'nguoi_dung_id');
    }

    /**
     * Scope để lấy tin tức đã xuất bản
     */
    public function scopePublished($query)
    {
        return $query->where('trang_thai', 'published');
    }

    /**
     * Scope để lấy tin tức theo slug
     */
    public function scopeBySlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * Tăng lượt xem
     */
    public function incrementViews()
    {
        $this->increment('luot_xem');
    }
}
