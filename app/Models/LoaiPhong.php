<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoaiPhong extends Model
{
    //
    use HasFactory;

    protected $table = 'loai_phong';

    protected $fillable = [
        'ten_loai',
        'mo_ta',
        'gia_co_ban',
        'gia_khuyen_mai',    // Promotional price
        'so_luong_phong',    // Total number of rooms
        'so_luong_trong',    // Available rooms
        'diem_danh_gia',
        'so_luong_danh_gia',
        'trang_thai',
        'anh',
        'phi_tre_em',        // Surcharge rate per child per night (6-11 years old)
        'phi_em_be',         // Surcharge rate per infant per night (0-5 years old)
    ];

    // The loai_phong table currently doesn't have timestamp columns.
    // Disable automatic timestamps to avoid SQL errors when inserting.
    public $timestamps = false;

    /**
     * Relationship với bảng dat_phong
     */
    public function datPhongs()
    {
        return $this->hasMany(DatPhong::class, 'loai_phong_id');
    }

    /**
     * Relationship với bảng phong (rooms)
     */
    public function phongs()
    {
        return $this->hasMany(Phong::class, 'loai_phong_id');
    }

    /**
     * Relationship với bảng danh_gia (comments)
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'loai_phong_id');
    }

    /**
     * Check if room type has available rooms
     */
    public function hasAvailableRooms()
    {
        return $this->so_luong_trong > 0;
    }

    /**
     * Get percentage of rooms occupied
     */
    public function getOccupancyRateAttribute()
    {
        if ($this->so_luong_phong == 0) return 0;
        return round((($this->so_luong_phong - $this->so_luong_trong) / $this->so_luong_phong) * 100, 2);
    }

    /**
     * Get number of booked rooms
     */
    public function getRoomsDatAttribute()
    {
        return $this->so_luong_phong - $this->so_luong_trong;
    }

    /**
     * Kiểm tra xem loại phòng có đánh giá cao không (>= 4.5 sao)
     */
    public function hasHighRating()
    {
        return $this->diem_danh_gia >= 4.5;
    }

    /**
     * Lấy số sao hiển thị
     */
    public function getStarsAttribute()
    {
        return round($this->diem_danh_gia, 1);
    }

    /**
     * Lấy text mô tả đánh giá
     */
    public function getRatingTextAttribute()
    {
        if ($this->diem_danh_gia >= 4.8) return 'Ngoại lệ';
        if ($this->diem_danh_gia >= 4.5) return 'Tuyệt vời';
        if ($this->diem_danh_gia >= 4.0) return 'Rất tốt';
        if ($this->diem_danh_gia >= 3.5) return 'Tốt';
        if ($this->diem_danh_gia >= 3.0) return 'Khá tốt';
        return 'Trung bình';
    }

    /**
     * Get display price (promotional price if available, otherwise base price)
     */
    public function getGiaHienThiAttribute()
    {
        return $this->gia_khuyen_mai ?? $this->gia_co_ban;
    }

    /**
     * Check if room type has promotional price
     */
    public function hasPromotion()
    {
        return !is_null($this->gia_khuyen_mai) && $this->gia_khuyen_mai < $this->gia_co_ban;
    }

    /**
     * Tính tỷ lệ đặt phòng (booking rate) trong khoảng thời gian
     * Dựa trên số lượng booking thành công so với tổng số phòng
     * 
     * @param int $days Số ngày để tính (mặc định 30 ngày gần đây)
     * @return float Tỷ lệ đặt phòng (0-100)
     */
    public function getBookingRate($days = 30)
    {
        if ($this->so_luong_phong == 0) {
            return 0;
        }

        $startDate = now()->subDays($days)->startOfDay();
        
        // Tính tỷ lệ dựa trên số đêm đã được đặt
        $bookings = $this->datPhongs()
            ->where('ngay_dat', '>=', $startDate)
            ->whereIn('trang_thai', ['da_xac_nhan', 'da_tra'])
            ->whereHas('invoice', function($query) {
                $query->where('trang_thai', 'da_thanh_toan');
            })
            ->whereNotNull('ngay_nhan')
            ->whereNotNull('ngay_tra')
            ->get();

        $totalNightsBooked = $bookings->sum(function($booking) {
            try {
                if (!$booking->ngay_nhan || !$booking->ngay_tra) {
                    return 0;
                }
                // ngay_nhan và ngay_tra đã được cast thành Carbon trong model DatPhong
                $nights = $booking->ngay_nhan->diffInDays($booking->ngay_tra);
                return max(1, $nights); // Tối thiểu 1 đêm
            } catch (\Exception $e) {
                return 0;
            }
        });

        // Tính tỷ lệ dựa trên số đêm được đặt so với tổng số đêm có thể (số phòng * số ngày)
        // Nếu có nhiều phòng cùng loại, mỗi phòng có thể được đặt trong $days ngày
        $totalPossibleNights = $this->so_luong_phong * $days;
        
        if ($totalPossibleNights == 0) {
            return 0;
        }

        $bookingRate = ($totalNightsBooked / $totalPossibleNights) * 100;
        
        return round(min(100, max(0, $bookingRate)), 2);
    }

    /**
     * Scope để lấy các loại phòng có tỷ lệ đặt thấp
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLowBookingRate($query)
    {
        // Lấy tất cả phòng hoạt động và còn trống
        return $query->where('trang_thai', 'hoat_dong')
            ->where('so_luong_trong', '>', 0);
    }

    /**
     * Lấy danh sách phòng có tỷ lệ đặt thấp
     * 
     * @param int $days Số ngày để tính (mặc định 30 ngày)
     * @param float $maxRate Tỷ lệ đặt tối đa để được coi là "thấp" (mặc định 30%)
     * @param int $limit Số lượng phòng tối đa cần lấy
     * @return \Illuminate\Support\Collection
     */
    public static function getRoomsWithLowBookingRate($days = 30, $maxRate = 30.0, $limit = 6)
    {
        return static::where('trang_thai', 'hoat_dong')
            ->where('so_luong_trong', '>', 0)
            ->get()
            ->map(function($room) use ($days) {
                $room->booking_rate = $room->getBookingRate($days);
                return $room;
            })
            ->filter(function($room) use ($maxRate) {
                return $room->booking_rate <= $maxRate;
            })
            ->sortBy('booking_rate')
            ->take($limit)
            ->values();
    }
}
