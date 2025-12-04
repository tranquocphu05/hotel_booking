<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Voucher;
use App\Models\DatPhong;
use App\Models\Phong;
use App\Models\LoaiPhong;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Mail\BookingConfirmed;
use App\Mail\InvoicePaid;
use App\Mail\AdminBookingEvent;
use App\Models\BookingService;
use App\Models\Service;
use App\Models\ThanhToan;

class DatPhongController extends Controller
{
    public function index(Request $request)
    {
        // Lấy tất cả đơn đặt phòng và sắp xếp theo ngày đặt mới nhất
        $query = DatPhong::with(['loaiPhong', 'voucher', 'invoice'])
            ->orderBy('ngay_dat', 'desc');

        // Áp dụng các bộ lọc
        if ($request->search) {
            $query->whereHas('loaiPhong', function ($q) use ($request) {
                $q->where('ten_loai', 'like', '%' . $request->search . '%');
            })
                ->orWhere('username', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        if ($request->status) {
            $query->where('trang_thai', $request->status);
        }

        if ($request->from_date) {
            $query->whereDate('ngay_dat', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('ngay_dat', '<=', $request->to_date);
        }

        // Lấy thống kê tổng số đặt phòng theo từng trạng thái (tất cả các đơn)
        $bookingCounts = [
            'cho_xac_nhan' => DatPhong::where('trang_thai', 'cho_xac_nhan')->count(),
            'da_xac_nhan' => DatPhong::where('trang_thai', 'da_xac_nhan')->count(),
            'da_huy' => DatPhong::where('trang_thai', 'da_huy')->count(),
            'da_tra' => DatPhong::where('trang_thai', 'da_tra')->count(),
        ];

        // Phân trang, mỗi trang 5 đơn
        $bookings = $query->paginate(5);

        if ($request->ajax()) {
            return view('admin.dat_phong._bookings_list', compact('bookings'))->render();
        }

        return view('admin.dat_phong.index', compact('bookings', 'bookingCounts'));
    }

    public function showCancelForm($id)
    {
        $booking = DatPhong::with(['loaiPhong'])->findOrFail($id);

        // Kiểm tra nếu không phải trạng thái chờ xác nhận thì không cho hủy
        if ($booking->trang_thai !== 'cho_xac_nhan') {
            return redirect()->route('admin.dat_phong.index')
                ->with('error', 'Chỉ có thể hủy đơn đặt phòng đang chờ xác nhận');
        }

        return view('admin.dat_phong.cancel', compact('booking'));
    }

    public function submitCancel(Request $request, $id)
    {
        $booking = DatPhong::findOrFail($id);

        // Validate
        $request->validate([
            'ly_do' => 'required|in:thay_doi_lich_trinh,thay_doi_ke_hoach,khong_phu_hop,ly_do_khac'
        ], [
            'ly_do.required' => 'Vui lòng chọn lý do hủy đặt phòng',
            'ly_do.in' => 'Lý do không hợp lệ'
        ]);

        // Cập nhật trạng thái và lý do hủy, đồng thời giải phóng phòng
        DB::transaction(function () use ($booking, $request) {
            // Load relationships
            $booking->load(['phong', 'loaiPhong']);

            // Update booking status
            $booking->update([
                'trang_thai' => 'da_huy',
                'ngay_huy' => now()
            ]);

            // Free up room via phong_id (legacy)
            if ($booking->phong_id && $booking->phong) {
                // Kiểm tra xem phòng có đang được đặt cho booking khác không
                $hasOtherBooking = DatPhong::where('id', '!=', $booking->id)
                    ->where(function ($q) use ($booking) {
                        $q->where('phong_id', $booking->phong_id)
                            ->orWhereContainsPhongId($booking->phong_id);
                    })
                    ->where(function ($q) use ($booking) {
                        $q->where('ngay_tra', '>', $booking->ngay_nhan)
                            ->where('ngay_nhan', '<', $booking->ngay_tra);
                    })
                    ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
                    ->exists();

                if (!$hasOtherBooking) {
                    $booking->phong->update(['trang_thai' => 'trong']);
                }
            }

            // Free up rooms via phong_ids JSON
            $phongIds = $booking->getPhongIds();
            foreach ($phongIds as $phongId) {
                $phong = Phong::find($phongId);
                if ($phong) {
                    // Kiểm tra xem phòng có đang được đặt cho booking khác không
                    $hasOtherBooking = DatPhong::where('id', '!=', $booking->id)
                        ->where(function ($q) use ($phongId) {
                            $q->where('phong_id', $phongId)
                                ->orWhereContainsPhongId($phongId);
                        })
                        ->where(function ($q) use ($booking) {
                            $q->where('ngay_tra', '>', $booking->ngay_nhan)
                                ->where('ngay_nhan', '<', $booking->ngay_tra);
                        })
                        ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
                        ->exists();

                    if (!$hasOtherBooking) {
                        $phong->update(['trang_thai' => 'trong']);
                    }
                }
            }

            // Clear phong_ids after freeing rooms
            $booking->phong_ids = [];
            $booking->save();

            // Update so_luong_trong in loai_phong
            if ($booking->loaiPhong) {
                $trongCount = Phong::where('loai_phong_id', $booking->loai_phong_id)
                    ->where('trang_thai', 'trong')
                    ->count();
                $booking->loaiPhong->update(['so_luong_trong' => $trongCount]);
            }
        });

        return redirect()->route('admin.dat_phong.index')
            ->with('success', 'Đã hủy đặt phòng thành công');
    }

    public function show($id)
    {
        $booking = DatPhong::with(['loaiPhong', 'voucher', 'phong', 'services.service'])->findOrFail($id);

        // Lấy danh sách phòng trống của loại phòng này cho khoảng thời gian booking
        // Loại trừ các phòng đã được gán cho booking này
        $availableRooms = null;
        if ($booking->loai_phong_id && $booking->ngay_nhan && $booking->ngay_tra) {
            $assignedPhongIds = $booking->getPhongIds();
            $availableRooms = Phong::findAvailableRooms(
                $booking->loai_phong_id,
                $booking->ngay_nhan,
                $booking->ngay_tra,
                20 // Lấy tối đa 20 phòng để hiển thị
            )->reject(function ($phong) use ($assignedPhongIds) {
                return in_array($phong->id, $assignedPhongIds);
            })->values();
        }

        // Tính chính sách hủy nếu booking đã xác nhận
        $cancellationPolicy = null;
        if ($booking->trang_thai === 'da_xac_nhan') {
            $cancellationPolicy = $this->calculateCancellationPolicy($booking);
        }

        // Lấy danh sách dịch vụ đang hoạt động
        $services = \App\Models\Service::where('status', 'hoat_dong')->get();
        $bookingServices = $booking->services->sortBy('used_at')->values();

        // Prepare available rooms grouped by loai_phong_id for show view (for assigning missing rooms)
        $availableRoomsByLoaiPhong = [];
        $roomTypes = $booking->getRoomTypes();
        if (is_array($roomTypes) && !empty($roomTypes) && $booking->ngay_nhan && $booking->ngay_tra) {
            $assignedPhongIds = $booking->getPhongIds();
            foreach ($roomTypes as $rt) {
                $lid = $rt['loai_phong_id'] ?? null;
                if (!$lid) continue;

                $rooms = Phong::findAvailableRooms(
                    $lid,
                    $booking->ngay_nhan,
                    $booking->ngay_tra,
                    999,
                    $booking->id
                )->values();

                // include currently assigned rooms for this type so admin can keep them
                $assignedForThis = [];
                $assignedIdsForThis = array_filter(array_values(array_map('intval', array_filter($assignedPhongIds ?? [], function($v){return $v; }))));
                if (!empty($assignedIdsForThis)) {
                    $assignedForThis = Phong::whereIn('id', $assignedIdsForThis)
                        ->where('loai_phong_id', $lid)
                        ->get()
                        ->values();
                }

                $merged = collect($assignedForThis)->merge($rooms)->unique('id')->values();
                $availableRoomsByLoaiPhong[$lid] = $merged;
            }
        }

        return view('admin.dat_phong.show', compact(
            'booking',
            'availableRooms',
            'availableRoomsByLoaiPhong',
            'cancellationPolicy',
            'services',
            'bookingServices'
        ));
    }

    public function edit($id)
    {
        $booking = DatPhong::with(['loaiPhong', 'voucher', 'user', 'phong'])->findOrFail($id);

        // Lấy danh sách loại phòng để hiển thị trong form sửa
        $loaiPhongs = LoaiPhong::where('trang_thai', 'hoat_dong')->get();

        // Chỉ cho phép sửa đơn đang chờ xác nhận
        if ($booking->trang_thai !== 'cho_xac_nhan') {
            return redirect()->route('admin.dat_phong.show', $booking->id)
                ->with('error', 'Chỉ có thể sửa đơn đặt phòng đang chờ xác nhận');
        }

        // Tự động điền CCCD từ user nếu booking chưa có CCCD
        if (!$booking->cccd && $booking->user && $booking->user->cccd) {
            $booking->cccd = $booking->user->cccd;
        }

        // Lấy danh sách phòng trống của loại phòng này cho khoảng thời gian booking
        // Bao gồm cả phòng hiện tại đã được gán (để có thể giữ nguyên hoặc đổi)
        $availableRooms = null;
        if ($booking->loai_phong_id && $booking->ngay_nhan && $booking->ngay_tra) {
            $availableRooms = Phong::findAvailableRooms(
                $booking->loai_phong_id,
                $booking->ngay_nhan,
                $booking->ngay_tra,
                20 // Lấy tối đa 20 phòng để hiển thị
            )->values();

            // Loại trừ các phòng đã được gán cho booking này
            $assignedPhongIds = $booking->getPhongIds();
            $availableRooms = $availableRooms->reject(function ($phong) use ($assignedPhongIds) {
                return in_array($phong->id, $assignedPhongIds);
            })->values();
        }

        // Lấy danh sách dịch vụ đang hoạt động
        $services = \App\Models\Service::where('status', 'hoat_dong')->get();
        
        // Lấy dịch vụ đã sử dụng của booking này (với quan hệ service và phong)
        $bookingServices = \App\Models\BookingService::with(['service','phong'])
            ->where('dat_phong_id', $booking->id)
            ->get();

        // Build a JS-friendly structure grouped by service_id and date
        $bookingServicesServer = [];
        $roomMap = []; // map room_id => room label (so_phong/ten_phong)

        foreach ($bookingServices as $bs) {
            $svcId = $bs->service_id;
            if (!isset($bookingServicesServer[$svcId])) {
                $bookingServicesServer[$svcId] = [
                    'service' => $bs->service ? $bs->service->only(['id','name','price','unit']) : null,
                    'entries' => [], // each entry: ['ngay'=>'Y-m-d','so_luong'=>int,'phong_ids'=>[]]
                ];
            }

            $ngay = $bs->used_at ? (is_string($bs->used_at) ? date('Y-m-d', strtotime($bs->used_at)) : $bs->used_at->format('Y-m-d')) : ($booking->ngay_nhan ? date('Y-m-d', strtotime($booking->ngay_nhan)) : null);

            // Each BookingService record is 1 entry (no merging by date/phong)
            // If phong_id present => specific room, otherwise applies to all
            $phongIds = $bs->phong_id ? [$bs->phong_id] : [];
            
            $bookingServicesServer[$svcId]['entries'][] = [
                'ngay' => $ngay,
                'so_luong' => $bs->quantity ?? 1,  // so_luong from record (usually 1 for specific-mode)
                'phong_ids' => $phongIds,
            ];

            if ($bs->phong) {
                $roomMap[$bs->phong->id] = $bs->phong->so_phong ?? $bs->phong->ten_phong ?? $bs->phong->id;
            }
            
            // DEBUG
            if (config('app.debug')) {
                Log::debug("BookingService: id={$bs->id}, service_id={$svcId}, phong_id={$bs->phong_id}, used_at={$bs->used_at}, ngay={$ngay}, phongIds=" . json_encode($phongIds));
            }
        }

        // Ensure roomMap contains labels for all currently assigned rooms
        $assignedIdsForMapping = $booking->getPhongIds();
        if (!empty($assignedIdsForMapping)) {
            $missing = array_diff($assignedIdsForMapping, array_keys($roomMap));
            if (!empty($missing)) {
                $roomsForMap = Phong::whereIn('id', $missing)->get();
                foreach ($roomsForMap as $r) {
                    if (!isset($roomMap[$r->id])) {
                        $roomMap[$r->id] = $r->so_phong ?? $r->ten_phong ?? $r->id;
                    }
                }
            }
        }

        // Assigned room ids for this booking (phong_ids JSON or legacy phong_id)
        $assignedPhongIds = $booking->phong_ids ?? $booking->getPhongIds();

        // Normalize room_types: merge entries with same loai_phong_id to avoid duplicated counts
        $normalizedRoomTypes = [];
        if (is_array($booking->room_types)) {
            $map = [];
            foreach ($booking->room_types as $rt) {
                if (!isset($rt['loai_phong_id'])) continue;
                $lid = $rt['loai_phong_id'];
                $soLuong = isset($rt['so_luong']) ? intval($rt['so_luong']) : 0;
                $gia = isset($rt['gia_rieng']) ? $rt['gia_rieng'] : 0;
                $phongs = isset($rt['phong_ids']) && is_array($rt['phong_ids']) ? $rt['phong_ids'] : [];

                if (!isset($map[$lid])) {
                    $map[$lid] = [
                        'loai_phong_id' => $lid,
                        'so_luong' => $soLuong,
                        'gia_rieng' => $gia,
                        'phong_ids' => array_values(array_unique($phongs)),
                    ];
                } else {
                    // sum quantities, prefer non-zero gia_rieng if available, and merge phong_ids
                    $map[$lid]['so_luong'] += $soLuong;
                    if (empty($map[$lid]['gia_rieng']) && !empty($gia)) $map[$lid]['gia_rieng'] = $gia;
                    $map[$lid]['phong_ids'] = array_values(array_unique(array_merge($map[$lid]['phong_ids'], $phongs)));
                }
            }
            // convert map back to indexed array preserving original order of appearance
            $seen = [];
            foreach ($booking->room_types as $rt) {
                if (!isset($rt['loai_phong_id'])) continue;
                $lid = $rt['loai_phong_id'];
                if (isset($map[$lid]) && !in_array($lid, $seen)) {
                    $normalizedRoomTypes[] = $map[$lid];
                    $seen[] = $lid;
                }
            }
            // fallback: if something went wrong, take map values
            if (empty($normalizedRoomTypes)) $normalizedRoomTypes = array_values($map);
            // overwrite booking->room_types so view uses normalized data
            $booking->room_types = $normalizedRoomTypes;
        }

        // Chuẩn bị danh sách phòng đang chọn theo loại phòng (cho JS)
        $selectedRoomsByLoaiPhong = [];
        if (is_array($booking->room_types)) {
            foreach ($booking->room_types as $rt) {
                if (isset($rt['loai_phong_id'])) {
                    $selectedRoomsByLoaiPhong[$rt['loai_phong_id']] = $rt['phong_ids'] ?? [];
                }
            }
        }

        // Prepare available rooms grouped by loai_phong_id for server-side rendering
        $availableRoomsByLoaiPhong = [];
        if (is_array($booking->room_types)) {
            foreach ($booking->room_types as $rt) {
                $lid = $rt['loai_phong_id'] ?? null;
                if (!$lid) continue;
                // fetch available rooms for this room type excluding rooms already assigned to this booking
                $rooms = Phong::findAvailableRooms(
                    $lid,
                    $booking->ngay_nhan,
                    $booking->ngay_tra,
                    999,
                    $booking->id
                )->values();

                // include currently assigned rooms for this type so admin can keep them
                $assignedForThis = [];
                if (isset($selectedRoomsByLoaiPhong[$lid]) && is_array($selectedRoomsByLoaiPhong[$lid])) {
                    $assignedForThis = Phong::whereIn('id', $selectedRoomsByLoaiPhong[$lid])->get()->values();
                }

                // merge assigned rooms first then available rooms (unique)
                $merged = $assignedForThis->merge($rooms)->unique('id')->values();
                $availableRoomsByLoaiPhong[$lid] = $merged;
            }
        }

        // Lấy danh sách vouchers để hiển thị.
        // Only include vouchers that make sense for this booking's dates:
        // - booking.checkin (ngay_nhan) must fall within voucher.ngay_bat_dau..voucher.ngay_ket_thuc
        // - booking.checkout (ngay_tra) must be after voucher.ngay_bat_dau
        // Always include the booking's currently assigned voucher (if any) so admin can keep it.
        $allVouchers = \App\Models\Voucher::orderBy('id', 'desc')->get();
        if ($booking->ngay_nhan && $booking->ngay_tra) {
            $checkin = Carbon::parse($booking->ngay_nhan)->startOfDay();
            $checkout = Carbon::parse($booking->ngay_tra)->startOfDay();

            $vouchers = $allVouchers->filter(function ($v) use ($checkin, $booking) {
                // Always keep the booking's currently assigned voucher so admin can keep it
                if (!empty($booking->voucher_id) && $booking->voucher_id == $v->id) {
                    return true;
                }

                // Exclude vouchers that are exhausted or inactive
                if (isset($v->so_luong) && intval($v->so_luong) <= 0) return false;
                if (isset($v->trang_thai) && $v->trang_thai !== 'con_han') return false;

                if (empty($v->ngay_bat_dau) || empty($v->ngay_ket_thuc)) {
                    return false;
                }

                try {
                    $vStart = Carbon::parse($v->ngay_bat_dau)->startOfDay();
                    $vEnd = Carbon::parse($v->ngay_ket_thuc)->startOfDay();
                } catch (\Exception $e) {
                    return false;
                }

                // Condition: checkin in [vStart, vEnd]
                return $checkin->between($vStart, $vEnd);
            })->values();
        } else {
            // If booking dates missing, keep original full list (admin can still pick)
            $vouchers = $allVouchers;
        }

        return view('admin.dat_phong.edit', compact(
            'booking',
            'loaiPhongs',
            'availableRooms',
            'availableRoomsByLoaiPhong',
            'services',
            'bookingServices',
            'bookingServicesServer',
            'roomMap',
            'assignedPhongIds',
            'selectedRoomsByLoaiPhong',
            'vouchers'
        ));
    }

    /**
     * Tính toán chính sách hủy phòng
     */
    private function calculateCancellationPolicy($booking)
    {
        $now = Carbon::now();
        $checkinDate = Carbon::parse($booking->ngay_nhan);
        $daysUntilCheckin = $now->diffInDays($checkinDate, false);
        
        $policy = [
            'can_cancel' => true,
            'refund_percentage' => 0,
            'refund_amount' => 0,
            'penalty_amount' => 0,
            'message' => '',
            'days_until_checkin' => $daysUntilCheckin,
        ];

        // Nếu đã quá ngày nhận phòng, không cho hủy (khách đã check-in)
        if ($daysUntilCheckin < 0) {
            $policy['can_cancel'] = false;
            $policy['refund_percentage'] = 0;
            $policy['refund_amount'] = 0;
            $policy['penalty_amount'] = $booking->tong_tien;
            $policy['message'] = 'Không thể hủy sau ngày nhận phòng (khách đã check-in)';
            return $policy;
        }

        // Chính sách hoàn tiền theo số ngày trước khi nhận phòng
        if ($daysUntilCheckin >= 7) {
            // Hủy trước 7 ngày: Hoàn 100%
            $policy['refund_percentage'] = 100;
            $policy['message'] = 'Hoàn 100% tiền đã thanh toán';
        } elseif ($daysUntilCheckin >= 3) {
            // Hủy trước 3-6 ngày: Hoàn 50%
            $policy['refund_percentage'] = 50;
            $policy['message'] = 'Hoàn 50% tiền đã thanh toán (phí hủy 50%)';
        } elseif ($daysUntilCheckin >= 1) {
            // Hủy trước 1-2 ngày: Hoàn 25%
            $policy['refund_percentage'] = 25;
            $policy['message'] = 'Hoàn 25% tiền đã thanh toán (phí hủy 75%)';
        } else {
            // Hủy trong ngày: Không hoàn tiền
            $policy['refund_percentage'] = 0;
            $policy['message'] = 'Không hoàn tiền (hủy quá gần ngày nhận phòng)';
        }

        return $policy;
    }

    /**
     * Normalize an array of room_types: merge entries with same loai_phong_id.
     * Ensures 'so_luong' is summed and 'phong_ids' are merged uniquely.
     */
    private function normalizeRoomTypesArray(array $roomTypes)
    {
        $map = [];
        foreach ($roomTypes as $rt) {
            if (!isset($rt['loai_phong_id'])) continue;
            $lid = $rt['loai_phong_id'];
            $soLuong = isset($rt['so_luong']) ? intval($rt['so_luong']) : 0;
            $gia = isset($rt['gia_rieng']) ? $rt['gia_rieng'] : 0;
            $phongs = isset($rt['phong_ids']) && is_array($rt['phong_ids']) ? $rt['phong_ids'] : [];

            if (!isset($map[$lid])) {
                $map[$lid] = [
                    'loai_phong_id' => $lid,
                    'so_luong' => $soLuong,
                    'gia_rieng' => $gia,
                    'phong_ids' => array_values(array_unique($phongs)),
                ];
            } else {
                $map[$lid]['so_luong'] += $soLuong;
                if (empty($map[$lid]['gia_rieng']) && !empty($gia)) $map[$lid]['gia_rieng'] = $gia;
                $map[$lid]['phong_ids'] = array_values(array_unique(array_merge($map[$lid]['phong_ids'], $phongs)));
            }
        }

        // Preserve insertion order based on first appearance
        $normalized = [];
        $seen = [];
        foreach ($roomTypes as $rt) {
            if (!isset($rt['loai_phong_id'])) continue;
            $lid = $rt['loai_phong_id'];
            if (isset($map[$lid]) && !in_array($lid, $seen)) {
                $normalized[] = $map[$lid];
                $seen[] = $lid;
            }
        }
        if (empty($normalized)) $normalized = array_values($map);
        return $normalized;
    }

    public function update(Request $request, $id)
    {
        $booking = DatPhong::findOrFail($id);

        if ($booking->trang_thai !== 'cho_xac_nhan') {
            return redirect()->route('admin.dat_phong.show', $booking->id)
                ->with('error', 'Chỉ có thể sửa đơn đặt phòng đang chờ xác nhận');
        }

        // Normalize room_types: Laravel converts single array to string, so we need to handle both cases
        $rawRoomTypes = $request->input('room_types', []);
        if (is_string($rawRoomTypes)) {
            // Single room type case: convert string to array
            $rawRoomTypes = [$rawRoomTypes];
        } elseif (!is_array($rawRoomTypes)) {
            $rawRoomTypes = [];
        }
        
        // Rebuild room_types with proper structure (loai_phong_id and so_luong keys)
        $normalizedRoomTypes = [];
        foreach ($rawRoomTypes as $idx => $roomType) {
            $loaiPhongId = null;
            $soLuong = 1;
            
            if (is_array($roomType)) {
                $loaiPhongId = $roomType['loai_phong_id'] ?? null;
                $soLuong = isset($roomType['so_luong']) ? intval($roomType['so_luong']) : 1;
            } else {
                // String value case (single room type scenario)
                $loaiPhongId = $roomType;
            }
            
            if ($loaiPhongId) {
                $normalizedRoomTypes[] = [
                    'loai_phong_id' => intval($loaiPhongId),
                    'so_luong' => max(1, $soLuong),
                ];
            }
        }
        
        // Merge normalized data back into request for validation
        $request->merge(['room_types' => $normalizedRoomTypes]);

        // Validate room_types array
        $request->validate([
            'room_types' => 'required|array|min:1',
            'room_types.*.loai_phong_id' => 'required|exists:loai_phong,id',
            'room_types.*.so_luong' => 'required|integer|min:1|max:10',
            // note: do not require per-room 'gia_rieng' on update — use LoaiPhong prices
            'ngay_nhan' => 'required|date|after_or_equal:today',
            'ngay_tra' => 'required|date|after_or_equal:ngay_nhan',
            'so_nguoi' => 'required|integer|min:1',
            'username' => 'required|string|max:255|regex:/^[\p{L}\s]+$/u',
            'email' => 'required|email:rfc,dns|max:255',
            'sdt' => 'required|regex:/^0[0-9]{9}$/',
            'cccd' => 'required|regex:/^[0-9]{12}$/',
            'voucher' => 'nullable|string|exists:voucher,ma_voucher'
        ], [
            'room_types.required' => 'Vui lòng chọn ít nhất một loại phòng',
            'room_types.*.loai_phong_id.required' => 'Vui lòng chọn loại phòng',
            'room_types.*.loai_phong_id.exists' => 'Loại phòng không tồn tại',
            'room_types.*.so_luong.required' => 'Vui lòng nhập số lượng phòng',
            'room_types.*.so_luong.min' => 'Số lượng phòng phải lớn hơn 0',
            'room_types.*.so_luong.max' => 'Số lượng phòng không được vượt quá 10',
            'ngay_nhan.required' => 'Vui lòng chọn ngày nhận phòng',
            'ngay_nhan.after_or_equal' => 'Ngày nhận phòng phải từ hôm nay trở đi',
            'ngay_tra.required' => 'Vui lòng chọn ngày trả phòng',
            'ngay_tra.after_or_equal' => 'Ngày trả phòng phải sau hoặc bằng ngày nhận phòng',
            'so_nguoi.required' => 'Vui lòng nhập số người',
            'so_nguoi.min' => 'Số người phải lớn hơn 0',
            'username.required' => 'Vui lòng nhập họ tên',
            'username.regex' => 'Vui lòng nhập tên của bạn',
            'email.required' => 'Vui lòng nhập email',
            'email.email' => 'Email không hợp lệ',
            'sdt.required' => 'Vui lòng nhập số điện thoại',
            'sdt.regex' => 'Số điện thoại không đúng định dạng',
            'cccd.required' => 'Vui lòng nhập CCCD/CMND',
            'cccd.regex' => 'CCCD/CMND phải gồm 12 chữ số',
        ]);

        $roomTypes = $request->room_types;

        // Check for duplicate room types
        $loaiPhongIds = array_column($roomTypes, 'loai_phong_id');
        if (count($loaiPhongIds) !== count(array_unique($loaiPhongIds))) {
            return back()->withErrors(['room_types' => 'Không thể chọn trùng loại phòng.'])->withInput();
        }

        // Validate each room type availability
        // Lấy danh sách phòng đã gán để loại trừ khi kiểm tra availability
        $oldPhongIds = $booking->getPhongIds();

        foreach ($roomTypes as $roomType) {
            $loaiPhong = LoaiPhong::find($roomType['loai_phong_id']);
            if (!$loaiPhong || $loaiPhong->trang_thai !== 'hoat_dong') {
                return back()->withErrors(['room_types' => 'Loại phòng ' . ($loaiPhong->ten_loai ?? 'N/A') . ' không khả dụng.'])->withInput();
            }

            // Check availability for the date range (exclude current booking's rooms)
            // Pass the current booking id so the model can exclude rooms already assigned to this booking
            $availableCount = Phong::countAvailableRooms(
                $roomType['loai_phong_id'],
                $request->ngay_nhan,
                $request->ngay_tra,
                $booking->id
            );

            // Đếm số phòng cũ thuộc loại này và trong danh sách phòng đã gán
            $oldPhongsOfThisType = Phong::whereIn('id', $oldPhongIds)
                ->where('loai_phong_id', $roomType['loai_phong_id'])
                ->count();

            // Số phòng cần thiết sau khi trừ đi phòng cũ cùng loại (nếu có)
            $soLuongCanThem = max(0, $roomType['so_luong'] - $oldPhongsOfThisType);

            // Nếu cần thêm phòng, kiểm tra availability
            if ($soLuongCanThem > 0 && $availableCount < $soLuongCanThem) {
                return back()->withErrors([
                    'room_types' => 'Loại phòng ' . $loaiPhong->ten_loai . ' chỉ còn ' . $availableCount . ' phòng trống trong khoảng thời gian từ ' . date('d/m/Y', strtotime($request->ngay_nhan)) . ' đến ' . date('d/m/Y', strtotime($request->ngay_tra)) . '. Bạn cần thêm ' . $soLuongCanThem . ' phòng.'
                ])->withInput();
            }
        }

        // Calculate number of nights and total rooms and price using LoaiPhong prices
        $nights = Carbon::parse($request->ngay_nhan)->diffInDays(Carbon::parse($request->ngay_tra));
        $nights = max(1, $nights);

        $totalSoLuong = array_sum(array_column($roomTypes, 'so_luong'));
        $totalPrice = 0;
        // prepare room_types array to store (similar shape as store)
        $roomTypesArray = [];
        foreach ($roomTypes as $roomType) {
            $loaiPhong = LoaiPhong::find($roomType['loai_phong_id']);
            $unitPricePerNight = $loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban ?? 0;
            $roomTotal = $unitPricePerNight * $nights * $roomType['so_luong'];
            $totalPrice += $roomTotal;

            $roomTypesArray[] = [
                'loai_phong_id' => $roomType['loai_phong_id'],
                'so_luong' => $roomType['so_luong'],
                'gia_rieng' => $roomTotal,
            ];
        }

        // Get first room type for legacy support
        if (empty($roomTypes)) {
            return back()->withErrors(['room_types' => 'Vui lòng chọn ít nhất một loại phòng'])->withInput();
        }
        $firstLoaiPhongId = $roomTypes[0]['loai_phong_id'];

        // Tính tổng tiền dịch vụ (nếu có) từ input services_data và chuẩn hóa dữ liệu
        $servicesData = $request->input('services_data', []);
        $totalServicePrice = 0;
        $normalizedServices = [];
        if (is_array($servicesData) && !empty($servicesData)) {
            foreach ($servicesData as $svcId => $svcRow) {
                $service = Service::find($svcId);
                if (!$service) {
                    continue;
                }

                $entries = isset($svcRow['entries']) && is_array($svcRow['entries']) ? $svcRow['entries'] : [];
                // Fallback: nếu không có entries nhưng có tổng số lượng, tạo 1 entry mặc định theo ngày nhận phòng
                if (empty($entries) && isset($svcRow['so_luong']) && intval($svcRow['so_luong']) > 0) {
                    $entries = [
                        [
                            'ngay' => $request->ngay_nhan,
                            'so_luong' => intval($svcRow['so_luong']),
                        ],
                    ];
                }

                $cleanEntries = [];
                foreach ($entries as $entry) {
                    $day = $entry['ngay'] ?? null;
                    $qty = isset($entry['so_luong']) ? intval($entry['so_luong']) : 0;
                    if (!$day || $qty <= 0) {
                        continue;
                    }

                    // collect per-entry room ids if provided (support either entries[][phong_ids][] or entries[][phong_id])
                    $entryPhongIds = [];
                    if (isset($entry['phong_ids']) && is_array($entry['phong_ids'])) {
                        $entryPhongIds = array_filter($entry['phong_ids']);
                    } elseif (isset($entry['phong_id'])) {
                        // frontend may post a singular phong_id per entry (legacy / current sync behavior)
                        $entryPhongIds = array_filter([$entry['phong_id']]);
                    }

                    $cleanEntries[] = [
                        'ngay' => $day,
                        'so_luong' => $qty,
                        'phong_ids' => $entryPhongIds,
                    ];

                    // Tính tổng tiền: 
                    // Mỗi entry đại diện cho 1 lần sử dụng dịch vụ (1 checkbox trong specific-mode, hoặc 1 ngày trong global-mode)
                    // Nếu entry có phong_ids (specific-mode): mỗi phòng = 1 entry riêng => chỉ nhân qty × price
                    // Nếu entry không có phong_ids (global-mode): áp dụng cho tất cả phòng => nhân qty × price × tổng_phòng_booking
                    $priceMultiplier = 1; // Default: 1 entry = 1 use
                    if (empty($entryPhongIds)) {
                        // Global mode: entry không có specific phòng, áp dụng cho tất cả phòng
                        $priceMultiplier = $totalSoLuong;
                    }
                    // Nếu có $entryPhongIds (specific mode): mỗi entry = 1 phòng = 1 use, nên multiplier = 1
                    
                    $totalServicePrice += $qty * ($service->price ?? 0) * $priceMultiplier;
                }

                if (!empty($cleanEntries)) {
                    $normalizedServices[] = [
                        'service_id' => $service->id,
                        'unit_price' => $service->price ?? 0,
                        'entries' => $cleanEntries,
                    ];
                }
            }
        }

    // Calculate voucher discount on room subtotal only (match frontend logic)
    $voucherDiscount = 0;
    $requestVoucher = $request->input('voucher_clear_checkbox') ? null : $request->input('voucher');
    if ($requestVoucher) {
        // Only accept vouchers that are active and available (same checks as create/store path)
        $voucher = Voucher::where('ma_voucher', $requestVoucher)
            ->where('so_luong', '>', 0)
            ->where('trang_thai', 'con_han')
            ->whereDate('ngay_ket_thuc', '>=', now())
            ->first();

        if ($voucher) {
            $discountValue = floatval($voucher->gia_tri ?? 0);

            // Compute applicable total: if voucher targets a specific loai_phong_id,
            // sum only matching room types; otherwise use full room subtotal.
            $applicableTotal = 0;
            if (empty($voucher->loai_phong_id)) {
                $applicableTotal = $totalPrice;
            } else {
                foreach ($roomTypesArray as $rt) {
                    if (isset($rt['loai_phong_id']) && $rt['loai_phong_id'] == $voucher->loai_phong_id) {
                        $applicableTotal += $rt['gia_rieng'];
                    }
                }
            }

            if ($applicableTotal > 0 && $discountValue > 0) {
                if ($discountValue <= 100) {
                    // Percentage discount
                    $voucherDiscount = intval(round($applicableTotal * ($discountValue / 100)));
                } else {
                    // Fixed amount discount (cap at applicable total)
                    $voucherDiscount = intval(min(round($discountValue), $applicableTotal));
                }
            }
        }
    }

    // Tổng cuối cùng bao gồm tiền phòng + tiền dịch vụ - giảm giá voucher
    $finalTotal = max(0, $totalPrice + $totalServicePrice - $voucherDiscount);

        // Support admin-selected specific rooms per room type
        $requestedRooms = $request->input('rooms', []);

        // Update booking và gán lại phòng trong transaction
    DB::transaction(function () use ($booking, $request, $roomTypes, $roomTypesArray, $totalSoLuong, $firstLoaiPhongId, $oldPhongIds, $servicesData, $finalTotal, $totalPrice, $totalServicePrice, $requestedRooms, $voucherDiscount) {
            // 1. Giải phóng tất cả phòng cũ (set về 'trong' nếu không có booking khác)
            foreach ($oldPhongIds as $phongId) {
                $phong = Phong::find($phongId);
                if ($phong) {
                    // Kiểm tra xem phòng có đang được đặt cho booking khác không
                    $hasOtherBooking = DatPhong::where('id', '!=', $booking->id)
                        ->where(function ($q) use ($phongId) {
                            $q->whereContainsPhongId($phongId);
                        })
                        ->where(function ($q) use ($request) {
                            $q->where('ngay_tra', '>', $request->ngay_nhan)
                                ->where('ngay_nhan', '<', $request->ngay_tra);
                        })
                        ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
                        ->exists();

                    if (!$hasOtherBooking) {
                        $phong->update(['trang_thai' => 'trong']);
                    }
                }
            }

            // 2. Gán lại phòng mới dựa trên room_types mới
            $newPhongIds = [];
            foreach ($roomTypes as $roomType) {
                $soLuongCan = $roomType['so_luong'];
                $loaiId = $roomType['loai_phong_id'];

                // If admin provided explicit room_ids for this type, prefer them (validate & lock)
                $selectedForType = isset($requestedRooms[$loaiId]) && isset($requestedRooms[$loaiId]['phong_ids']) && is_array($requestedRooms[$loaiId]['phong_ids'])
                    ? array_values(array_filter($requestedRooms[$loaiId]['phong_ids']))
                    : null;

                if (is_array($selectedForType) && count($selectedForType) > 0) {
                    // If admin selected exact number equal to requested quantity, use them; otherwise validate up to needed
                    $toTake = min(count($selectedForType), $soLuongCan);
                    $countAdded = 0;
                    foreach (array_slice($selectedForType, 0, $toTake) as $phId) {
                        $ph = Phong::lockForUpdate()->find($phId);
                        if (!$ph) continue;
                        if ($ph->loai_phong_id != $loaiId) continue;
                        if (!$ph->isAvailableInPeriod($request->ngay_nhan, $request->ngay_tra, $booking->id)) continue;
                        if (!in_array($ph->id, $newPhongIds)) {
                            $newPhongIds[] = $ph->id;
                            $countAdded++;
                        }
                        if ($countAdded >= $soLuongCan) break;
                    }

                    // If admin selected fewer than needed, fall back to keep-old + auto-assign for remaining
                    if ($countAdded >= $soLuongCan) {
                        continue;
                    }
                    // else we will continue to keep-old + auto-assign for remaining quantity
                }

                // Ưu tiên giữ lại phòng cũ nếu cùng loại và còn available
                $oldPhongsOfThisType = Phong::whereIn('id', $oldPhongIds)
                    ->where('loai_phong_id', $loaiId)
                    ->get()
                    ->filter(function ($phong) use ($request, $booking) {
                        return $phong->isAvailableInPeriod($request->ngay_nhan, $request->ngay_tra, $booking->id);
                    })
                    ->take($soLuongCan);

                $keptCount = $oldPhongsOfThisType->count();
                foreach ($oldPhongsOfThisType as $phong) {
                    if (!in_array($phong->id, $newPhongIds)) $newPhongIds[] = $phong->id;
                }

                // Nếu cần thêm phòng, tìm phòng mới
                if ($keptCount < $soLuongCan) {
                    $soLuongCanThem = $soLuongCan - $keptCount;
                    $availableRooms = Phong::findAvailableRooms(
                        $loaiId,
                        $request->ngay_nhan,
                        $request->ngay_tra,
                        $soLuongCanThem,
                        $booking->id
                    )->reject(function ($phong) use ($newPhongIds) {
                        return in_array($phong->id, $newPhongIds);
                    });

                    foreach ($availableRooms as $phong) {
                        // Lock phòng trước khi gán
                        $phongLocked = Phong::lockForUpdate()->find($phong->id);
                        if ($phongLocked && $phongLocked->isAvailableInPeriod($request->ngay_nhan, $request->ngay_tra, $booking->id)) {
                            if (!in_array($phongLocked->id, $newPhongIds)) $newPhongIds[] = $phongLocked->id;
                        }
                    }
                }
            }

            // 3. Update booking với thông tin mới (bao gồm tổng tiền đã cộng dịch vụ)
            
            // Handle voucher: check if voucher is selected or should be cleared
            $voucherId = null;
            if ($request->input('voucher_clear_checkbox')) {
                // Admin checked the "clear voucher" checkbox, so set voucher_id to NULL
                $voucherId = null;
            } elseif ($request->voucher) {
                // Admin selected a voucher
                $voucher = Voucher::where('ma_voucher', $request->voucher)->first();
                $voucherId = $voucher ? $voucher->id : null;
            }
            
            $bookingData = [
                'loai_phong_id' => $firstLoaiPhongId, // Legacy support
                'room_types' => $this->normalizeRoomTypesArray($roomTypesArray), // Store computed room types (use LoaiPhong prices)
                'so_luong_da_dat' => $totalSoLuong,
                'trang_thai' => $request->trang_thai ?? $booking->trang_thai,
                'ngay_nhan' => $request->ngay_nhan,
                'ngay_tra' => $request->ngay_tra,
                'so_nguoi' => $request->so_nguoi,
                'username' => $request->username,
                'email' => $request->email,
                'sdt' => $request->sdt,
                'cccd' => $request->cccd,
                'phong_ids' => $newPhongIds, // Cập nhật danh sách phòng mới
                'tong_tien' => $finalTotal,
                'voucher_id' => $voucherId,
            ];

            if (Schema::hasColumn('dat_phong', 'tien_phong')) {
                $bookingData['tien_phong'] = $totalPrice;
            }
            if (Schema::hasColumn('dat_phong', 'tien_dich_vu')) {
                $bookingData['tien_dich_vu'] = $totalServicePrice;
            }

            $booking->update($bookingData);

            // 4. Lưu lại các dịch vụ booking (xóa service cũ và ghi mới)
            // Mỗi entry (ngày) = 1 BookingService record
            // Hỗ trợ cả dịch vụ áp dụng cho tất cả phòng (phong_id = NULL)
            // và dịch vụ riêng cho phòng cụ thể (phong_id = room_id)
            
            // Xóa hoàn toàn tất cả BookingService cũ để tạo lại từ form mới (đảm bảo consistency)
            \App\Models\BookingService::where('dat_phong_id', $booking->id)->delete();
            
            if (is_array($servicesData) && !empty($servicesData)) {
                foreach ($servicesData as $svcId => $svcRow) {
                    $service = Service::find($svcId);
                    if (!$service) continue;
                    
                    // Kiểm tra phòng riêng (phong_ids) hay áp dụng cho tất cả
                    $phongIds = isset($svcRow['phong_ids']) && is_array($svcRow['phong_ids']) 
                        ? array_filter($svcRow['phong_ids']) 
                        : [];
                    
                    // Lấy các entries (mỗi ngày)
                    $entries = isset($svcRow['entries']) && is_array($svcRow['entries']) ? $svcRow['entries'] : [];
                    foreach ($entries as $entry) {
                        $ngay = isset($entry['ngay']) ? $entry['ngay'] : '';
                        $qty = isset($entry['so_luong']) ? intval($entry['so_luong']) : 0;
                        if (!$ngay || $qty <= 0) continue;

                        // support either entries[][phong_ids] (array) or entries[][phong_id] (singular hidden input)
                        $entryPhongIds = [];
                        if (isset($entry['phong_ids']) && is_array($entry['phong_ids'])) {
                            $entryPhongIds = array_filter($entry['phong_ids']);
                        } elseif (isset($entry['phong_id'])) {
                            $entryPhongIds = array_filter([$entry['phong_id']]);
                        }
                        $usePhongIds = !empty($entryPhongIds) ? $entryPhongIds : $phongIds;
                        
                        // Check if entry was originally for specific rooms or global
                        $wasSpecificRooms = !empty($usePhongIds);
                        
                        // Filter: only keep phong_ids that are still in $newPhongIds (remove deleted rooms)
                        if (!empty($usePhongIds)) {
                            $usePhongIds = array_intersect($usePhongIds, $newPhongIds);
                        }

                        // If originally specific rooms but now all rooms are deleted -> skip (don't create)
                        if ($wasSpecificRooms && empty($usePhongIds)) {
                            continue; // Skip this entry, don't create it
                        }
                        
                        if (empty($usePhongIds)) {
                            // Originally global or no specific rooms -> create per-room records when we have room ids
                            if (!empty($newPhongIds)) {
                                foreach ($newPhongIds as $phongId) {
                                    \App\Models\BookingService::create([
                                        'dat_phong_id' => $booking->id,
                                        'service_id' => $service->id,
                                        'quantity' => $qty,
                                        'unit_price' => $service->price,
                                        'used_at' => $ngay,
                                        'phong_id' => $phongId, // create one record per room
                                    ]);
                                }
                            } else {
                                // Fallback: no rooms available/assigned -> keep aggregated record
                                \App\Models\BookingService::create([
                                    'dat_phong_id' => $booking->id,
                                    'service_id' => $service->id,
                                    'quantity' => $qty,
                                    'unit_price' => $service->price,
                                    'used_at' => $ngay,
                                    'phong_id' => null,
                                ]);
                            }
                        } else {
                            // Tạo record riêng cho từng phòng được chỉ định
                            foreach ($usePhongIds as $phongId) {
                                \App\Models\BookingService::create([
                                    'dat_phong_id' => $booking->id,
                                    'service_id' => $service->id,
                                    'quantity' => $qty,
                                    'unit_price' => $service->price,
                                    'used_at' => $ngay,
                                    'phong_id' => $phongId, // Phòng riêng
                                ]);
                            }
                        }
                    }
                }
            }

            // 5. Cập nhật phong_id (legacy support) nếu chỉ có 1 phòng
            if (count($newPhongIds) == 1) {
                $booking->update(['phong_id' => $newPhongIds[0]]);
            } else {
                $booking->update(['phong_id' => null]);
            }
            
            // 6. Tính toán lại tổng tiền dịch vụ từ các BookingService vừa tạo và update lại tong_tien
            $recalculatedServiceTotal = \App\Models\BookingService::where('dat_phong_id', $booking->id)
                ->sum(DB::raw('quantity * unit_price'));
            $recalculatedTotal = $totalPrice + $recalculatedServiceTotal;

            // Ensure voucher discount is applied to the final stored total (rooms minus voucher + services)
            $finalRecalculated = max(0, $recalculatedTotal - ($voucherDiscount ?? 0));

            $updateData = [
                'tong_tien' => $finalRecalculated,
            ];
            if (Schema::hasColumn('dat_phong', 'tien_phong')) {
                $updateData['tien_phong'] = $totalPrice;
            }
            if (Schema::hasColumn('dat_phong', 'tien_dich_vu')) {
                $updateData['tien_dich_vu'] = $recalculatedServiceTotal;
            }
            
            $booking->update($updateData);
        });

        return redirect()->route('admin.dat_phong.show', $booking->id)
            ->with('success', 'Cập nhật thông tin đặt phòng thành công');
    }

    public function assignRoom(Request $request, $id)
    {
        $booking = DatPhong::with(['loaiPhong', 'phong'])->findOrFail($id);

        // Kiểm tra booking có phải trạng thái cho phép gán phòng không
        if (!in_array($booking->trang_thai, ['cho_xac_nhan', 'da_xac_nhan'])) {
            return redirect()->route('admin.dat_phong.show', $booking->id)
                ->with('error', 'Chỉ có thể gán phòng cho booking đang chờ xác nhận hoặc đã xác nhận.');
        }

        // Accept either single 'phong_id' or multiple 'phong_ids[loai_id][]'
        $selected = [];
        if ($request->has('phong_ids') && is_array($request->phong_ids)) {
            foreach ($request->phong_ids as $loai => $arr) {
                if (is_array($arr)) {
                    foreach ($arr as $v) {
                        if ($v) $selected[] = intval($v);
                    }
                }
            }
        } elseif ($request->filled('phong_id')) {
            $selected[] = intval($request->input('phong_id'));
        }

        if (empty($selected)) {
            return redirect()->back()->withErrors(['phong_id' => 'Vui lòng chọn ít nhất một phòng'])->withInput();
        }

        $selected = array_values(array_unique($selected));

        $phongs = Phong::whereIn('id', $selected)->get()->keyBy('id');
        if (count($phongs) !== count($selected)) {
            return redirect()->back()->withErrors(['phong_id' => 'Một hoặc nhiều phòng không tồn tại'])->withInput();
        }

        // Kiểm tra phòng có thuộc loại phòng của booking không
        // Nếu booking có nhiều loại phòng (room_types), kiểm tra phòng có thuộc một trong các loại đó không
        $roomTypes = $booking->getRoomTypes();
        $allowedLoaiPhongIds = [];
        if (count($roomTypes) > 1) {
            $allowedLoaiPhongIds = array_column($roomTypes, 'loai_phong_id');
        } else {
            $allowedLoaiPhongIds = [$booking->loai_phong_id];
        }

        // Build needed counts per loai_phong
        $neededByLoai = [];
        foreach ($roomTypes as $rt) {
            $lid = $rt['loai_phong_id'] ?? null;
            $neededByLoai[$lid] = $rt['so_luong'] ?? 0;
        }

        $assignedPhongIds = $booking->getPhongIds();
        // count assigned per type
        $assignedCountByLoai = [];
        if (!empty($assignedPhongIds)) {
            $assignedPhongs = Phong::whereIn('id', $assignedPhongIds)->get();
            foreach ($assignedPhongs as $p) {
                $assignedCountByLoai[$p->loai_phong_id] = ($assignedCountByLoai[$p->loai_phong_id] ?? 0) + 1;
            }
        }

        // Validate selected rooms against limits and availability
        $byLoaiSelected = [];
        foreach ($selected as $sid) {
            $p = $phongs->get($sid);
            if (!$p) {
                return redirect()->back()->withErrors(['phong_id' => 'Phòng không tồn tại'])->withInput();
            }

            if (!in_array($p->loai_phong_id, $allowedLoaiPhongIds)) {
                return redirect()->back()->withErrors(['phong_id' => 'Phòng ' . ($p->so_phong ?? $p->id) . ' không thuộc loại phòng của booking này.'])->withInput();
            }

            if ($p->trang_thai === 'bao_tri') {
                return redirect()->back()->withErrors(['phong_id' => 'Phòng ' . ($p->so_phong ?? $p->id) . ' đang bảo trì'])->withInput();
            }

            // availability check
            if (!$p->isAvailableInPeriod($booking->ngay_nhan, $booking->ngay_tra, $booking->id)) {
                return redirect()->back()->withErrors(['phong_id' => 'Phòng ' . ($p->so_phong ?? $p->id) . ' không trống trong khoảng thời gian này'])->withInput();
            }

            $byLoaiSelected[$p->loai_phong_id] = ($byLoaiSelected[$p->loai_phong_id] ?? 0) + 1;
        }

        // Ensure per-type selection does not exceed remaining slots
        foreach ($byLoaiSelected as $loai => $countSel) {
            $already = $assignedCountByLoai[$loai] ?? 0;
            $needed = $neededByLoai[$loai] ?? 0;
            $remaining = max(0, $needed - $already);
            if ($countSel > $remaining) {
                return redirect()->back()->withErrors(['phong_id' => 'Không thể chọn quá ' . $remaining . ' phòng cho loại phòng ' . ($loai ?? '')])->withInput();
            }
        }

        // All validation passed — add rooms
        DB::transaction(function () use ($booking, $selected, $phongs) {
            $booking->refresh();
            $phongIds = $booking->getPhongIds();
            foreach ($selected as $sid) {
                if (!in_array($sid, $phongIds)) {
                    $phongIds[] = (int) $sid;
                }
            }
            $booking->phong_ids = $phongIds;
            $booking->save();

            // Update room status for confirmed bookings
            if ($booking->trang_thai === 'da_xac_nhan') {
                foreach ($selected as $sid) {
                    $p = $phongs->get($sid);
                    if ($p && $p->trang_thai === 'trong') {
                        $p->update(['trang_thai' => 'dang_thue']);
                    }
                }
            }

            // Update legacy phong_id if single
            $booking->refresh();
            $phongIds = $booking->getPhongIds();
            if (count($phongIds) == 1) {
                $booking->update(['phong_id' => $phongIds[0]]);
            } else {
                $booking->update(['phong_id' => null]);
            }
        });

        // Build a human-readable room label for the success message
        $roomNumbers = Phong::whereIn('id', $selected)->get()->pluck('so_phong')->filter()->all();
        $phongNumber = !empty($roomNumbers) ? implode(', ', $roomNumbers) : 'N/A';

        // Xác định route redirect dựa trên referer
        $referer = $request->headers->get('referer');
        $redirectRoute = str_contains($referer, '/edit')
            ? route('admin.dat_phong.edit', $booking->id)
            : route('admin.dat_phong.show', $booking->id);

        $booking->refresh();
        $assignedPhongIds = $booking->getPhongIds();
        $assignedCount = count($assignedPhongIds);
        $remainingCount = $booking->so_luong_da_dat - $assignedCount;

        $message = 'Gán phòng ' . $phongNumber . ' thành công!';
        if ($booking->so_luong_da_dat > 1) {
            $message .= ' Đã gán ' . $assignedCount . '/' . $booking->so_luong_da_dat . ' phòng';
            if ($remainingCount > 0) {
                $message .= ' (Còn thiếu ' . $remainingCount . ' phòng)';
            } else {
                $message .= ' (Đã gán đủ)';
            }
        }

        return redirect($redirectRoute)
            ->with('success', $message);
    }

    public function create(\Illuminate\Http\Request $request)
    {
        // Lấy danh sách loại phòng thay vì phòng cụ thể
        $loaiPhongs = LoaiPhong::where('trang_thai', 'hoat_dong')
            ->with([
                'phongs' => function ($q) {
                    $q->where('trang_thai', 'trong'); // chỉ lấy phòng sẵn sàng
                }
            ])
            ->get();
        ;
        $services = Service::where('status', 'hoat_dong')->get();

        // Lấy danh sách voucher còn hiệu lực: must be active, not exhausted,
        // and checkin (ngay_nhan) must fall within voucher.ngay_bat_dau..voucher.ngay_ket_thuc
        $checkinInput = $request->input('ngay_nhan');
        $allVouchers = Voucher::where('trang_thai', 'con_han')
            ->where('so_luong', '>', 0)
            ->whereDate('ngay_ket_thuc', '>=', now())
            ->orderBy('id', 'desc')
            ->get();

        if ($checkinInput) {
            try {
                $checkin = Carbon::parse($checkinInput)->startOfDay();
                $vouchers = $allVouchers->filter(function ($v) use ($checkin) {
                    if (empty($v->ngay_bat_dau) || empty($v->ngay_ket_thuc)) return false;
                    try {
                        $vStart = Carbon::parse($v->ngay_bat_dau)->startOfDay();
                        $vEnd = Carbon::parse($v->ngay_ket_thuc)->startOfDay();
                    } catch (\Exception $e) {
                        return false;
                    }
                    return $checkin->between($vStart, $vEnd);
                })->values();
            } catch (\Exception $e) {
                // If parsing fails, fall back to empty list
                $vouchers = collect();
            }
        } else {
            // If no checkin provided, show only vouchers that are active and not exhausted
            $vouchers = $allVouchers;
        }

        return view('admin.dat_phong.create', compact('loaiPhongs', 'vouchers', 'services'));
    }

    /**
     * API endpoint để lấy số phòng trống theo khoảng thời gian (AJAX)
     */
    public function getAvailableCount(Request $request)
    {
        // Accept either 'checkin'/'checkout' or legacy 'ngay_nhan'/'ngay_tra'
        $loaiPhongId = $request->input('loai_phong_id');
        $checkin = $request->input('checkin') ?? $request->input('ngay_nhan');
        $checkout = $request->input('checkout') ?? $request->input('ngay_tra');

        if (!$loaiPhongId || !$checkin || !$checkout) {
            return response()->json([
                'success' => false,
                'message' => 'Thiếu tham số: loai_phong_id, checkin/ngay_nhan và checkout/ngay_tra là bắt buộc',
            ], 422);
        }

        try {
            // Basic date validation
            $checkinDate = \Carbon\Carbon::parse($checkin);
            $checkoutDate = \Carbon\Carbon::parse($checkout);
            if ($checkoutDate <= $checkinDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ngày trả phải sau ngày nhận',
                ], 422);
            }

            // Allow client to pass booking_id (when editing) so we can exclude rooms
            $excludeBookingId = $request->input('booking_id');

            $availableCount = Phong::countAvailableRooms(
                $loaiPhongId,
                $checkinDate->toDateString(),
                $checkoutDate->toDateString(),
                $excludeBookingId
            );

            $response = [
                'success' => true,
                'available_count' => max(0, (int) $availableCount),
            ];

            // Nếu client yêu cầu danh sách phòng cụ thể, trả về luôn
            if ($request->input('include_rooms')) {
                $rooms = Phong::findAvailableRooms($loaiPhongId, $checkinDate, $checkoutDate, 999, $excludeBookingId);
                $response['rooms'] = $rooms->map(function($r){
                    return [
                        'id' => $r->id,
                        'so_phong' => $r->so_phong ?? null,
                        'ten_phong' => $r->ten_phong ?? null,
                    ];
                })->values();
            }

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể kiểm tra số phòng trống: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        // Validate room_types array first
        $request->validate([
            'room_types' => 'required|array|min:1',
            'room_types.*' => 'required|integer|exists:loai_phong,id',
            'ngay_nhan' => 'required|date|after_or_equal:today',
            'ngay_tra' => 'required|date|after:ngay_nhan',
            'so_nguoi' => 'required|integer|min:1',
            'username' => 'required|string|max:255|regex:/^[\p{L}\s]+$/u',
            'email' => 'required|email:rfc,dns|max:255',
            'sdt' => 'required|regex:/^0[0-9]{9}$/',
            'cccd' => 'required|regex:/^[0-9]{12}$/',
            'voucher' => 'nullable|string|exists:voucher,ma_voucher'
        ], [
            'room_types.required' => 'Vui lòng chọn ít nhất một loại phòng',
            'room_types.min' => 'Vui lòng chọn ít nhất một loại phòng',
            'room_types.*.exists' => 'Loại phòng không tồn tại',
            'ngay_nhan.required' => 'Vui lòng chọn ngày nhận phòng',
            'ngay_nhan.after_or_equal' => 'Ngày nhận phòng phải từ hôm nay trở đi',
            'ngay_tra.required' => 'Vui lòng chọn ngày trả phòng',
            'ngay_tra.after' => 'Ngày trả phòng phải sau ngày nhận phòng',
            'so_nguoi.required' => 'Vui lòng nhập số người',
            'so_nguoi.min' => 'Số người phải lớn hơn 0',
            'username.required' => 'Vui lòng nhập họ tên',
            'username.regex' => 'Vui lòng nhập tên của bạn',
            'email.required' => 'Vui lòng nhập email',
            'email.email' => 'Email không hợp lệ',
            'sdt.required' => 'Vui lòng nhập số điện thoại',
            'sdt.regex' => 'Số điện thoại không đúng định dạng (phải bắt đầu bằng 0 và có 10 chữ số)',
            'cccd.required' => 'Vui lòng nhập CCCD/CMND',
            'cccd.regex' => 'CCCD/CMND phải gồm 12 chữ số',
        ]);

        // Lọc chỉ lấy các loại phòng đã được chọn
        $selectedRoomTypes = $request->room_types ?? [];
        if (empty($selectedRoomTypes)) {
            return back()->withErrors(['room_types' => 'Vui lòng chọn ít nhất một loại phòng'])->withInput();
        }

        // Validate số lượng cho từng loại phòng đã chọn
        $errors = [];
        foreach ($selectedRoomTypes as $roomTypeId) {
            if (!isset($request->rooms[$roomTypeId])) {
                $errors["rooms.{$roomTypeId}.so_luong"] = "Vui lòng nhập số lượng cho loại phòng này";
                continue;
            }

            $room = $request->rooms[$roomTypeId];
            if (!isset($room['so_luong']) || $room['so_luong'] <= 0) {
                $errors["rooms.{$roomTypeId}.so_luong"] = "Số lượng phòng phải lớn hơn 0";
            }

            if (!isset($room['loai_phong_id']) || $room['loai_phong_id'] != $roomTypeId) {
                $errors["rooms.{$roomTypeId}.loai_phong_id"] = "Dữ liệu không hợp lệ";
            }
        }

        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

        $nights = Carbon::parse($request->ngay_nhan)->diffInDays(Carbon::parse($request->ngay_tra));
        $nights = max(1, $nights);

        // Validate each room type and check availability
        $totalPrice = 0;
        $roomDetails = [];
        $validationErrors = [];

        foreach ($selectedRoomTypes as $roomTypeId) {
            $room = $request->rooms[$roomTypeId];
            // Additional validation: check if room_type_id matches
            if ($room['loai_phong_id'] != $roomTypeId) {
                $validationErrors[] = "Dữ liệu không hợp lệ cho loại phòng ID: {$roomTypeId}";
                continue;
            }

            $loaiPhong = LoaiPhong::find($room['loai_phong_id']);

            if (!$loaiPhong) {
                $validationErrors[] = "Loại phòng ID {$room['loai_phong_id']} không tồn tại";
                continue;
            }

            // Check if room type is active
            if ($loaiPhong->trang_thai !== 'hoat_dong') {
                $validationErrors[] = "Loại phòng '{$loaiPhong->ten_loai}' hiện không khả dụng";
                continue;
            }

            // Validate quantity is positive
            if ($room['so_luong'] < 1) {
                $validationErrors[] = "Số lượng phòng cho loại phòng '{$loaiPhong->ten_loai}' phải lớn hơn 0";
                continue;
            }

            // Check availability based on date range (real-time check)
            $availableCount = Phong::countAvailableRooms($loaiPhong->id, $request->ngay_nhan, $request->ngay_tra);
            if ($availableCount < $room['so_luong']) {
                $validationErrors[] = "Loại phòng '{$loaiPhong->ten_loai}' chỉ có {$availableCount} phòng trống trong khoảng thời gian từ " . date('d/m/Y', strtotime($request->ngay_nhan)) . " đến " . date('d/m/Y', strtotime($request->ngay_tra)) . ". Bạn đã chọn {$room['so_luong']} phòng";
                continue;
            }

            // Use promotional price if available, otherwise use base price
            $pricePerNight = $loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban ?? 0;
            $roomTotal = $pricePerNight * $nights * $room['so_luong'];
            $totalPrice += $roomTotal;

            $roomDetails[] = [
                'loai_phong_id' => $loaiPhong->id, // Thêm ID để dùng trong transaction
                'loai_phong' => $loaiPhong,
                'so_luong' => $room['so_luong'],
                'price' => $roomTotal,
            ];
        }

        // Return errors if any validation failed
        if (!empty($validationErrors)) {
            return back()->withErrors(['error' => implode('. ', $validationErrors)])->withInput();
        }

        // Additional validation: at least one room must be selected
        if (empty($roomDetails)) {
            return back()->withErrors(['room_types' => 'Vui lòng chọn ít nhất một loại phòng'])->withInput();
        }

        // Xử lý voucher nếu có
        // Server-side must match client-side: voucher can be percentage (<=100) or fixed amount (>100)
        // and may target a specific loai_phong_id (apply only to that room type) or NULL for all.
        $voucherId = null;
        $roomSubtotal = $totalPrice; // Tổng tiền phòng chưa giảm
        $roomDiscount = 0;
        $roomNetTotal = $roomSubtotal;
        if ($request->voucher) {
            // Try to find the voucher by code first
            $voucher = Voucher::where('ma_voucher', $request->voucher)->first();

            // Server-side checks: voucher must exist, have quantity, be active, and be valid for the selected check-in date
            if ($voucher) {
                $checkin = null;
                try {
                    $checkin = \Carbon\Carbon::parse($request->ngay_nhan)->startOfDay();
                } catch (\Exception $e) {
                    $checkin = now()->startOfDay();
                }

                $vStart = null; $vEnd = null;
                try { $vStart = \Carbon\Carbon::parse($voucher->ngay_bat_dau)->startOfDay(); } catch(\Exception $e) { $vStart = null; }
                try { $vEnd = \Carbon\Carbon::parse($voucher->ngay_ket_thuc)->startOfDay(); } catch(\Exception $e) { $vEnd = null; }

                $validNow = ($voucher->so_luong > 0) && ($voucher->trang_thai === 'con_han');
                // check date range against selected check-in
                $dateOk = true;
                if ($vStart && $vEnd && $checkin) {
                    if ($checkin->lt($vStart) || $checkin->gt($vEnd)) $dateOk = false;
                }

                if (! $validNow || ! $dateOk) {
                    // Voucher is not applicable for the booking dates or not active/available
                    // Return with a validation error so admin knows why voucher wasn't applied
                    return back()->withErrors(['voucher' => 'Mã giảm giá không áp dụng cho ngày nhận phòng đã chọn hoặc không khả dụng'])->withInput();
                }
            }

            if ($voucher) {
                $discountValue = floatval($voucher->gia_tri ?? 0);

                // Compute applicable room total depending on voucher->loai_phong_id
                $applicableTotal = 0;
                if (empty($voucher->loai_phong_id) || $voucher->loai_phong_id === null) {
                    $applicableTotal = $roomSubtotal;
                } else {
                    // Sum only room totals for the specific room type
                    foreach ($roomDetails as $rd) {
                        if (isset($rd['loai_phong_id']) && $rd['loai_phong_id'] == $voucher->loai_phong_id) {
                            $applicableTotal += $rd['price'];
                        }
                    }
                }

                if ($applicableTotal > 0 && $discountValue > 0) {
                    if ($discountValue <= 100) {
                        // percentage
                        $roomDiscount = ($applicableTotal * $discountValue) / 100;
                    } else {
                        // fixed amount - do not exceed applicable total
                        $roomDiscount = min($discountValue, $applicableTotal);
                    }

                    // Apply discount only to room subtotal (services excluded)
                    $roomNetTotal = max(0, $roomSubtotal - $roomDiscount);
                    $voucherId = $voucher->id;
                    // decrement available quantity
                    try {
                        $voucher->decrement('so_luong');
                    } catch (\Exception $e) {
                        // ignore decrement errors for now - transaction will still proceed
                        Log::warning('Failed to decrement voucher quantity: ' . $e->getMessage());
                    }
                }
            }
        }
        // Calculate price per room (distribute voucher discount proportionally)
        // Prevent division by zero
        if ($roomSubtotal <= 0) {
            return back()->withErrors(['error' => 'Tổng giá phòng không hợp lệ. Vui lòng kiểm tra lại.'])->withInput();
        }
        $priceRatio = $roomNetTotal / $roomSubtotal;

        // Tính tổng số lượng phòng
        $totalSoLuong = array_sum(array_column($roomDetails, 'so_luong'));

        // Lấy loại phòng đầu tiên làm loại phòng chính (cho backward compatibility)
        $firstLoaiPhongId = $roomDetails[0]['loai_phong_id'];

        // Chuẩn bị mảng room_types để lưu vào JSON
        $roomTypesArray = [];
        foreach ($roomDetails as $roomDetail) {
            $roomPrice = $roomDetail['price'] * $priceRatio;
            $roomTypesArray[] = [
                'loai_phong_id' => $roomDetail['loai_phong_id'],
                'so_luong' => $roomDetail['so_luong'],
                'gia_rieng' => $roomPrice,
            ];
        }

        // Tính tổng tiền dịch vụ (nếu có) từ input services_data (được gửi theo từng ngày và phòng riêng)
        $servicesData = $request->input('services_data', []);
        $selectedPhongIds = $request->input('selected_phong_ids', []); // Danh sách phòng được chọn riêng
        $totalServicePrice = 0;
        $normalizedServices = [];
        
        if (is_array($servicesData) && !empty($servicesData)) {
            foreach ($servicesData as $svcId => $svcRow) {
                $service = Service::find($svcId);
                if (!$service) {
                    continue;
                }

                // Kiểm tra xem dịch vụ này có chỉ định phòng riêng hay áp dụng cho tất cả
                // svcRow['phong_ids'] = [id1, id2, ...] nếu áp dụng cho phòng riêng
                // svcRow['phong_ids'] = [] hoặc không có = áp dụng cho TẤT CẢ phòng (nhân với số phòng)
                $servicePhongIds = isset($svcRow['phong_ids']) && is_array($svcRow['phong_ids']) 
                    ? array_filter($svcRow['phong_ids']) 
                    : [];

                $entries = isset($svcRow['entries']) && is_array($svcRow['entries']) ? $svcRow['entries'] : [];
                if (empty($entries) && isset($svcRow['so_luong']) && intval($svcRow['so_luong']) > 0) {
                    $entries = [
                        [
                            'ngay' => $request->ngay_nhan,
                            'so_luong' => intval($svcRow['so_luong']),
                        ],
                    ];
                }

                $cleanEntries = [];
                foreach ($entries as $entry) {
                    $day = $entry['ngay'] ?? null;
                    $qty = isset($entry['so_luong']) ? intval($entry['so_luong']) : 0;
                    if (!$day || $qty <= 0) {
                        continue;
                    }

                    // per-entry room ids (support entries[][phong_ids] array or singular entries[][phong_id])
                    $entryPhongIds = [];
                    if (isset($entry['phong_ids']) && is_array($entry['phong_ids'])) {
                        $entryPhongIds = array_filter($entry['phong_ids']);
                    } elseif (isset($entry['phong_id'])) {
                        $entryPhongIds = array_filter([$entry['phong_id']]);
                    }

                    $cleanEntries[] = [
                        'ngay' => $day,
                        'so_luong' => $qty,
                        'phong_ids' => $entryPhongIds,
                    ];
                    
                    // Tính tổng tiền:
                    // - Nếu entry có phong_ids: nhân với số phòng trong entry
                    // - Else if service-level phong_ids present: nhân với số phòng chỉ định
                    // - Nếu không (áp dụng tất cả): nhân với tổng số phòng
                    if (!empty($entryPhongIds)) {
                        $priceMultiplier = count($entryPhongIds);
                    } else {
                        $priceMultiplier = !empty($servicePhongIds) ? count($servicePhongIds) : $totalSoLuong;
                    }
                    $totalServicePrice += $qty * ($service->price ?? 0) * $priceMultiplier;
                }

                if (!empty($cleanEntries)) {
                    $normalizedServices[] = [
                        'service_id' => $service->id,
                        'unit_price' => $service->price ?? 0,
                        'entries' => $cleanEntries,
                        'phong_ids' => $servicePhongIds, // Danh sách phòng nếu chỉ định riêng ở mức service
                    ];
                }
            }
        }

        // Cộng tổng tiền dịch vụ vào tổng thanh toán cuối cùng
        $finalPrice = $roomNetTotal + $totalServicePrice;

    // Create single booking within transaction to ensure atomicity
    $booking = DB::transaction(function () use (
        $roomDetails,
        $priceRatio,
        $request,
        $voucherId,
        $finalPrice,
        $roomSubtotal,
        $roomNetTotal,
        $roomDiscount,
        $totalServicePrice,
        $totalSoLuong,
        $firstLoaiPhongId,
        $roomTypesArray,
        $normalizedServices
    ) {
            // Validate availability for all room types first
            foreach ($roomDetails as $roomDetail) {
                // Lock and re-check availability inside transaction to prevent race conditions
                $loaiPhong = LoaiPhong::lockForUpdate()->findOrFail($roomDetail['loai_phong_id']);

                // Tìm phòng trống TRƯỚC khi kiểm tra để đảm bảo có đủ phòng trong khoảng thời gian cụ thể
                $availableRooms = Phong::findAvailableRooms(
                    $loaiPhong->id,
                    $request->ngay_nhan,
                    $request->ngay_tra,
                    $roomDetail['so_luong'], // Tìm đủ số lượng phòng cần thiết
                    null // Không exclude booking nào (booking chưa tồn tại)
                );

                // Kiểm tra xem có đủ phòng không (dựa trên conflict check thực tế)
                if ($availableRooms->count() < $roomDetail['so_luong']) {
                    $availableCount = Phong::countAvailableRooms($loaiPhong->id, $request->ngay_nhan, $request->ngay_tra);
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'error' => "Loại phòng '{$loaiPhong->ten_loai}' chỉ có {$availableCount} phòng trống trong khoảng thời gian từ " . date('d/m/Y', strtotime($request->ngay_nhan)) . " đến " . date('d/m/Y', strtotime($request->ngay_tra)) . ". Bạn đã chọn {$roomDetail['so_luong']} phòng."
                    ]);
                }
            }

            // Tạo 1 booking duy nhất chứa tất cả các loại phòng
            $bookingData = [
                'nguoi_dung_id' => Auth::id(),
                'loai_phong_id' => $firstLoaiPhongId, // Loại phòng chính (cho backward compatibility)
                'room_types' => $this->normalizeRoomTypesArray($roomTypesArray), // Lưu tất cả loại phòng vào JSON
                'so_luong_da_dat' => $totalSoLuong, // Tổng số lượng phòng
                'phong_id' => null, // Không gán phòng ở đây, sẽ dùng phong_ids JSON
                'ngay_dat' => now(),
                'ngay_nhan' => $request->ngay_nhan,
                'ngay_tra' => $request->ngay_tra,
                'so_nguoi' => $request->so_nguoi,
                'trang_thai' => 'cho_xac_nhan',
                'tong_tien' => $finalPrice, // Tổng tiền sau khi cộng dịch vụ
                'voucher_id' => $voucherId,
                'username' => $request->username,
                'email' => $request->email,
                'sdt' => $request->sdt,
                'cccd' => $request->cccd
            ];

            if (Schema::hasColumn('dat_phong', 'tien_phong')) {
                $bookingData['tien_phong'] = $roomNetTotal;
            }
            if (Schema::hasColumn('dat_phong', 'tien_dich_vu')) {
                $bookingData['tien_dich_vu'] = $totalServicePrice;
            }

            $booking = DatPhong::create($bookingData);

            // Gán phòng cho tất cả các loại phòng
            $allPhongIds = [];
            $requestedRooms = $request->input('rooms', []);
            foreach ($roomDetails as $roomDetail) {
                $loaiPhong = LoaiPhong::find($roomDetail['loai_phong_id']);
                $phongIdsForThisType = []; // Đếm riêng cho từng loại phòng

                // Nếu admin đã chọn phòng cụ thể cho loại phòng này, ưu tiên dùng các phòng đó
                $selectedForType = $requestedRooms[$loaiPhong->id]['phong_ids'] ?? null;
                if (is_array($selectedForType) && count($selectedForType) == $roomDetail['so_luong']) {
                    foreach ($selectedForType as $phongId) {
                        $phongLocked = Phong::lockForUpdate()->find($phongId);
                        if (!$phongLocked) continue;
                        // Kiểm tra phòng thuộc loại phòng tương ứng
                        if ($phongLocked->loai_phong_id != $loaiPhong->id) continue;
                        // Kiểm tra phòng có thực sự trống trong khoảng thời gian
                        if (!$phongLocked->isAvailableInPeriod($request->ngay_nhan, $request->ngay_tra, $booking->id)) continue;
                        $phongIdsForThisType[] = $phongLocked->id;
                        $allPhongIds[] = $phongLocked->id;
                    }

                    if (count($phongIdsForThisType) < $roomDetail['so_luong']) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'error' => "Phòng được chọn không khả dụng hoặc không thuộc loại phòng yêu cầu. Vui lòng kiểm tra lại."
                        ]);
                    }

                    // tiếp tục sang loại phòng tiếp theo
                    continue;
                }

                // Nếu không có phòng được chọn trước, tìm và gán phòng tự động
                $availableRooms = Phong::findAvailableRooms(
                    $loaiPhong->id,
                    $request->ngay_nhan,
                    $request->ngay_tra,
                    $roomDetail['so_luong'], // Tìm đủ số lượng phòng cần thiết
                    $booking->id // Exclude booking hiện tại
                )->values();

                // Lưu các phòng vào phong_ids JSON
                foreach ($availableRooms as $phong) {
                    // Lock phòng trước khi gán để tránh race condition
                    $phongLocked = Phong::lockForUpdate()->find($phong->id);
                    if (!$phongLocked) {
                        continue;
                    }

                    // Double-check availability sau khi lock
                    if ($phongLocked->isAvailableInPeriod($request->ngay_nhan, $request->ngay_tra, $booking->id)) {
                        $allPhongIds[] = $phongLocked->id;
                        $phongIdsForThisType[] = $phongLocked->id;
                    }
                }

                // Kiểm tra xem đã gán đủ phòng cho loại phòng này chưa
                if (count($phongIdsForThisType) < $roomDetail['so_luong']) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'error' => "Không thể gán đủ {$roomDetail['so_luong']} phòng cho loại phòng '{$loaiPhong->ten_loai}'. Chỉ gán được " . count($phongIdsForThisType) . " phòng. Vui lòng thử lại."
                    ]);
                }
            }

            // Lưu tất cả phong_ids vào JSON column
            $booking->phong_ids = $allPhongIds;
            $booking->save();

            // Cập nhật phong_id (legacy support) nếu chỉ có 1 phòng
            if (count($allPhongIds) == 1) {
                $booking->update(['phong_id' => $allPhongIds[0]]);
            }

            // NOTE: Do NOT create an invoice here. Invoices should be created
            // when a booking is confirmed (trang_thai = 'da_xac_nhan').
            // Invoice creation is moved to the confirmation flow (quickConfirm/markPaid).

            // Tạo các bản ghi dịch vụ cho booking
            // Hỗ trợ cả dịch vụ áp dụng cho tất cả phòng (phong_id = NULL)
            // và dịch vụ riêng cho phòng cụ thể (phong_id = room_id)
            if (!empty($normalizedServices)) {
                foreach ($normalizedServices as $svcPayload) {
                    $svcLevelPhongIds = $svcPayload['phong_ids'] ?? [];
                    foreach ($svcPayload['entries'] as $entry) {
                        $entryPhongIds = $entry['phong_ids'] ?? [];
                        // if entry has no specific rooms, fallback to service-level rooms
                        $usePhongIds = !empty($entryPhongIds) ? $entryPhongIds : $svcLevelPhongIds;

                        if (empty($usePhongIds)) {
                            // global service for all rooms: create a record per assigned room when possible
                            if (!empty($allPhongIds)) {
                                foreach ($allPhongIds as $phongId) {
                                    \App\Models\BookingService::create([
                                        'dat_phong_id' => $booking->id,
                                        'service_id' => $svcPayload['service_id'],
                                        'quantity' => $entry['so_luong'],
                                        'unit_price' => $svcPayload['unit_price'],
                                        'used_at' => $entry['ngay'],
                                        'phong_id' => $phongId,
                                    ]);
                                }
                            } else {
                                // Fallback: no rooms assigned -> keep aggregated record
                                \App\Models\BookingService::create([
                                    'dat_phong_id' => $booking->id,
                                    'service_id' => $svcPayload['service_id'],
                                    'quantity' => $entry['so_luong'],
                                    'unit_price' => $svcPayload['unit_price'],
                                    'used_at' => $entry['ngay'],
                                    'phong_id' => null,
                                ]);
                            }
                        } else {
                            // create per-room entries for each selected room in this entry
                            foreach ($usePhongIds as $phongId) {
                                \App\Models\BookingService::create([
                                    'dat_phong_id' => $booking->id,
                                    'service_id' => $svcPayload['service_id'],
                                    'quantity' => $entry['so_luong'],
                                    'unit_price' => $svcPayload['unit_price'],
                                    'used_at' => $entry['ngay'],
                                    'phong_id' => $phongId,
                                ]);
                            }
                        }
                    }
                }
            }

            // Booking sẽ được tự động hủy bởi AutoCancelExpiredBookings middleware
            // Không cần queue worker - tích hợp trực tiếp vào code

            return $booking;
        });

        // Gửi mail cho admin: đơn đặt phòng mới (trạng thái chờ xác nhận)
        try {
            $adminEmails = \App\Models\User::where('vai_tro', 'admin')
                ->where('trang_thai', 'hoat_dong')
                ->pluck('email')
                ->filter()
                ->all();
            if (!empty($adminEmails)) {
                Mail::to($adminEmails)->send(new AdminBookingEvent($booking->load(['loaiPhong']), 'created'));
            }
        } catch (\Throwable $e) {
            Log::warning('Send admin booking created mail failed: ' . $e->getMessage());
        }

        // Lấy danh sách tên loại phòng từ room_types JSON
        $roomTypes = [];
        foreach ($booking->getRoomTypes() as $roomType) {
            $loaiPhong = LoaiPhong::find($roomType['loai_phong_id']);
            if ($loaiPhong) {
                $roomTypes[] = $loaiPhong->ten_loai;
            }
        }
        $roomTypesText = implode(', ', $roomTypes);

        return redirect()->route('admin.dat_phong.show', $booking->id)
            ->with('success', 'Đặt phòng thành công! Loại phòng: ' . $roomTypesText);
    }

    public function blockRoom($id)
    {
        $booking = DatPhong::findOrFail($id);

        // Chỉ cho phép chống phòng khi đã xác nhận
        if ($booking->trang_thai !== 'da_xac_nhan') {
            return redirect()->route('admin.dat_phong.index')
                ->with('error', 'Chỉ có thể chống phòng đã xác nhận');
        }

        // Cập nhật trạng thái đặt phòng thành "đã chống"
        // Note: Không còn phòng riêng lẻ, chỉ cập nhật booking status
        $booking->update(['trang_thai' => 'da_chong']);

        return redirect()->route('admin.dat_phong.index')
            ->with('success', 'Đã chống phòng thành công! Phòng không thể đặt được cho đến khi hủy chống.');
    }

    /**
     * Quick confirm booking from index card
     */
    public function quickConfirm($id)
    {
        $booking = DatPhong::findOrFail($id);

        if ($booking->trang_thai !== 'cho_xac_nhan') {
            return redirect()->route('admin.dat_phong.index')
                ->with('error', 'Chỉ xác nhận được đơn đang chờ xác nhận');
        }

        // Nếu chưa có phòng được gán, tự động gán phòng
        $assignedPhongIds = $booking->getPhongIds();
        if (empty($assignedPhongIds)) {
            $allPhongIds = [];
            $roomTypes = $booking->getRoomTypes();

            // Nếu booking có nhiều loại phòng (room_types)
            if (count($roomTypes) > 0) {
                foreach ($roomTypes as $roomType) {
                    $soLuongCan = $roomType['so_luong'] ?? 1;
                    $loaiPhongId = $roomType['loai_phong_id'];

                    $availableRooms = Phong::findAvailableRooms(
                        $loaiPhongId,
                        $booking->ngay_nhan,
                        $booking->ngay_tra,
                        $soLuongCan,
                        $booking->id
                    )->reject(function ($phong) use ($allPhongIds) {
                        return in_array($phong->id, $allPhongIds);
                    });

                    $count = 0; 
                    foreach ($availableRooms as $phong) {
                        if ($count >= $soLuongCan)
                            break;
                        $allPhongIds[] = $phong->id;
                        $count++;
                    }

                    // Nếu không đủ phòng, báo lỗi
                    if ($count < $soLuongCan) {
                        $loaiPhong = LoaiPhong::find($loaiPhongId);
                        return redirect()->route('admin.dat_phong.index')
                            ->with('error', "Không đủ phòng cho loại phòng '{$loaiPhong->ten_loai}'. Cần {$soLuongCan} phòng nhưng chỉ có {$count} phòng trống.");
                    }
                }
            } else {
                // Fallback: Booking chỉ có 1 loại phòng (legacy)
                $soLuongCan = $booking->so_luong_da_dat ?? 1;
                $availableRooms = Phong::findAvailableRooms(
                    $booking->loai_phong_id,
                    $booking->ngay_nhan,
                    $booking->ngay_tra,
                    $soLuongCan,
                    $booking->id
                );

                if ($availableRooms->count() < $soLuongCan) {
                    return redirect()->route('admin.dat_phong.index')
                        ->with('error', "Không đủ phòng. Cần {$soLuongCan} phòng nhưng chỉ có {$availableRooms->count()} phòng trống.");
                }

                foreach ($availableRooms as $phong) {
                    $allPhongIds[] = $phong->id;
                }
            }

            // Cập nhật phong_ids JSON
            $booking->phong_ids = $allPhongIds;

            // Cập nhật phong_id (legacy support) nếu chỉ có 1 phòng
            if (count($allPhongIds) == 1) {
                $booking->phong_id = $allPhongIds[0];
            } else {
                $booking->phong_id = null;
            }
        }

        // Allow confirming even if dates are in the past
        $booking->trang_thai = 'da_xac_nhan';
        $booking->save();

        // ========================================
        // CALCULATE PRICES (matching update() logic)
        // ========================================
        
        // Calculate number of nights
        $nights = Carbon::parse($booking->ngay_nhan)->diffInDays(Carbon::parse($booking->ngay_tra));
        $nights = max(1, $nights);
        
        // Get room types
        $roomTypes = $booking->getRoomTypes();
        $totalPrice = 0;
        $roomTypesArray = [];
        $totalSoLuong = 0;
        
        // Calculate room prices
        foreach ($roomTypes as $roomType) {
            $soLuong = $roomType['so_luong'] ?? 1;
            $loaiPhongId = $roomType['loai_phong_id'];
            $totalSoLuong += $soLuong;
            
            $loaiPhong = LoaiPhong::find($loaiPhongId);
            $unitPricePerNight = $loaiPhong ? ($loaiPhong->gia_khuyen_mai ?? $loaiPhong->gia_co_ban ?? 0) : 0;
            $roomTotal = $unitPricePerNight * $nights * $soLuong;
            $totalPrice += $roomTotal;
            
            $roomTypesArray[] = [
                'loai_phong_id' => $loaiPhongId,
                'so_luong' => $soLuong,
                'gia_rieng' => $roomTotal,
            ];
        }
        
        // Calculate service prices
        $totalServicePrice = 0;
        $serviceTotal = \App\Models\BookingService::where('dat_phong_id', $booking->id)
            ->sum(DB::raw('quantity * unit_price'));
        $totalServicePrice = $serviceTotal ?? 0;
        
        // Calculate voucher discount (if exists)
        $voucherDiscount = 0;
        if ($booking->voucher_id && $booking->voucher) {
            $voucher = $booking->voucher;
            if ($voucher->gia_tri) {
                // Compute applicable total: if voucher targets a specific loai_phong_id,
                // sum only matching room types; otherwise use full room subtotal.
                $applicableTotal = 0;
                if (empty($voucher->loai_phong_id)) {
                    $applicableTotal = $totalPrice;
                } else {
                    foreach ($roomTypesArray as $rt) {
                        if (isset($rt['loai_phong_id']) && $rt['loai_phong_id'] == $voucher->loai_phong_id) {
                            $applicableTotal += $rt['gia_rieng'];
                        }
                    }
                }
                
                if ($applicableTotal > 0) {
                    if ($voucher->gia_tri <= 100) {
                        // Percentage discount
                        $voucherDiscount = intval(round($applicableTotal * ($voucher->gia_tri / 100)));
                    } else {
                        // Fixed amount discount (cap at applicable total)
                        $voucherDiscount = intval(min(round($voucher->gia_tri), $applicableTotal));
                    }
                }
            }
        }
        
        // Final total: room price + service price - voucher discount
        $finalTotal = max(0, $totalPrice + $totalServicePrice - $voucherDiscount);
        
        // Update booking with calculated totals
        $updateData = [
            'tong_tien' => $finalTotal,
        ];
        if (Schema::hasColumn('dat_phong', 'tien_phong')) {
            $updateData['tien_phong'] = $totalPrice;
        }
        if (Schema::hasColumn('dat_phong', 'tien_dich_vu')) {
            $updateData['tien_dich_vu'] = $totalServicePrice;
        }
        
        $booking->update($updateData);

        // Create invoice now that booking is confirmed (if not exists)
        if (!$booking->invoice) {
            $booking = $booking->fresh();

            // Prepare invoice data with full breakdown
            $invoiceData = [
                'dat_phong_id' => $booking->id,
                'tong_tien' => $booking->tong_tien ?? 0,
                'trang_thai' => 'cho_thanh_toan',
                'phuong_thuc' => null,
            ];

            if (Schema::hasColumn('hoa_don', 'tien_phong')) {
                $invoiceData['tien_phong'] = $booking->tien_phong ?? 0;
            }
            if (Schema::hasColumn('hoa_don', 'tien_dich_vu')) {
                $invoiceData['tien_dich_vu'] = $booking->tien_dich_vu ?? 0;
            }
            if (Schema::hasColumn('hoa_don', 'giam_gia')) {
                $invoiceData['giam_gia'] = $voucherDiscount;
            }
            if (Schema::hasColumn('hoa_don', 'ngay_tao')) {
                $invoiceData['ngay_tao'] = now();
            }

            try {
                \App\Models\Invoice::create($invoiceData);
            } catch (\Exception $e) {
                Log::warning('Failed to create invoice on confirmation: ' . $e->getMessage());
            }
        }

        // Gửi mail xác nhận đặt phòng
        if ($booking->email) {
            try {
                Mail::to($booking->email)->send(new BookingConfirmed($booking->load('loaiPhong')));
            } catch (\Throwable $e) {
                Log::warning('Send booking confirmed mail failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.dat_phong.index')
            ->with('success', 'Phòng đã được xác nhận thành công!');
    }

    /**
     * Mark booking as paid: create (or update) invoice to 'da_thanh_toan'
     */
    public function markPaid($id)
    {
        $booking = DatPhong::with('invoice')->findOrFail($id);

        // Create invoice if missing
        $invoice = $booking->invoice;
        if (!$invoice) {
            \App\Services\BookingPriceCalculator::recalcTotal($booking);
            $booking = $booking->fresh();

            $invoiceData = [
                'dat_phong_id' => $booking->id,
                'tong_tien' => $booking->tong_tien,
                'trang_thai' => 'da_thanh_toan',
                'phuong_thuc' => Schema::hasColumn('hoa_don', 'phuong_thuc') ? 'tien_mat' : null,
            ];

            if (Schema::hasColumn('hoa_don', 'tien_phong')) {
                $invoiceData['tien_phong'] = $booking->tong_tien;
            }
            if (Schema::hasColumn('hoa_don', 'ngay_tao')) {
                $invoiceData['ngay_tao'] = now();
            }

            $invoice = \App\Models\Invoice::create($invoiceData);
        } else {
            $invoice->update([
                'trang_thai' => 'da_thanh_toan',
            ]);
        }

        // Optionally confirm booking if still pending
        if ($booking->trang_thai === 'cho_xac_nhan') {
            $booking->trang_thai = 'da_xac_nhan';
            $booking->save();
        }

        // Gửi mail hóa đơn đã thanh toán (khách hàng)
        if ($booking->email) {
            try {
                Mail::to($booking->email)->send(new InvoicePaid($booking->load(['loaiPhong'])));
            } catch (\Throwable $e) {
                Log::warning('Send invoice mail failed: ' . $e->getMessage());
            }
        }

        // Gửi mail cho admin: đơn đã thanh toán
        try {
            $adminEmails = \App\Models\User::where('vai_tro', 'admin')
                ->where('trang_thai', 'hoat_dong')
                ->pluck('email')
                ->filter()
                ->all();
            if (!empty($adminEmails)) {
                Mail::to($adminEmails)->send(new AdminBookingEvent($booking->load(['loaiPhong']), 'paid'));
            }
        } catch (\Throwable $e) {
            Log::warning('Send admin paid mail failed: ' . $e->getMessage());
        }

        return redirect()->route('admin.dat_phong.index')
            ->with('success', 'Đã đánh dấu thanh toán và đồng bộ hóa đơn thành công.');
    }

    /**
     * Check-in booking
     */
    public function checkin(Request $request, $id)
    {
        $validated = $request->validate([
            'ghi_chu_checkin' => 'nullable|string|max:500',
        ], [
            'ghi_chu_checkin.max' => 'Ghi chú không được vượt quá 500 ký tự',
        ]);

        try {
            DB::transaction(function () use ($id, $validated) {
                $booking = DatPhong::lockForUpdate()->findOrFail($id);

                // Validate
                if (!$booking->canCheckin()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'error' => 'Không thể check-in booking này. Booking phải đã thanh toán và chưa check-in.'
                    ]);
                }

                // Update booking
                $booking->update([
                    'thoi_gian_checkin' => now(),
                    'nguoi_checkin' => Auth::user()->ho_ten,
                    'ghi_chu_checkin' => $validated['ghi_chu_checkin'] ?? null,
                ]);

                // Update room status to 'dang_thue'
                foreach ($booking->getAssignedPhongs() as $phong) {
                    $phong->update(['trang_thai' => 'dang_thue']);
                }

                Log::info('Booking checked in', [
                    'booking_id' => $booking->id,
                    'staff' => Auth::user()->ho_ten,
                ]);
            });

            return redirect()->back()->with('success', 'Check-in thành công');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        } catch (\Exception $e) {
            Log::error('Check-in failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi check-in. Vui lòng thử lại.');
        }
    }

    /**
     * Check-out booking
     */
    public function checkout(Request $request, $id)
    {
        $validated = $request->validate([
            'phi_phat_sinh' => 'nullable|numeric|min:0',
            'ly_do_phi' => 'nullable|string|max:500',
            'ghi_chu_checkout' => 'nullable|string|max:500',
        ], [
            'phi_phat_sinh.numeric' => 'Phụ phí phải là số',
            'phi_phat_sinh.min' => 'Phụ phí không được âm',
            'ly_do_phi.max' => 'Lý do không được vượt quá 500 ký tự',
            'ghi_chu_checkout.max' => 'Ghi chú không được vượt quá 500 ký tự',
        ]);

        try {
            DB::transaction(function () use ($id, $validated) {
                $booking = DatPhong::lockForUpdate()->findOrFail($id);

                // Validate
                if (!$booking->canCheckout()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'error' => 'Không thể check-out. Booking phải đã check-in và chưa check-out.'
                    ]);
                }

                // Calculate late checkout fee
                $phiCheckoutMuon = 0;
                $checkoutTime = now();
                $expectedCheckout = Carbon::parse($booking->ngay_tra)->setTime(12, 0);

                if ($checkoutTime->gt($expectedCheckout)) {
                    $hoursLate = $checkoutTime->diffInHours($expectedCheckout);
                    if ($hoursLate <= 6) { // Before 18:00
                        $phiCheckoutMuon = $booking->tong_tien * 0.5;
                    } else { // After 18:00
                        $phiCheckoutMuon = $booking->tong_tien;
                    }
                }

                $tongPhiPhatSinh = ($validated['phi_phat_sinh'] ?? 0) + $phiCheckoutMuon;

                // Build checkout note
                $ghiChuCheckout = $validated['ghi_chu_checkout'] ?? '';
                if ($phiCheckoutMuon > 0) {
                    $ghiChuCheckout .= "\nPhí check-out muộn: " . number_format($phiCheckoutMuon) . "đ";
                }
                if (!empty($validated['ly_do_phi'])) {
                    $ghiChuCheckout .= "\nLý do phụ phí: " . $validated['ly_do_phi'];
                }

                // Update booking
                $booking->update([
                    'thoi_gian_checkout' => $checkoutTime,
                    'nguoi_checkout' => Auth::user()->ho_ten,
                    'phi_phat_sinh' => $tongPhiPhatSinh,
                    'ghi_chu_checkout' => trim($ghiChuCheckout),
                    'trang_thai' => 'da_tra',
                ]);

                // Update invoice
                if ($booking->invoice) {
                    $tongMoi = $booking->invoice->tien_phong 
                        + $booking->invoice->tien_dich_vu 
                        + $tongPhiPhatSinh 
                        - $booking->invoice->giam_gia;

                    $booking->invoice->update([
                        'phi_phat_sinh' => $tongPhiPhatSinh,
                        'tong_tien' => $tongMoi,
                        'con_lai' => $tongMoi - $booking->invoice->da_thanh_toan,
                    ]);

                    // Create payment record for additional fees if any
                    if ($tongPhiPhatSinh > 0) {
                        ThanhToan::create([
                            'hoa_don_id' => $booking->invoice->id,
                            'loai' => 'phi_phat_sinh',
                            'so_tien' => $tongPhiPhatSinh,
                            'ngay_thanh_toan' => now(),
                            'trang_thai' => 'pending',
                            'ghi_chu' => 'Phụ phí phát sinh khi check-out',
                        ]);
                    }
                }

                // Update room status to 'dang_don'
                foreach ($booking->getAssignedPhongs() as $phong) {
                    $phong->update(['trang_thai' => 'dang_don']);
                }

                Log::info('Booking checked out', [
                    'booking_id' => $booking->id,
                    'staff' => Auth::user()->ho_ten,
                    'phi_phat_sinh' => $tongPhiPhatSinh,
                ]);
            });

            return redirect()->back()->with('success', 'Check-out thành công');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        } catch (\Exception $e) {
            Log::error('Check-out failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi check-out. Vui lòng thử lại.');
        }
    }
}

