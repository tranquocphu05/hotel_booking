<?php

namespace App\Models;

use App\Models\Invoice;
use App\Models\LoaiPhong;

use App\Models\Phong;
use App\Models\YeuCauDoiPhong as ModelsYeuCauDoiPhong;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use YeuCauDoiPhong;

class DatPhong extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dat_phong';

    /**
     * Indicates if the model should be timestamped.
     * DatPhong table doesn't have created_at/updated_at columns.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nguoi_dung_id',
        'loai_phong_id',
        'so_luong_da_dat',
        // legacy single-room column removed; use pivot `booking_rooms` instead
        'ngay_dat',
        'ngay_nhan',
        'ngay_tra',
        'phong_id',  // Specific room assigned (nullable, legacy support)
        'tong_tien',
        'tong_tien_phong',
        'so_nguoi',
        'so_tre_em',        // Number of children (6-12 years old)
        'so_em_be',         // Number of infants (0-5 years old)
        'phu_phi_tre_em',   // Surcharge for children
        'phu_phi_em_be',    // Surcharge for infants
        'ghi_chu',
        'voucher_id',
        'ly_do_huy',
        'ngay_huy',
        'username',
        'email',
        'sdt',
        'cccd',
        'trang_thai',
        'phi_phat_sinh',
        'thoi_gian_checkin',
        'thoi_gian_checkout',
        'nguoi_checkin',
        'nguoi_checkout',
        'ghi_chu_checkin',
        'ghi_chu_checkout',
        'ghi_chu_hoan_tien',
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'ngay_dat' => 'datetime',
        'ngay_nhan' => 'date',
        'ngay_tra' => 'date',
        'tong_tien' => 'decimal:0',
        'tien_phong' => 'decimal:0',
        'tien_dich_vu' => 'decimal:0',
        'phi_phat_sinh' => 'decimal:2',
        'thoi_gian_checkin' => 'datetime',
        'thoi_gian_checkout' => 'datetime',
    ];

    /**
     * Get the user that owns the booking.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'nguoi_dung_id');
    }

    /**
     * Get the room type associated with the booking.
     */
    public function loaiPhong()
    {
        return $this->belongsTo(LoaiPhong::class, 'loai_phong_id');
    }

    /**
     * Get the voucher associated with the booking.
     */
    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    /**
     * Get the specific room assigned to this booking (single room - legacy support).
     */
    public function phong()
    {
        return $this->belongsTo(Phong::class, 'phong_id');
    }

    /**
     * Get all rooms assigned to this booking via pivot table
     * Many-to-Many relationship through booking_rooms pivot table
     */
    public function phongs()
    {
        return $this->belongsToMany(Phong::class, 'booking_rooms', 'dat_phong_id', 'phong_id')
            ->withPivot('phu_phi', 'thoi_gian_checkin', 'thoi_gian_checkout', 'trang_thai_phong', 'phi_phat_sinh_phong')
            ->withTimestamps();
    }

    /**
     * Get all room types in this booking via pivot table
     * Many-to-Many relationship through booking_room_types pivot table
     */
    public function roomTypes()
    {
        return $this->belongsToMany(LoaiPhong::class, 'booking_room_types', 'dat_phong_id', 'loai_phong_id')
            ->withPivot('so_luong', 'gia_rieng', 'so_nguoi', 'so_tre_em', 'so_em_be')
            ->withTimestamps();
    }

    /**
     * Get array of assigned room IDs from pivot table
     * Returns array of room IDs: [1, 2, 3]
     * Falls back to single phong_id column if pivot is empty
     */
    public function getPhongIds()
    {

        // Get from pivot table (booking_rooms) - use correct relationship
        $phongIds = $this->phongs()->pluck('phong.id')->toArray();

        Log::info('DatPhong::getPhongIds - pivot result', ['booking_id' => $this->id, 'phong_ids' => $phongIds]);

        // Fallback: If no rooms in pivot, try legacy phong_id column
        if (empty($phongIds) && $this->phong_id) {
            Log::info('DatPhong::getPhongIds - using legacy phong_id', ['booking_id' => $this->id, 'phong_id' => $this->phong_id]);
            return [$this->phong_id];
        }

        if (empty($phongIds)) {
            Log::warning('DatPhong::getPhongIds - no rooms found for booking', ['booking_id' => $this->id]);
        }

        return $phongIds;
    }

    /**
     * Get assigned Phong models from pivot table
     */
    public function getAssignedPhongs()
    {
        return $this->phongs;
    }

    /**
     * Get only the rooms that are currently checked in for this booking.
     * Uses pivot `thoi_gian_checkin` and `thoi_gian_checkout` fields.
     */
    public function getCheckedInPhongs()
    {
        return $this->phongs->filter(function ($p) {
            // Consider room checked-in when:
            // - pivot thoi_gian_checkin is present and thoi_gian_checkout is empty
            // OR
            // - booking has a check-in (booking->thoi_gian_checkin) and the room's status is 'dang_thue'
            $pivotCheckin = !empty($p->pivot->thoi_gian_checkin) && empty($p->pivot->thoi_gian_checkout);
            $legacyChecked = $this->thoi_gian_checkin && empty($p->pivot->thoi_gian_checkout) && ($p->trang_thai === 'dang_thue');
            return $pivotCheckin || $legacyChecked;
        })->values();
    }

    /**
     * Check if a given guest composition can be accommodated by this booking
     * according to slot rules:
     * - base capacity: 2 persons per room
     * - each room provides ONE extra slot that can hold either:
     *   - 1 adult, or
     *   - 2 children, or
     *   - 3 infants
     * This method takes into account already-added stay guests (they consume slots)
     * and simulates allocation without touching the database.
     *
     * @param int $adults
     * @param int $children
     * @param int $infants
     * @return bool
     */
    public function canAccommodateGuests(int $adults, int $children, int $infants): bool
    {
        $roomCount = count($this->getPhongIds());

        if ($roomCount <= 0) return false; // no rooms

        // Standard capacity
        $standardCapacity = $roomCount * 2;
        $totalPeople = $adults + $children + $infants;

        // If total fits within standard capacity, no extras needed
        if ($totalPeople <= $standardCapacity) return true;

        // Simulate filling the base capacity (priority: adults -> children -> infants)
        $remainingBase = $standardCapacity;
        $adultsInBase = min($adults, $remainingBase);
        $remainingBase -= $adultsInBase;

        $childrenInBase = min($children, $remainingBase);
        $remainingBase -= $childrenInBase;

        $infantsInBase = min($infants, $remainingBase);
        $remainingBase -= $infantsInBase;

        $extraAdults = max(0, $adults - $adultsInBase);
        $extraChildren = max(0, $children - $childrenInBase);
        $extraInfants = max(0, $infants - $infantsInBase);

        // Slots (one per room)
        $slots = $roomCount;

        // 1) Allocate extra adults (1 slot each)
        if ($extraAdults > $slots) {
            return false; // not enough slots for adults
        }
        $slots -= $extraAdults;

        // 2) Allocate extra children (2 per slot)
        if ($extraChildren > ($slots * 2)) {
            return false; // not enough capacity for children
        }
        // consume slots used by children
        $slotsUsedByChildren = (int) ceil($extraChildren / 2);
        $slots -= $slotsUsedByChildren;

        // 3) Allocate extra infants (2 per slot)
        if ($extraInfants > ($slots * 2)) {
            return false; // not enough capacity for infants
        }

        // All extras fit according to priority
        return true;
    }

    /**
     * Validate whether a specific room composition (adults, children, infants)
     * can be accommodated within a single room according to rules:
     * - base 2 persons
     * - one extra slot that holds either 1 adult OR 2 children OR 3 infants
     */
    protected function isRoomCompositionValid(int $adults, int $children, int $infants): bool
    {
        // Try all ways to seat up to 2 people in the base seats.
        $maxBase = 2;
        for ($aBase = 0; $aBase <= min($maxBase, $adults); $aBase++) {
            for ($cBase = 0; $cBase <= min($maxBase - $aBase, $children); $cBase++) {
                $iBase = min($maxBase - $aBase - $cBase, $infants);

                $ra = $adults - $aBase;
                $rc = $children - $cBase;
                $ri = $infants - $iBase;

                // If nothing remains after base seats, valid
                if ($ra === 0 && $rc === 0 && $ri === 0) return true;

                // Try extra slot options (slot is exclusive):
                // - adult: at most 1 adult and no other categories
                if ($rc === 0 && $ri === 0 && $ra <= 1) return true;

                // - children only: up to 2 children
                if ($ra === 0 && $ri === 0 && $rc <= 2) return true;

                // - infants only: up to 2 infants
                if ($ra === 0 && $rc === 0 && $ri <= 2) return true;

                // - mixed children + infants: combined up to 2 (e.g., 1 child + 1 infant)
                if ($ra === 0 && ($rc + $ri) <= 2 && ($rc + $ri) > 0) return true;
            }
        }

        return false;
    }

    /**
     * Check if the booking can accommodate totals when certain rooms already have
     * a forced (pre-filled) composition. This runs a small backtracking search
     * distributing the remaining people across rooms and verifying per-room validity.
     *
     * - $forced is an associative array: [phongId => ['adults'=>x,'children'=>y,'infants'=>z], ...]
     */
    public function canAccommodateWithForcedAssignments(int $adults, int $children, int $infants, array $forced = []): bool
    {
        $rooms = $this->getPhongIds();
        if (empty($rooms)) return false;

        // Build current occupancy per room from existing stay guests
        $roomState = [];
        foreach ($rooms as $rid) {
            $roomState[$rid] = ['adults' => 0, 'children' => 0, 'infants' => 0];
        }

        foreach ($this->stayGuests as $g) {
            $rid = $g->phong_id;
            if (!isset($roomState[$rid])) continue; // skip guests assigned to rooms not in pivot
            $age = $g->age;
            // Age classification: infant <6, child 6-12 (inclusive), adult >=13
            if (is_null($age) || $age >= 13) $roomState[$rid]['adults']++;
            elseif ($age >= 6 && $age <= 12) $roomState[$rid]['children']++;
            else $roomState[$rid]['infants']++;
        }

        // Merge forced assignments into room state (these are additional counts we want the room to contain)
        foreach ($forced as $rid => $counts) {
            if (!isset($roomState[$rid])) return false; // forced room not part of booking
            $roomState[$rid]['adults'] += $counts['adults'] ?? 0;
            $roomState[$rid]['children'] += $counts['children'] ?? 0;
            $roomState[$rid]['infants'] += $counts['infants'] ?? 0;
            // Early validation: if the forced composition already invalid in that room -> fail early
            if (!$this->isRoomCompositionValid($roomState[$rid]['adults'], $roomState[$rid]['children'], $roomState[$rid]['infants'])) {
                return false;
            }
        }

        // Compute remaining people to distribute after accounting for current stayGuests and forced.
        $currentTotals = ['adults' => 0, 'children' => 0, 'infants' => 0];
        foreach ($roomState as $s) {
            $currentTotals['adults'] += $s['adults'];
            $currentTotals['children'] += $s['children'];
            $currentTotals['infants'] += $s['infants'];
        }

        $remAdults = $adults - $currentTotals['adults'];
        $remChildren = $children - $currentTotals['children'];
        $remInfants = $infants - $currentTotals['infants'];

        // Quick global pruning using room-count based slot model (each room grants one extra slot: 1 adult OR 2 children OR 2 infants)
        $rooms = count($roomState);
        $baseCapacity = $rooms * 2;
        $totalPeople = $adults + $children + $infants;
        if ($totalPeople <= $baseCapacity) {
            // fits in base capacity already
            return true;
        }

        $usedBase = min($baseCapacity, $currentTotals['adults'] + $currentTotals['children'] + $currentTotals['infants']);
        $extrasAlready = ($currentTotals['adults'] + $currentTotals['children'] + $currentTotals['infants']) - $usedBase;
        $remainingSlots = $rooms - max(0, $extrasAlready);

        // Simple upper bounds: if remaining children > remainingSlots*2 or infants > remainingSlots*2, fail fast
        if ($remAdults > $remainingSlots) return false;
        if ($remChildren > $remainingSlots * 2) return false;
        if ($remInfants > $remainingSlots * 2) return false;

        if ($remAdults < 0 || $remChildren < 0 || $remInfants < 0) {
            // forced/assigned guests already exceed requested totals
            return false;
        }

        // Prepare list of rooms and their current occupancy to try to fill the remaining people
        $roomIds = array_keys($roomState);
        $n = count($roomIds);

        // Backtracking function: try to assign remaining people starting from room index i
        $that = $this;
        $cache = [];

        $dfs = function($i, $ra, $rc, $ri) use (&$dfs, $roomIds, $roomState, $n, $that, &$cache) {
            $key = implode('|', [$i, $ra, $rc, $ri]);
            if (isset($cache[$key])) return $cache[$key];

            if ($ra === 0 && $rc === 0 && $ri === 0) return $cache[$key] = true;
            if ($i >= $n) return $cache[$key] = false;

            $rid = $roomIds[$i];
            $base = $roomState[$rid];

            // Determine how many additional people we may try adding to this room
            // Upper bounds: base 2 + extra up to 2 (total max 4)
            $currentTotal = $base['adults'] + $base['children'] + $base['infants'];
            $maxTotalForRoom = 4; // base 2 + at most 2 extra (children/infants) or 1 adult
            $maxAdd = max(0, $maxTotalForRoom - $currentTotal);

            // Try all reasonable allocations of additional adults/children/infants to this room (bounded by remaining and maxAdd)
            for ($addA = 0; $addA <= min($ra, $maxAdd); $addA++) {
                for ($addC = 0; $addC <= min($rc, $maxAdd - $addA); $addC++) {
                    for ($addI = 0; $addI <= min($ri, $maxAdd - $addA - $addC); $addI++) {
                        // Check that the new composition for the room is valid
                        $na = $base['adults'] + $addA;
                        $nc = $base['children'] + $addC;
                        $ni = $base['infants'] + $addI;

                        if (!$that->isRoomCompositionValid($na, $nc, $ni)) continue;

                        // Recurse to next room
                        if ($dfs($i + 1, $ra - $addA, $rc - $addC, $ri - $addI)) {
                            return $cache[$key] = true;
                        }
                    }
                }
            }

            // Also allow skipping adding more people to this room
            if ($dfs($i + 1, $ra, $rc, $ri)) return $cache[$key] = true;

            return $cache[$key] = false;
        };

        return $dfs(0, $remAdults, $remChildren, $remInfants);
    }

    /**
     * Convenience: check whether adding a single guest of the given category to the
     * specified room is possible while keeping the booking valid.
     * $category: 'adult'|'child'|'infant'
     */
    /**
     * Counts the number of already-added stay guests for a room by category
     */
    public function getRoomAddedCounts(int $phongId): array
    {
        $counts = ['adults' => 0, 'children' => 0, 'infants' => 0];
        foreach ($this->stayGuests()->where('phong_id', $phongId)->get() as $g) {
            $age = $g->age;
            // Age classification: infant <6, child 6-12 (inclusive), adult >=13
            if (is_null($age) || $age >= 13) $counts['adults']++;
            elseif ($age >= 6 && $age <= 12) $counts['children']++;
            else $counts['infants']++;
        }
        return $counts;
    }

    /**
     * Lấy số khách đã khai báo ban đầu từ client booking (trong booking_rooms)
     * Chỉ tính trẻ em và em bé vì đây là số "thêm" so với người lớn cơ bản
     */
    public function getRoomInitialCounts(int $phongId): array
    {
        $counts = ['adults' => 0, 'children' => 0, 'infants' => 0];
        
        if (\Illuminate\Support\Facades\Schema::hasColumn('booking_rooms', 'so_tre_em')) {
            $row = DB::table('booking_rooms')
                ->where('dat_phong_id', $this->id)
                ->where('phong_id', $phongId)
                ->first();
            if ($row) {
                // Chỉ đếm trẻ em và em bé từ booking ban đầu
                // Người lớn không tính vì họ là khách chính, không phải khách thêm
                $counts['children'] = (int) ($row->so_tre_em ?? 0);
                $counts['infants'] = (int) ($row->so_em_be ?? 0);
            }
        }
        
        return $counts;
    }

    public function canAddGuestToRoom(int $phongId, string $category): bool
    {
        // Lấy số khách khai báo ban đầu từ booking_rooms
        $initial = $this->getRoomCurrentCounts($phongId);
        
        // Lấy số khách đã thêm SAU check-in (stayGuests)
        $added = $this->getRoomAddedCounts($phongId);

        // Quy tắc sức chứa phòng:
        // - Sức chứa cơ bản: 2 người
        // - Tối đa extra: +1 người lớn, +2 trẻ em, +1 em bé
        // - Tổng tối đa: 2 + 1 + 2 + 1 = 6 người
        $baseCapacity = 2;
        $maxExtraAdults = 1;
        $maxExtraChildren = 2;
        $maxExtraInfants = 1;

        // Tính tổng số người hiện tại
        $totalAdults = $initial['adults'] + $added['adults'];
        $totalChildren = $initial['children'] + $added['children'];
        $totalInfants = $initial['infants'] + $added['infants'];
        
        // Tính số người vượt quá base capacity
        $remainingBase = $baseCapacity;
        $adultsInBase = min($totalAdults, $remainingBase);
        $remainingBase -= $adultsInBase;
        $childrenInBase = min($totalChildren, $remainingBase);
        $remainingBase -= $childrenInBase;
        $infantsInBase = min($totalInfants, $remainingBase);
        
        // Số extra hiện tại (đã vượt quá base)
        $currentExtraAdults = $totalAdults - $adultsInBase;
        $currentExtraChildren = $totalChildren - $childrenInBase;
        $currentExtraInfants = $totalInfants - $infantsInBase;
        
        // Kiểm tra nếu thêm 1 khách mới có vượt quá giới hạn không
        if ($category === 'adult') {
            // Thêm 1 người lớn: kiểm tra extra adults không vượt quá max
            if ($currentExtraAdults >= $maxExtraAdults) {
                return false;
            }
        } elseif ($category === 'child') {
            // Thêm 1 trẻ em: kiểm tra extra children không vượt quá max
            if ($currentExtraChildren >= $maxExtraChildren) {
                return false;
            }
        } else {
            // Thêm 1 em bé: kiểm tra extra infants không vượt quá max
            if ($currentExtraInfants >= $maxExtraInfants) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get current per-room counts, preferring booking_rooms pivot columns when available.
     * Falls back to distributing booking-level totals across rooms (legacy behavior) if columns are missing.
     */
    public function getRoomCurrentCounts(int $phongId): array
    {
        if (\Illuminate\Support\Facades\Schema::hasColumn('booking_rooms', 'so_nguoi_lon')) {
            $row = DB::table('booking_rooms')
                ->where('dat_phong_id', $this->id)
                ->where('phong_id', $phongId)
                ->first();
            if ($row) {
                return [
                    'adults' => (int) ($row->so_nguoi_lon ?? 0),
                    'children' => (int) ($row->so_tre_em ?? 0),
                    'infants' => (int) ($row->so_em_be ?? 0),
                ];
            }
            return ['adults' => 0, 'children' => 0, 'infants' => 0];
        }

        // Legacy fallback: distribute booking totals deterministically across assigned rooms
        $initialAdults = (int)($this->so_nguoi ?? 0);
        $initialChildren = (int)($this->so_tre_em ?? 0);
        $initialInfants = (int)($this->so_em_be ?? 0);
        $roomIds = $this->getPhongIds();
        $n = count($roomIds);
        if ($n === 0) return ['adults' => 0, 'children' => 0, 'infants' => 0];

        $aBase = intdiv($initialAdults, $n); $aRem = $initialAdults % $n;
        $cBase = intdiv($initialChildren, $n); $cRem = $initialChildren % $n;
        $iBase = intdiv($initialInfants, $n); $iRem = $initialInfants % $n;

        $map = [];
        foreach ($roomIds as $idx => $rid) {
            $map[$rid] = [
                'adults' => $aBase + ($idx < $aRem ? 1 : 0),
                'children' => $cBase + ($idx < $cRem ? 1 : 0),
                'infants' => $iBase + ($idx < $iRem ? 1 : 0),
            ];
        }
        return $map[$phongId] ?? ['adults' => 0, 'children' => 0, 'infants' => 0];
    }

    /**
     * Atomically increment booking_rooms counters for a room (creates pivot row if missing).
     * If the booking_rooms columns are not present, log a warning and fallback to incrementing
     * booking-level totals to preserve behavior (administrators should run migration to enable per-room counts).
     */
    public function incrementBookingRoomCount(int $phongId, string $category, int $by = 1): void
    {
        $col = $category === 'adult' ? 'so_nguoi_lon' : ($category === 'child' ? 'so_tre_em' : 'so_em_be');
        if (\Illuminate\Support\Facades\Schema::hasColumn('booking_rooms', $col)) {
            $now = now();
            DB::statement(
                'INSERT INTO booking_rooms (dat_phong_id, phong_id, ' . $col . ', created_at, updated_at) VALUES (?, ?, ?, ?, ?) ' .
                'ON DUPLICATE KEY UPDATE ' . $col . ' = COALESCE(' . $col . ', 0) + VALUES(' . $col . '), updated_at = VALUES(updated_at)',
                [$this->id, $phongId, $by, $now, $now]
            );
            return;
        }

        // Fallback: increment booking-level totals (deprecated behavior)
        Log::warning('incrementBookingRoomCount: booking_rooms columns missing, falling back to dat_phong counters', ['booking_id' => $this->id, 'phong_id' => $phongId, 'category' => $category]);
        if ($category === 'adult' && \Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'so_nguoi')) {
            $this->increment('so_nguoi', $by);
        } elseif ($category === 'child' && \Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'so_tre_em')) {
            $this->increment('so_tre_em', $by);
        } elseif ($category === 'infant' && \Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'so_em_be')) {
            $this->increment('so_em_be', $by);
        }
    }

    /**
     * Atomically decrement booking_rooms counters for a room (never below zero).
     */
    public function decrementBookingRoomCount(int $phongId, string $category, int $by = 1): void
    {
        $col = $category === 'adult' ? 'so_nguoi_lon' : ($category === 'child' ? 'so_tre_em' : 'so_em_be');
        if (\Illuminate\Support\Facades\Schema::hasColumn('booking_rooms', $col)) {
            DB::statement(
                'UPDATE booking_rooms SET ' . $col . ' = GREATEST(COALESCE(' . $col . ', 0) - ?, 0), updated_at = ? WHERE dat_phong_id = ? AND phong_id = ?',
                [$by, now(), $this->id, $phongId]
            );
            return;
        }

        // Fallback: decrement booking-level totals (deprecated behavior)
        Log::warning('decrementBookingRoomCount: booking_rooms columns missing, falling back to dat_phong counters', ['booking_id' => $this->id, 'phong_id' => $phongId, 'category' => $category]);
        if ($category === 'adult' && \Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'so_nguoi')) {
            $this->decrement('so_nguoi', $by);
        } elseif ($category === 'child' && \Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'so_tre_em')) {
            $this->decrement('so_tre_em', $by);
        } elseif ($category === 'infant' && \Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'so_em_be')) {
            $this->decrement('so_em_be', $by);
        }
    }

    /**
     * Determine whether a given composition fits into a single room according to rules.
     */
    public function doesRoomCompositionFit(int $adults, int $children, int $infants): bool
    {
        // Quy tắc mới: mỗi phòng có sức chứa cơ bản 2 người
        // Có thể thêm tối đa: +1 người lớn VÀ +2 trẻ em VÀ +2 em bé
        // Tổng tối đa = 2 (cơ bản) + 1 (người lớn thêm) + 2 (trẻ em thêm) + 2 (em bé thêm) = 7 người
        
        $baseCapacity = 2; // Sức chứa cơ bản của phòng
        $maxExtraAdults = 1;
        $maxExtraChildren = 2;
        $maxExtraInfants = 2;
        
        $total = $adults + $children + $infants;
        
        // Nếu tổng <= sức chứa cơ bản, luôn OK
        if ($total <= $baseCapacity) return true;
        
        // Tính số người vượt quá sức chứa cơ bản
        // Ưu tiên người lớn vào base trước, sau đó trẻ em, cuối cùng em bé
        $remainingBase = $baseCapacity;
        $adultsInBase = min($adults, $remainingBase);
        $remainingBase -= $adultsInBase;
        $childrenInBase = min($children, $remainingBase);
        $remainingBase -= $childrenInBase;
        $infantsInBase = min($infants, $remainingBase);
        
        // Số người thêm (vượt quá base)
        $extraAdults = $adults - $adultsInBase;
        $extraChildren = $children - $childrenInBase;
        $extraInfants = $infants - $infantsInBase;
        
        // Kiểm tra từng loại không vượt quá giới hạn
        if ($extraAdults > $maxExtraAdults) return false;
        if ($extraChildren > $maxExtraChildren) return false;
        if ($extraInfants > $maxExtraInfants) return false;
        
        return true;
    }

    /**
     * Return how many stay guests are already assigned to a room
     */
    public function getRoomStayGuestCount(int $phongId): int
    {
        return (int) $this->stayGuests()->where('phong_id', $phongId)->count();
    }

    /**
     * How many base seats (of the 2 base persons) remain free in a room
     */
    public function getRoomBaseSeatsRemaining(int $phongId): int
    {
        // If booking_rooms per-room columns exist, use them as source of truth
        if (\Illuminate\Support\Facades\Schema::hasColumn('booking_rooms', 'so_nguoi_lon')) {
            $r = $this->getRoomCurrentCounts($phongId);
            $totalInRoom = $r['adults'] + $r['children'] + $r['infants'];
            return max(0, 2 - $totalInRoom);
        }

        // Legacy fallback: count stayGuests assigned to this room
        $stayCount = $this->getRoomStayGuestCount($phongId);

        // Compute original booking occupants by category (so_nguoi = adults, so_tre_em = children, so_em_be = infants)
        $initialAdults = (int)($this->so_nguoi ?? 0);
        $initialChildren = (int)($this->so_tre_em ?? 0);
        $initialInfants = (int)($this->so_em_be ?? 0);
        $totalInitial = $initialAdults + $initialChildren + $initialInfants;

        // Distribute initial booked people across rooms filling up to 2 per room deterministically by pivot order
        $roomIds = $this->getPhongIds();
        $initialPerRoom = [];
        $rem = $totalInitial;
        foreach ($roomIds as $rid) {
            $take = min(2, $rem);
            $initialPerRoom[$rid] = $take;
            $rem -= $take;
        }

        $used = ($initialPerRoom[$phongId] ?? 0) + $stayCount;
        return max(0, 2 - $used);
    }

    /**
     * Add a room to booking via pivot table
     */
    public function addPhong($phongId)
    {
        // Check if already attached
        if (!$this->phongs()->where('phong_id', $phongId)->exists()) {
            $this->phongs()->attach($phongId);
        }
        return $this;
    }

    /**
     * Remove a room from booking via pivot table
     */
    public function removePhong($phongId)
    {
        $this->phongs()->detach($phongId);
        return $this;
    }

    /**
     * Sync rooms with booking (replace all rooms)
     */
    public function syncPhongs(array $phongIds)
    {
        $this->phongs()->sync($phongIds);
        return $this;
    }

    /**
     * Get all room types in this booking from pivot table
     */
    public function getRoomTypes()
    {
        $roomTypes = $this->roomTypes()->get();

        // If no room types in pivot, fallback to legacy single room type
        if ($roomTypes->isEmpty() && $this->loai_phong_id) {
            return collect([
                [
                    'loai_phong_id' => $this->loai_phong_id,
                    'so_luong' => $this->so_luong_da_dat ?? 1,
                    'gia_rieng' => $this->tong_tien ?? 0,
                    // Fallback: lấy số khách từ booking level
                    'so_nguoi' => $this->so_nguoi ?? 0,
                    'so_tre_em' => $this->so_tre_em ?? 0,
                    'so_em_be' => $this->so_em_be ?? 0,
                ]
            ]);
        }

        // Transform to array format for compatibility
        return $roomTypes->map(function ($roomType) {
            return [
                'loai_phong_id' => $roomType->id,
                'ten_loai' => $roomType->ten_loai,
                'so_luong' => $roomType->pivot->so_luong,
                'gia_rieng' => $roomType->pivot->gia_rieng,
                'so_nguoi' => $roomType->pivot->so_nguoi ?? 0,
                'so_tre_em' => $roomType->pivot->so_tre_em ?? 0,
                'so_em_be' => $roomType->pivot->so_em_be ?? 0,
            ];
        });

    }
    /**
     * Add room type to booking via pivot table
     */
    public function addRoomType($loaiPhongId, $soLuong, $giaRieng)
    {
        $this->roomTypes()->attach($loaiPhongId, [
            'so_luong' => $soLuong,
            'gia_rieng' => $giaRieng,
        ]);
        return $this;
    }

    /**
     * Sync room types with booking (replace all room types)
     */
    public function syncRoomTypes(array $roomTypesData)
    {

        $this->roomTypes()->sync($roomTypesData);
        return $this;
    }
    /**
     * Get all booking services for this booking.
     */
    public function services()
    {
        return $this->hasMany(BookingService::class, 'dat_phong_id');
    }

    /**
     * Get the invoice associated with the booking.
     */
    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'dat_phong_id');
    }

    /**
     * Get the actual guests staying for this booking (separate from booking->so_nguoi)
     */
    public function stayGuests()
    {
        return $this->hasMany(\App\Models\StayGuest::class, 'dat_phong_id');
    }

    /**
     * Scope a query to only include bookings with a specific status.
     */
    public function scopeTrangThai($query, $trangThai)
    {
        return $query->where('trang_thai', $trangThai);
    }

    /**
     * @deprecated Use whereHas('phongs', function($q) use ($phongId) { $q->where('phong_id', $phongId); }) instead
     *
     * Legacy scope: safely filter bookings that contain a specific room id
     * Falls back to legacy `phong_id` when `phong_ids` JSON column is not present.
     * Kept for backward compatibility only. System now uses pivot table booking_rooms.
     *
     * Usage: DatPhong::whereContainsPhongId($phongId)->get();
     */
    public function scopeWhereContainsPhongId($query, $phongId)
    {
        $table = $query->getModel()->getTable();
        if (\Illuminate\Support\Facades\Schema::hasColumn($table, 'phong_ids')) {
            return $query->whereJsonContains('phong_ids', $phongId);
        }

        return $query->where('phong_id', $phongId);
    }

    /**
     * @deprecated Use orWhereHas('phongs', function($q) use ($phongId) { $q->where('phong_id', $phongId); }) instead
     *
     * Legacy scope: safely add an OR condition for bookings that contain a specific room id
     * Falls back to legacy `phong_id` when `phong_ids` JSON column is not present.
     * Kept for backward compatibility only. System now uses pivot table booking_rooms.
     *
     * Usage: DatPhong::orWhereContainsPhongId($phongId)
     */
    public function scopeOrWhereContainsPhongId($query, $phongId)
    {
        $table = $query->getModel()->getTable();
        if (\Illuminate\Support\Facades\Schema::hasColumn($table, 'phong_ids')) {
            return $query->orWhere(function ($q) use ($phongId) {
                $q->whereJsonContains('phong_ids', $phongId);
            });
        }

        return $query->orWhere('phong_id', $phongId);
    }

    /**
     * Check if booking is confirmed
     */
    public function isDaXacNhan()
    {
        return $this->trang_thai === 'da_xac_nhan';
    }

    /**
     * Check if booking is cancelled
     */
    public function isDaHuy()
    {
        return $this->trang_thai === 'da_huy';
    }

    /**
     * Check if booking is completed
     */
    public function isDaTra()
    {
        return $this->trang_thai === 'da_tra';
    }

    /**
     * Check if guest can request services (checked in but not checked out)
     */
    public function canRequestService()
    {
        return $this->thoi_gian_checkin
            && !$this->thoi_gian_checkout
            && $this->trang_thai === 'da_xac_nhan';
    }

    /**
     * Check if booking can be checked in
     */
    public function canCheckin()
    {
        return $this->trang_thai === 'da_xac_nhan'
            && !$this->thoi_gian_checkin;
    }

    /**
     * Check if booking can be checked out
     */
    public function canCheckout()
    {
        return $this->trang_thai === 'da_xac_nhan'
            && $this->thoi_gian_checkin
            && !$this->thoi_gian_checkout;
    }

    /**
     * Validate if status transition is allowed
     *
     * @param string $newStatus
     * @param string|null $oldStatus
     * @return bool
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validateStatusTransition($newStatus, $oldStatus = null)
    {
        $oldStatus = $oldStatus ?? $this->trang_thai;

        // Terminal states cannot be changed
        $terminalStates = ['da_tra', 'da_huy', 'tu_choi', 'thanh_toan_that_bai'];
        if (in_array($oldStatus, $terminalStates) && $newStatus !== $oldStatus) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'trang_thai' => "Không thể thay đổi trạng thái từ '{$oldStatus}' sang '{$newStatus}'. Booking đã ở trạng thái cuối cùng."
            ]);
        }

        // Define valid transitions
        $validTransitions = [
            'cho_xac_nhan' => ['da_xac_nhan', 'da_huy', 'tu_choi', 'thanh_toan_that_bai'],
            'da_xac_nhan' => ['da_tra', 'da_huy', 'da_chong'],
            'da_chong' => ['da_xac_nhan'], // Có thể hủy chống
        ];

        // Check if transition is valid
        if (!isset($validTransitions[$oldStatus])) {
            // If old status is not in valid transitions, it's a terminal state (already checked above)
            return true;
        }

        if (!in_array($newStatus, $validTransitions[$oldStatus])) {
            $allowedStatuses = implode(', ', $validTransitions[$oldStatus]);
            throw \Illuminate\Validation\ValidationException::withMessages([
                'trang_thai' => "Không thể chuyển từ trạng thái '{$oldStatus}' sang '{$newStatus}'. Chỉ cho phép: {$allowedStatuses}"
            ]);
        }

        // Additional business rules
        // Cannot cancel booking that has been checked in (applies to both cho_xac_nhan and da_xac_nhan)
        if ($newStatus === 'da_huy' && $this->thoi_gian_checkin) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'trang_thai' => 'Không thể hủy booking đã check-in. Vui lòng thực hiện check-out trước.'
            ]);
        }

        // Cannot set da_tra without checkout
        if ($newStatus === 'da_tra' && !$this->thoi_gian_checkout) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'trang_thai' => 'Không thể đặt trạng thái "đã trả" mà chưa thực hiện check-out.'
            ]);
        }

        return true;
    }

    /**
     * Check if room type has available rooms
     *
     * @param int $loaiPhongId
     * @return bool
     */
    public static function hasAvailableRooms($loaiPhongId)
    {
        $loaiPhong = LoaiPhong::find($loaiPhongId);
        return $loaiPhong && $loaiPhong->so_luong_trong > 0;
    }

    /**
     * Decrease available room count when booking is created/confirmed
     */
    public static function boot()
    {
        parent::boot();

        // Note: so_luong_trong is updated directly in BookingController transaction
        // to ensure atomicity with lockForUpdate. This prevents double-decrement.
        // The created event is kept for edge cases but should not decrement again.

        // When booking status changes
        static::updating(function ($booking) {
            if ($booking->isDirty('trang_thai')) {
                $oldStatus = $booking->getOriginal('trang_thai');
                $newStatus = $booking->trang_thai;

                // Validate status transition
                $booking->validateStatusTransition($newStatus, $oldStatus);
            }
        });

        static::updated(function ($booking) {
            if ($booking->isDirty('trang_thai')) {
                $oldStatus = $booking->getOriginal('trang_thai');
                $newStatus = $booking->trang_thai;

                // Thu thập danh sách loại phòng cần cập nhật
                $roomTypes = $booking->getRoomTypes();
                $loaiPhongIdsToUpdate = [];

                foreach ($roomTypes as $roomType) {
                    if (isset($roomType['loai_phong_id'])) {
                        $loaiPhongIdsToUpdate[] = $roomType['loai_phong_id'];
                    }
                }

                // Thêm loai_phong_id chính nếu chưa có
                if ($booking->loai_phong_id && !in_array($booking->loai_phong_id, $loaiPhongIdsToUpdate)) {
                    $loaiPhongIdsToUpdate[] = $booking->loai_phong_id;
                }

                // Cập nhật trạng thái phòng (sử dụng DB facade để tránh trigger Phong observer trùng lặp)
                // Load relationships
                $booking->load(['phong']);

                // Update Phong status if phong_id is set (legacy)
                if ($booking->phong_id) {
                    $phong = \App\Models\Phong::find($booking->phong_id);
                    if ($phong) {
                        // Khi booking bị hủy/từ chối -> phòng chuyển về "trống"
                        if (
                            in_array($newStatus, ['da_huy', 'tu_choi', 'thanh_toan_that_bai'])
                            && in_array($oldStatus, ['cho_xac_nhan', 'da_xac_nhan'])
                        ) {
                            // Sử dụng DB facade để update trực tiếp, tránh trigger observer
                            \Illuminate\Support\Facades\DB::table('phong')
                                ->where('id', $phong->id)
                                ->update(['trang_thai' => 'trong']);
                        }
                        // Khi booking hoàn thành (check-out) -> phòng chuyển về "dang_don" để nhân viên dọn
                        elseif ($newStatus === 'da_tra' && $oldStatus !== 'da_tra') {
                            // Sử dụng DB facade để update trực tiếp, tránh trigger observer
                            \Illuminate\Support\Facades\DB::table('phong')
                                ->where('id', $phong->id)
                                ->update(['trang_thai' => 'dang_don']);
                        }
                    }
                }

                // Update Phong status via phong_ids (pivot table)
                $assignedPhongs = $booking->getAssignedPhongs();
                foreach ($assignedPhongs as $phong) {
                    // Khi booking bị hủy/từ chối -> phòng chuyển về "trống"
                    if (
                        in_array($newStatus, ['da_huy', 'tu_choi', 'thanh_toan_that_bai'])
                        && in_array($oldStatus, ['cho_xac_nhan', 'da_xac_nhan'])
                    ) {
                        // CRITICAL: Kiểm tra xem phòng có đang được đặt cho booking khác không
                        $hasOtherBooking = \App\Models\DatPhong::where('id', '!=', $booking->id)
                            ->whereHas('phongs', function ($q) use ($phong) {
                                $q->where('phong_id', $phong->id);
                            })
                            ->where(function ($q) use ($booking) {
                                $q->where('ngay_tra', '>', $booking->ngay_nhan)
                                    ->where('ngay_nhan', '<', $booking->ngay_tra);
                            })
                            ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
                            ->exists();

                        if (!$hasOtherBooking) {
                            // Sử dụng DB facade để update trực tiếp, tránh trigger observer
                            \Illuminate\Support\Facades\DB::table('phong')
                                ->where('id', $phong->id)
                                ->update(['trang_thai' => 'trong']);
                        }
                    }
                    // Khi booking hoàn thành (check-out) -> phòng chuyển về "dang_don" để nhân viên dọn
                    elseif ($newStatus === 'da_tra' && $oldStatus !== 'da_tra') {
                        // Sử dụng DB facade để update trực tiếp, tránh trigger observer
                        \Illuminate\Support\Facades\DB::table('phong')
                            ->where('id', $phong->id)
                            ->update(['trang_thai' => 'dang_don']);
                    }
                }

                // Tính lại so_luong_trong MỘT LẦN tại cuối cho tất cả loại phòng liên quan
                // Bao gồm cả 'trong' và 'dang_don' vì phòng đang dọn vẫn có thể đặt trước
                foreach (array_unique($loaiPhongIdsToUpdate) as $loaiPhongId) {
                    $trongCount = \App\Models\Phong::where('loai_phong_id', $loaiPhongId)
                        ->whereIn('trang_thai', ['trong', 'dang_don'])
                        ->count();
                    
                    LoaiPhong::where('id', $loaiPhongId)
                        ->update(['so_luong_trong' => $trongCount]);
                }
            }
        });

        // When booking is deleted, recalculate so_luong_trong
        static::deleted(function ($booking) {
            $roomTypes = $booking->getRoomTypes();
            $loaiPhongIdsToUpdate = [];

            foreach ($roomTypes as $roomType) {
                if (isset($roomType['loai_phong_id'])) {
                    $loaiPhongIdsToUpdate[] = $roomType['loai_phong_id'];
                }
            }

            if ($booking->loai_phong_id && !in_array($booking->loai_phong_id, $loaiPhongIdsToUpdate)) {
                $loaiPhongIdsToUpdate[] = $booking->loai_phong_id;
            }

            // Bao gồm cả 'trong' và 'dang_don' vì phòng đang dọn vẫn có thể đặt trước
            foreach (array_unique($loaiPhongIdsToUpdate) as $loaiPhongId) {
                $trongCount = \App\Models\Phong::where('loai_phong_id', $loaiPhongId)
                    ->whereIn('trang_thai', ['trong', 'dang_don'])
                    ->count();

                LoaiPhong::where('id', $loaiPhongId)
                    ->update(['so_luong_trong' => $trongCount]);
            }
        });
    }

    /**
     * Get all room type IDs affected by this booking
     * Includes both primary loai_phong_id and all room types in room_types JSON
     *
     * @param DatPhong $booking
     * @return array
     */
    protected static function getAffectedRoomTypeIds($booking): array
    {
        $loaiPhongIds = [];

        // Add primary loai_phong_id
        if ($booking->loai_phong_id) {
            $loaiPhongIds[] = $booking->loai_phong_id;
        }

        // Add all room types from room_types JSON
        $roomTypes = $booking->getRoomTypes();
        foreach ($roomTypes as $roomType) {
            if (isset($roomType['loai_phong_id'])) {
                $loaiPhongIds[] = $roomType['loai_phong_id'];
            }
        }

        return array_unique($loaiPhongIds);
    }

    /**
     * Recalculate so_luong_trong for a room type based on actual room status
     * Logic chuẩn hóa: Chỉ đếm phòng có trang_thai = 'trong'
     *
     * @param int $loaiPhongId
     * @return void
     */
    protected static function recalculateSoLuongTrong(int $loaiPhongId): void
    {
        $trongCount = \App\Models\Phong::where('loai_phong_id', $loaiPhongId)
            ->where('trang_thai', 'trong')
            ->count();

        LoaiPhong::where('id', $loaiPhongId)
            ->update(['so_luong_trong' => $trongCount]);
    }
    public function getTrangThaiLabelAttribute()
    {
        $map = [
            'cho_xac_nhan' => 'Chờ xác nhận',
            'da_xac_nhan' => 'Đã xác nhận',
            'da_huy' => 'Đã hủy',
            'da_tra' => 'Đã trả',
            'tu_choi' => 'Từ chối',
            'thanh_toan_that_bai' => 'Thanh toán thất bại',
            'da_chong' => 'Đã chồng phòng',
        ];

        return $map[$this->trang_thai] ?? ucfirst(str_replace('_', ' ', $this->trang_thai));
    }
    public function yeuCauDoiPhongs()
    {
        return $this->hasMany(ModelsYeuCauDoiPhong::class, 'dat_phong_id');
    }
}
