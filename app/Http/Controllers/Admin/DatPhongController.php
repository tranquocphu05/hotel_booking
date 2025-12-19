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
use App\Traits\HasRolePermissions;
use App\Services\BookingPriceCalculator;

class DatPhongController extends Controller
{
    use HasRolePermissions;

    public function index(Request $request)
    {
        // Nhân viên và Lễ tân: xem danh sách đặt phòng
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
        // Chỉ admin mới được hủy đặt phòng
        if (!$this->hasRole('admin')) {
            abort(403, 'Bạn không có quyền hủy đặt phòng.');
        }

        $booking = DatPhong::with(['loaiPhong', 'invoice'])->findOrFail($id);

        // Không thể hủy booking đã check-in
        if ($booking->thoi_gian_checkin) {
            return redirect()->route('admin.dat_phong.index')
                ->with('error', 'Không thể hủy booking đã check-in. Vui lòng thực hiện check-out trước.');
        }

        // Chỉ cho phép hủy cho_xac_nhan hoặc da_xac_nhan (chưa check-in)
        if (!in_array($booking->trang_thai, ['cho_xac_nhan', 'da_xac_nhan'])) {
            return redirect()->route('admin.dat_phong.index')
                ->with('error', 'Chỉ có thể hủy booking đang chờ xác nhận hoặc đã xác nhận (chưa check-in).');
        }

        // Tính chính sách hoàn tiền nếu đã thanh toán
        $cancellationPolicy = null;
        if ($booking->invoice && $booking->invoice->trang_thai === 'da_thanh_toan') {
            $cancellationPolicy = $this->calculateCancellationPolicy($booking);
        }

        // Lấy thông tin tài khoản ngân hàng khách (nếu khách đã gửi trong yêu cầu hủy trước đó)
        $bankAccountInfo = [
            'ten_chu_tai_khoan' => null,
            'so_tai_khoan'      => null,
            'ten_ngan_hang'     => null,
        ];

        if (!empty($booking->ghi_chu_hoan_tien)) {
            $lines = preg_split("/(\r\n|\n|\r)/", $booking->ghi_chu_hoan_tien);
            foreach ($lines as $line) {
                $line = trim($line);

                if (stripos($line, 'Chủ tài khoản:') === 0) {
                    $bankAccountInfo['ten_chu_tai_khoan'] = trim(substr($line, strlen('Chủ tài khoản:')));
                } elseif (stripos($line, 'Số tài khoản:') === 0) {
                    $bankAccountInfo['so_tai_khoan'] = trim(substr($line, strlen('Số tài khoản:')));
                } elseif (stripos($line, 'Ngân hàng:') === 0) {
                    $bankAccountInfo['ten_ngan_hang'] = trim(substr($line, strlen('Ngân hàng:')));
                }
            }
        }

        return view('admin.dat_phong.cancel', compact('booking', 'cancellationPolicy', 'bankAccountInfo'));
    }

    public function submitCancel(Request $request, $id)
    {
        // Chỉ admin mới được hủy đặt phòng
        if (!$this->hasRole('admin')) {
            abort(403, 'Bạn không có quyền hủy đặt phòng.');
        }

        $booking = DatPhong::with(['invoice'])->findOrFail($id);

        // Validate
        $request->validate([
            'ly_do' => 'required|in:thay_doi_lich_trinh,thay_doi_ke_hoach,khong_phu_hop,ly_do_khac'
        ], [
            'ly_do.required' => 'Vui lòng chọn lý do hủy đặt phòng',
            'ly_do.in' => 'Lý do không hợp lệ'
        ]);
        
        // Không thể hủy booking đã check-in
        if ($booking->thoi_gian_checkin) {
            return redirect()->route('admin.dat_phong.index')
                ->with('error', 'Không thể hủy booking đã check-in. Vui lòng thực hiện check-out trước.');
        }

        // Chỉ cho phép hủy cho_xac_nhan hoặc da_xac_nhan (chưa check-in)
        if (!in_array($booking->trang_thai, ['cho_xac_nhan', 'da_xac_nhan'])) {
            return redirect()->route('admin.dat_phong.index')
                ->with('error', 'Chỉ có thể hủy booking đang chờ xác nhận hoặc đã xác nhận (chưa check-in).');
        }

        // Tính toán chính sách hoàn tiền nếu có hóa đơn đã thanh toán
        $refundInfo = null;
        $invoice = $booking->invoice;
        if ($invoice && $invoice->trang_thai === 'da_thanh_toan') {
            $refundInfo = $this->calculateCancellationPolicy($booking);

            if (!$refundInfo['can_cancel']) {
                return redirect()->route('admin.dat_phong.index')
                    ->with('error', $refundInfo['message']);
            }
        }

        // Map mã lý do thành nội dung dễ đọc để lưu vào ly_do_huy (hiển thị cho khách)
        $reasonCode = $request->input('ly_do');
        $reasonText = match ($reasonCode) {
            'thay_doi_lich_trinh' => 'Khách thay đổi lịch trình',
            'thay_doi_ke_hoach'   => 'Khách thay đổi kế hoạch',
            'khong_phu_hop'       => 'Khách thấy phòng/khách sạn không phù hợp',
            'ly_do_khac'          => 'Hủy theo yêu cầu của khách (lý do khác)',
            default               => 'Hủy theo yêu cầu của khách',
        };

        // Cập nhật trạng thái và lý do hủy, đồng thời giải phóng phòng, xử lý hoàn tiền
        DB::transaction(function () use ($booking, $request, $refundInfo, $invoice, $reasonText) {
            // Load relationships
            $booking->load(['phong', 'loaiPhong', 'invoice']);

            // Validate status transition trước khi hủy
            if (method_exists($booking, 'validateStatusTransition')) {
                $booking->validateStatusTransition('da_huy');
            }

            // Xử lý hoàn tiền nếu booking đã thanh toán
            $ghiChuHoanTien = null;
            if ($invoice && $invoice->trang_thai === 'da_thanh_toan' && $refundInfo) {
                $daThanhToan = $invoice->da_thanh_toan ?? 0;
                $refundAmount = $refundInfo['refund_amount'] ?? 0;
                $conLai = max(0, $daThanhToan - $refundAmount);

                // Cập nhật invoice
                $invoice->update([
                    'trang_thai' => 'hoan_tien',
                    'con_lai'    => $conLai,
                ]);

                // Ghi chú hoàn tiền
                $ghiChuHoanTien = sprintf(
                    'Hoàn tiền: %s%% (%s VNĐ). %s',
                    $refundInfo['refund_percentage'],
                    number_format($refundAmount, 0, ',', '.'),
                    $refundInfo['message']
                );

                // Tạo bản ghi thanh toán âm để thể hiện hoàn tiền
                ThanhToan::create([
                    'hoa_don_id'       => $invoice->id,
                    'so_tien'          => -$refundAmount,
                    'ngay_thanh_toan'  => now(),
                    'trang_thai'       => 'success',
                    'ghi_chu'          => $ghiChuHoanTien,
                ]);
            }

            // Update booking status + lý do hủy + ghi chú hoàn tiền
            $booking->update([
                'trang_thai'       => 'da_huy',
                'ngay_huy'         => now(),
                'ly_do_huy'        => $reasonText,
                'ghi_chu_hoan_tien'=> $ghiChuHoanTien,
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

            // Free up rooms via phong_ids JSON / pivot helper
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

            // Clear legacy phong_ids JSON nếu cột còn tồn tại (project cũ)
            if (\Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'phong_ids')) {
                $booking->phong_ids = [];
                $booking->save();
            }

            // Update so_luong_trong in loai_phong
            if ($booking->loaiPhong) {
                $trongCount = Phong::where('loai_phong_id', $booking->loai_phong_id)
                    ->where('trang_thai', 'trong')
                    ->count();
                $booking->loaiPhong->update(['so_luong_trong' => $trongCount]);
            }
        });

        // Tạo thông báo thành công với thông tin hoàn tiền
        $message = 'Đã hủy đặt phòng thành công';
        if ($refundInfo && ($refundInfo['refund_amount'] ?? 0) > 0) {
            $message .= '. Số tiền hoàn lại: ' . number_format($refundInfo['refund_amount'], 0, ',', '.') . ' VNĐ (' . $refundInfo['refund_percentage'] . '%).';
        } elseif ($refundInfo && ($refundInfo['refund_amount'] ?? 0) == 0) {
            $message .= '. ' . $refundInfo['message'];
        }

        return redirect()->route('admin.dat_phong.index')
            ->with('success', $message);
    }

    public function show($id)
    {
        $booking = DatPhong::with(['loaiPhong', 'voucher', 'phong', 'services.service', 'stayGuests.phong'])->findOrFail($id);

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
                $assignedIdsForThis = array_filter(array_values(array_map('intval', array_filter($assignedPhongIds ?? [], function ($v) {
                    return $v;
                }))));
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

        // Prepare check-in/check-out step variables
        $step3Complete = $booking->thoi_gian_checkin !== null;
        $step3Date = $step3Complete ? \Carbon\Carbon::parse($booking->thoi_gian_checkin) : null;

        return view('admin.dat_phong.show', compact(
            'booking',
            'availableRooms',
            'availableRoomsByLoaiPhong',
            'cancellationPolicy',
            'services',
            'bookingServices',
            'step3Complete',
            'step3Date'
        ));
    }

    public function edit($id)
    {
        // Nhân viên: cập nhật thông tin khách
        // Lễ tân: không được sửa đặt phòng
        if ($this->hasRole('nhan_vien')) {
            $this->authorizePermission('booking.update_customer');
        } elseif ($this->hasRole('le_tan')) {
            abort(403, 'Bạn không có quyền chỉnh sửa đặt phòng.');
        }
        
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

        // Assigned room ids for this booking from pivot table
        $assignedPhongIds = $booking->getPhongIds();

        // Get room types from pivot table and normalize
        $roomTypes = $booking->getRoomTypes();
        if ($roomTypes instanceof \Illuminate\Support\Collection) {
            $roomTypes = $roomTypes->toArray();
        }
        
        $normalizedRoomTypes = [];
        if (is_array($roomTypes)) {
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
                    // sum quantities, prefer non-zero gia_rieng if available, and merge phong_ids
                    $map[$lid]['so_luong'] += $soLuong;
                    if (empty($map[$lid]['gia_rieng']) && !empty($gia)) $map[$lid]['gia_rieng'] = $gia;
                    $map[$lid]['phong_ids'] = array_values(array_unique(array_merge($map[$lid]['phong_ids'], $phongs)));
                }
            }
            // convert map back to indexed array preserving original order of appearance
            $seen = [];
            foreach ($roomTypes as $rt) {
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
            'can_cancel'        => true,
            'refund_percentage' => 0,
            'refund_amount'     => 0,
            'penalty_amount'    => 0,
            'message'           => '',
            'days_until_checkin'=> $daysUntilCheckin,
        ];

        // Nếu đã quá ngày nhận phòng, không cho hủy (coi như đã check-in)
        if ($daysUntilCheckin < 0) {
            $totalPaid = $booking->invoice ? $booking->invoice->tong_tien : $booking->tong_tien;
            $policy['can_cancel']     = false;
            $policy['refund_percentage'] = 0;
            $policy['refund_amount']  = 0;
            $policy['penalty_amount'] = $totalPaid;
            $policy['message']        = 'Không thể hủy sau ngày nhận phòng (khách đã check-in)';
            return $policy;
        }

        // Tổng tiền đã thanh toán (nếu có invoice) hoặc tổng tiền booking
        $totalPaid = $booking->invoice ? $booking->invoice->tong_tien : $booking->tong_tien;

        // Chính sách hoàn tiền theo số ngày trước khi nhận phòng
        if ($daysUntilCheckin >= 7) {
            $policy['refund_percentage'] = 100;
            $policy['message'] = 'Hoàn 100% tiền đã thanh toán';
        } elseif ($daysUntilCheckin >= 3) {
            $policy['refund_percentage'] = 50;
            $policy['message'] = 'Hoàn 50% tiền đã thanh toán (phí hủy 50%)';
        } elseif ($daysUntilCheckin >= 1) {
            $policy['refund_percentage'] = 25;
            $policy['message'] = 'Hoàn 25% tiền đã thanh toán (phí hủy 75%)';
        } else {
            $policy['refund_percentage'] = 0;
            $policy['message'] = 'Không hoàn tiền (hủy quá gần ngày nhận phòng)';
        }

        $policy['refund_amount']  = ($totalPaid * $policy['refund_percentage']) / 100;
        $policy['penalty_amount'] = $totalPaid - $policy['refund_amount'];

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
        // Nhân viên: cập nhật thông tin khách
        // Lễ tân: không được sửa đặt phòng
        if ($this->hasRole('nhan_vien')) {
            $this->authorizePermission('booking.update_customer');
        } elseif ($this->hasRole('le_tan')) {
            abort(403, 'Bạn không có quyền chỉnh sửa đặt phòng.');
        }
        
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
            'so_nguoi' => 'required|integer|min:1|max:20',
            'so_tre_em' => 'nullable|integer|min:0|max:10',
            'so_em_be' => 'nullable|integer|min:0|max:5',
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
            'so_nguoi.integer' => 'Số người phải là số nguyên',
            'so_nguoi.min' => 'Số người phải lớn hơn 0',
            'so_nguoi.max' => 'Số người không được lớn hơn 20',
            'so_tre_em.integer' => 'Số trẻ em phải là số nguyên',
            'so_tre_em.min' => 'Số trẻ em không được nhỏ hơn 0',
            'so_tre_em.max' => 'Số trẻ em không được lớn hơn 10',
            'so_em_be.integer' => 'Số em bé phải là số nguyên',
            'so_em_be.min' => 'Số em bé không được nhỏ hơn 0',
            'so_em_be.max' => 'Số em bé không được lớn hơn 5',
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

            // Tổng phòng có thể sử dụng = available mới + phòng cũ (giữ lại)
            $totalAvailable = $availableCount + $oldPhongsOfThisType;
            
            // Kiểm tra: số lượng yêu cầu không được vượt quá tổng có sẵn
            if ($roomType['so_luong'] > $totalAvailable) {
                return back()->withErrors([
                    'room_types' => 'Loại phòng ' . $loaiPhong->ten_loai . ' chỉ có ' . $totalAvailable . ' phòng có sẵn ('. $oldPhongsOfThisType . ' phòng cũ + ' . $availableCount . ' phòng mới). Bạn không thể chọn ' . $roomType['so_luong'] . ' phòng.'
                ])->withInput();
            }
        }

        // Calculate number of nights and total rooms and price using LoaiPhong prices
        $nights = Carbon::parse($request->ngay_nhan)->diffInDays(Carbon::parse($request->ngay_tra));
        $nights = max(1, $nights);
        $checkIn = Carbon::parse($request->ngay_nhan);
        $checkOut = Carbon::parse($request->ngay_tra);

        $totalSoLuong = array_sum(array_column($roomTypes, 'so_luong'));
        $totalPrice = 0;
        $totalExtraFee = 0;
        $totalChildFee = 0;
        $totalInfantFee = 0;
        // prepare room_types array to store (similar shape as store)
        $roomTypesArray = [];
        
        $maxAdultsPerRoom = 2;
        $extraFeePercent = 0.2; // 20% cho người lớn
        $childFeePercent = 0.1; // 10% cho trẻ em
        $infantFeePercent = 0.05; // 5% cho em bé
        
        // Lấy số khách từ request hoặc booking hiện tại
        $booking = DatPhong::findOrFail($id);
        $totalAdults = $request->input('so_nguoi') ?? $booking->so_nguoi ?? ($maxAdultsPerRoom * $totalSoLuong);
        $totalChildren = $request->input('so_tre_em') ?? $booking->so_tre_em ?? 0;
        $totalInfants = $request->input('so_em_be') ?? $booking->so_em_be ?? 0;

        // Debug: log incoming totals so we can see what the client sent
        Log::info('DatPhong::update - incoming top-level totals', [
            'so_nguoi_input' => $request->input('so_nguoi'),
            'so_tre_em_input' => $request->input('so_tre_em'),
            'so_em_be_input' => $request->input('so_em_be'),
            'computed_totalAdults' => $totalAdults,
            'computed_totalChildren' => $totalChildren,
            'computed_totalInfants' => $totalInfants,
        ]);
        
        foreach ($roomTypes as $roomType) {
            $loaiPhong = LoaiPhong::find($roomType['loai_phong_id']);
            if (!$loaiPhong) continue;
            
            $soLuong = (int) $roomType['so_luong'];

            // Tính tiền phòng theo từng ngày (ngày thường/cuối tuần/ngày lễ)
            $roomBaseTotal = BookingPriceCalculator::calculateRoomTypePriceByDateRange(
                $loaiPhong,
                $checkIn,
                $checkOut,
                $soLuong
            );
            
            // Compute base room total only; surcharge will be computed globally after loop
            $roomTotal = $roomBaseTotal;
            $totalPrice += $roomTotal;
            // extra fees will be computed after loop (initialized to 0 earlier)

            $roomTypesArray[] = [
                'loai_phong_id' => $roomType['loai_phong_id'],
                'so_luong' => $soLuong,
                'gia_rieng' => $roomBaseTotal, // Chỉ lưu giá phòng gốc (để tính voucher)
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

        // Before computing voucher, validate totals against room capacity and compute surcharges (units: adult=2, child=1, infant=1)
        // Log room-level summary and raw rooms input to aid parity debugging
        $roomSummary = array_map(function($rt){
            return [
                'loai_phong_id' => $rt['loai_phong_id'] ?? null,
                'so_luong' => $rt['so_luong'] ?? 0,
                'gia_rieng' => $rt['gia_rieng'] ?? 0,
            ];
        }, $roomTypesArray);
        Log::info('DatPhong::update - roomDetails summary', ['rooms' => $roomSummary, 'totalSoLuong' => $totalSoLuong]);
        Log::info('DatPhong::update - raw rooms input', ['rooms_input' => $request->input('rooms', [])]);

        // Aggregate per-room posted totals (if any) so we can detect mismatches with top-level inputs
        $roomsInput = $request->input('rooms', []);
        $sumAdultsFromRooms = 0;
        $sumChildrenFromRooms = 0;
        $sumInfantsFromRooms = 0;
        foreach ($roomsInput as $loai => $r) {
            $qty = isset($r['so_luong']) ? (int) $r['so_luong'] : 0;
            $sumAdultsFromRooms += isset($r['so_nguoi']) ? (int) $r['so_nguoi'] : ($maxAdultsPerRoom * $qty);
            $sumChildrenFromRooms += isset($r['so_tre_em']) ? (int) $r['so_tre_em'] : 0;
            $sumInfantsFromRooms += isset($r['so_em_be']) ? (int) $r['so_em_be'] : 0;
        }

        // Prefer top-level totals from request when present to match client preview; fall back to aggregated per-room sums
        $reqTotalAdults = (int) ($request->input('so_nguoi') ?? $sumAdultsFromRooms ?? $booking->so_nguoi ?? ($maxAdultsPerRoom * $totalSoLuong));
        $reqTotalChildren = (int) ($request->input('so_tre_em') ?? $sumChildrenFromRooms ?? $booking->so_tre_em ?? 0);
        $reqTotalInfants = (int) ($request->input('so_em_be') ?? $sumInfantsFromRooms ?? $booking->so_em_be ?? 0);

        if ($reqTotalAdults !== ($sumAdultsFromRooms ?? 0) || $reqTotalChildren !== ($sumChildrenFromRooms ?? 0) || $reqTotalInfants !== ($sumInfantsFromRooms ?? 0)) {
            Log::warning('DatPhong::update - mismatch between per-room sums and top-level totals', [
                'sum_from_rooms' => ['adults' => $sumAdultsFromRooms, 'children' => $sumChildrenFromRooms, 'infants' => $sumInfantsFromRooms],
                'request_totals' => ['adults' => $reqTotalAdults, 'children' => $reqTotalChildren, 'infants' => $reqTotalInfants]
            ]);
        }

        // Override aggregated per-room totals with top-level totals to be authoritative
        $totalAdults = $reqTotalAdults;
        $totalChildren = $reqTotalChildren;
        $totalInfants = $reqTotalInfants;

        $totalUnits = ($totalAdults * 2) + $totalChildren + $totalInfants;
        $maxUnits = $totalSoLuong * 6; // base + extra

        if ($totalUnits > $maxUnits) {
            return back()->withErrors(['so_nguoi' => 'Tổng số khách vượt quá sức chứa tối đa theo số phòng đã chọn. Tối đa: ' . ($maxUnits/2) . ' người lớn tương đương.'])->withInput();
        }

        // compute extra counts
        $baseUnits = $totalSoLuong * 4;
        $totalExtraFee = $totalChildFee = $totalInfantFee = 0;
        if ($totalUnits > $baseUnits) {
            $adultsUnits = $totalAdults * 2;
            $adultsExtraUnits = max(0, $adultsUnits - $baseUnits);
            $unitsRemainingBase = max(0, $baseUnits - $adultsUnits);

            $childrenAssignedUnits = min($totalChildren, $unitsRemainingBase);
            $childrenExtra = max(0, $totalChildren - $childrenAssignedUnits);
            $unitsRemainingBase -= $childrenAssignedUnits;

            $infantsAssignedUnits = min($totalInfants, $unitsRemainingBase);
            $infantsExtra = max(0, $totalInfants - $infantsAssignedUnits);

            $extraAdultsCount = intdiv($adultsExtraUnits, 2);
            $extraChildrenCount = $childrenExtra;
            $extraInfantsCount = $infantsExtra;

            // Distribute extras proportional to room quantities
            $remainingAdults = $extraAdultsCount;
            $remainingChildren = $extraChildrenCount;
            $remainingInfants = $extraInfantsCount;

            foreach ($roomTypesArray as $idx => $rt) {
                $soLuong = $rt['so_luong'];
                $share = $soLuong / max(1, $totalSoLuong);

                if ($idx < count($roomTypesArray) - 1) {
                    $a = (int) round($extraAdultsCount * $share);
                    $c = (int) round($extraChildrenCount * $share);
                    $f = (int) round($extraInfantsCount * $share);
                } else {
                    $a = $remainingAdults;
                    $c = $remainingChildren;
                    $f = $remainingInfants;
                }

                $remainingAdults -= $a;
                $remainingChildren -= $c;
                $remainingInfants -= $f;

                $loai = LoaiPhong::find($rt['loai_phong_id']);
                if (!$loai) continue;

                $adultFee = BookingPriceCalculator::calculateExtraGuestSurcharge($loai, $checkIn, $checkOut, $a, $extraFeePercent);
                $childFee = BookingPriceCalculator::calculateChildSurcharge($loai, $checkIn, $checkOut, $c, $childFeePercent);
                $infantFee = BookingPriceCalculator::calculateInfantSurcharge($loai, $checkIn, $checkOut, $f, $infantFeePercent);

                $totalExtraFee += $adultFee;
                $totalChildFee += $childFee;
                $totalInfantFee += $infantFee;

                Log::info('DatPhong::update - surcharge per-type', [
                    'loai_phong_id' => $rt['loai_phong_id'] ?? null,
                    'extraAdults' => $a,
                    'extraChildren' => $c,
                    'extraInfants' => $f,
                    'adultFee' => $adultFee,
                    'childFee' => $childFee,
                    'infantFee' => $infantFee,
                ]);
            }
        }

        // Calculate voucher discount on room base total only (KHÔNG bao gồm phụ phí)
        $roomBaseTotal = $totalPrice - $totalExtraFee - $totalChildFee - $totalInfantFee;
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
                // sum only matching room types; otherwise use full room base total (không bao gồm phụ phí).
                $applicableTotal = 0;
                if (empty($voucher->loai_phong_id)) {
                    $applicableTotal = $roomBaseTotal;
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

        // Tổng cuối cùng: (room base - discount) + surcharges + services
        $finalTotal = max(0, ($roomBaseTotal - $voucherDiscount) + $totalExtraFee + $totalChildFee + $totalInfantFee + $totalServicePrice);

        Log::info('DatPhong::update - computed totals', [
            'roomBaseTotal' => $roomBaseTotal,
            'voucherDiscount' => $voucherDiscount,
            'totalExtraFee' => $totalExtraFee,
            'totalChildFee' => $totalChildFee,
            'totalInfantFee' => $totalInfantFee,
            'totalServicePrice' => $totalServicePrice,
            'finalTotal' => $finalTotal,
            'totalAdults' => $totalAdults,
            'totalChildren' => $totalChildren,
            'totalInfants' => $totalInfants,
        ]);

        // Support admin-selected specific rooms per room type
        $requestedRooms = $request->input('rooms', []);

        // Update booking và gán lại phòng trong transaction
        DB::transaction(function () use ($booking, $request, $roomTypes, $roomTypesArray, $totalSoLuong, $firstLoaiPhongId, $oldPhongIds, $servicesData, $finalTotal, $totalPrice, $totalServicePrice, $requestedRooms, $voucherDiscount, $totalExtraFee, $totalChildFee, $totalInfantFee, $totalAdults, $totalChildren, $totalInfants) {
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
                    // If admin provided explicit room IDs for this type, require exact number equal to requested quantity.
                    if (count($selectedForType) !== $soLuongCan) {
                        // Build friendly message and abort
                        $loai = LoaiPhong::find($loaiId);
                        $lname = $loai ? $loai->ten_loai : ($loaiId ?: 'N/A');
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'rooms' => ["Vui lòng chọn chính xác {$soLuongCan} phòng cho loại '{$lname}' (bạn đã chọn " . count($selectedForType) . ")."]
                        ]);
                    }

                    // Validate and lock each selected room; if any invalid, abort
                    foreach ($selectedForType as $phId) {
                        $ph = Phong::lockForUpdate()->find($phId);
                        if (!$ph) {
                            throw \Illuminate\Validation\ValidationException::withMessages([
                                'rooms' => ["Phòng (ID: {$phId}) không tồn tại."]
                            ]);
                        }
                        if ($ph->loai_phong_id != $loaiId) {
                            throw \Illuminate\Validation\ValidationException::withMessages([
                                'rooms' => ["Phòng " . ($ph->so_phong ?? $ph->id) . " không thuộc loại được yêu cầu."]
                            ]);
                        }
                        if (!$ph->isAvailableInPeriod($request->ngay_nhan, $request->ngay_tra, $booking->id)) {
                            throw \Illuminate\Validation\ValidationException::withMessages([
                                'rooms' => ["Phòng " . ($ph->so_phong ?? $ph->id) . " không trống trong khoảng thời gian này."]
                            ]);
                        }
                        if (!in_array($ph->id, $newPhongIds)) $newPhongIds[] = $ph->id;
                    }

                    // We used the admin-provided rooms and must NOT auto-assign any additional rooms.
                    continue;
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

                // If we reached here it means admin did NOT provide explicit selections for this type.
                // We will only keep existing assigned rooms of this type up to requested quantity.
                // We do NOT perform automatic assignment of new rooms here — admin must explicitly select rooms.
                if ($keptCount < $soLuongCan) {
                    $loai = LoaiPhong::find($loaiId);
                    $lname = $loai ? $loai->ten_loai : ($loaiId ?: 'N/A');
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'rooms' => ["Loại phòng '{$lname}' cần {$soLuongCan} phòng nhưng chỉ có {$keptCount} phòng hiện được giữ. Vui lòng chọn thêm phòng cụ thể cho loại này trên giao diện."]
                    ]);
                }
            }

            // 3. Update booking với thông tin mới (bao gồm tổng tiền đã cộng dịch vụ)
            // 3. Update booking với thông tin mới
            // Note: room_types, phong_ids được lưu trong bảng pivot thay vì JSON columns
            
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
                'so_luong_da_dat' => $totalSoLuong,
                'trang_thai' => $request->trang_thai ?? $booking->trang_thai,
                'ngay_nhan' => $request->ngay_nhan,
                'ngay_tra' => $request->ngay_tra,
                'so_nguoi' => $totalAdults,
                'so_tre_em' => $totalChildren,
                'so_em_be' => $totalInfants,
                'phu_phi_tre_em' => $totalChildFee,
                'phu_phi_em_be' => $totalInfantFee,
                'username' => $request->username,
                'email' => $request->email,
                'sdt' => $request->sdt,
                'cccd' => $request->cccd,
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

            // Debug: confirm persisted guest and surcharge fields
            Log::info('DatPhong::update - booking updated', [
                'booking_id' => $booking->id,
                'so_nguoi' => $booking->so_nguoi,
                'so_tre_em' => $booking->so_tre_em,
                'so_em_be' => $booking->so_em_be,
                'phu_phi_tre_em' => $booking->phu_phi_tre_em,
                'phu_phi_em_be' => $booking->phu_phi_em_be,
                'tong_tien' => $booking->tong_tien,
            ]);

            // Sync pivot table booking_room_types với loại phòng mới
            $roomTypesForSync = [];            foreach ($roomTypesArray as $rt) {
                $roomTypesForSync[$rt['loai_phong_id']] = [
                    'so_luong' => $rt['so_luong'],
                    'gia_rieng' => $rt['gia_rieng'],
                ];
            }
            $booking->syncRoomTypes($roomTypesForSync);

            // Sync pivot table booking_rooms với phòng được gán mới
            $booking->syncPhongs($newPhongIds);

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
                                        'invoice_id' => $booking->invoice?->id ?? null,
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
                                    'invoice_id' => $booking->invoice?->id ?? null,
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
                                    'invoice_id' => $booking->invoice?->id ?? null,
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

            // 5. Tính toán lại tổng tiền dịch vụ từ các BookingService vừa tạo và update lại tong_tien
            $recalculatedServiceTotal = \App\Models\BookingService::where('dat_phong_id', $booking->id)
                ->sum(DB::raw('quantity * unit_price'));
            
            // $totalPrice đã bao gồm phụ phí, chỉ cần cộng dịch vụ
            // Voucher chỉ áp dụng cho giá phòng gốc (không bao gồm phụ phí)
            $roomBaseTotal = $totalPrice - $totalExtraFee - $totalChildFee - $totalInfantFee;
            $finalRecalculated = max(0, ($roomBaseTotal - ($voucherDiscount ?? 0)) + $totalExtraFee + $totalChildFee + $totalInfantFee + $recalculatedServiceTotal);

            $updateData = [
                'tong_tien' => $finalRecalculated,
                'phu_phi_tre_em' => $totalChildFee,
                'phu_phi_em_be' => $totalInfantFee,
            ];
            if (Schema::hasColumn('dat_phong', 'tien_phong')) {
                $updateData['tien_phong'] = $totalPrice;
            }
            if (Schema::hasColumn('dat_phong', 'tien_dich_vu')) {
                $updateData['tien_dich_vu'] = $recalculatedServiceTotal;
            }
            
            $booking->update($updateData);

            // Debug: confirm persisted totals after update
            Log::info('DatPhong::update - after main update', [
                'booking_id' => $booking->id,
                'so_nguoi' => $booking->so_nguoi,
                'so_tre_em' => $booking->so_tre_em,
                'so_em_be' => $booking->so_em_be,
                'phu_phi_tre_em' => $booking->phu_phi_tre_em,
                'phu_phi_em_be' => $booking->phu_phi_em_be,
                'tong_tien' => $booking->tong_tien,
            ]);

            // Defensive guard: ensure persisted booking totals match the freshly computed final price
            if ((int) $booking->tong_tien !== (int) $finalRecalculated) {
                Log::warning('DatPhong::update - persisted total mismatch; correcting booking', [
                    'booking_id' => $booking->id,
                    'expected' => $finalRecalculated,
                    'actual' => $booking->tong_tien
                ]);
                $booking->update([
                    'tong_tien' => $finalRecalculated,
                    'phu_phi_tre_em' => $totalChildFee,
                    'phu_phi_em_be' => $totalInfantFee,
                    'tien_phong' => $totalPrice,
                    'tien_dich_vu' => $recalculatedServiceTotal,
                ]);
                Log::info('DatPhong::update - booking corrected', ['booking' => $booking->toArray()]);
            }
        });

        // Defensive: after transaction, ensure booking totals are consistent using the central calculator
        try {
            $booking = $booking->fresh();
            $before = [
                'so_nguoi' => $booking->so_nguoi,
                'so_tre_em' => $booking->so_tre_em,
                'so_em_be' => $booking->so_em_be,
                'phu_phi_tre_em' => $booking->phu_phi_tre_em,
                'phu_phi_em_be' => $booking->phu_phi_em_be,
                'tong_tien' => $booking->tong_tien,
            ];

            // Use the canonical recalculation routine to ensure totals are authoritative
            \App\Services\BookingPriceCalculator::recalcTotal($booking);

            $booking = $booking->fresh();
            $after = [
                'phu_phi_tre_em' => $booking->phu_phi_tre_em,
                'phu_phi_em_be' => $booking->phu_phi_em_be,
                'tong_tien' => $booking->tong_tien,
            ];

            if ($before['tong_tien'] !== $after['tong_tien'] || $before['phu_phi_tre_em'] !== $after['phu_phi_tre_em'] || $before['phu_phi_em_be'] !== $after['phu_phi_em_be']) {
                Log::warning('DatPhong::update - post-transaction recalculation changed persisted totals', [
                    'booking_id' => $booking->id,
                    'before' => $before,
                    'after' => $after,
                ]);
            } else {
                Log::info('DatPhong::update - post-transaction recalculation confirmed totals', ['booking_id' => $booking->id, 'totals' => $after]);
            }
        } catch (\Throwable $e) {
            Log::error('DatPhong::update - recalculation failed', ['error' => $e->getMessage(), 'booking_id' => $booking->id ?? null]);
        }

        // If admin chose to confirm after saving, invoke quickConfirm flow
        if ($request->input('confirm_and_save')) {
            // Refresh booking to ensure latest values
            $booking = $booking->fresh();
            return $this->quickConfirm($booking->id);
        }

        return redirect()->route('admin.dat_phong.show', $booking->id)
            ->with('success', 'Cập nhật thông tin đặt phòng thành công');
    }

    public function assignRoom(Request $request, $id)
    {
        // Nhân viên: gán dịch vụ vào phòng
        // Lễ tân: không được gán phòng
        if ($this->hasRole('nhan_vien')) {
            $this->authorizePermission('phong.assign_service');
        } elseif ($this->hasRole('le_tan')) {
            abort(403, 'Bạn không có quyền gán phòng.');
        }
        
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
        if (is_array($roomTypes) && count($roomTypes) > 1) {
            $allowedLoaiPhongIds = array_column($roomTypes, 'loai_phong_id');
        } else {
            // Fallback: query pivot table để lấy loại phòng
            $allowedLoaiPhongIds = $booking->roomTypes()->pluck('id')->toArray();
            if (empty($allowedLoaiPhongIds)) {
                $allowedLoaiPhongIds = [];
            }
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
        // Nhân viên: tạo đặt phòng
        // Lễ tân: tạo đặt phòng trực tiếp, nhận đặt phòng qua điện thoại
        if ($this->hasRole('nhan_vien')) {
            $this->authorizePermission('booking.create');
        } elseif ($this->hasRole('le_tan')) {
            $this->authorizePermission('booking.create_direct');
        }
        
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
        // Nhân viên: tạo đặt phòng
        // Lễ tân: tạo đặt phòng trực tiếp
        if ($this->hasRole('nhan_vien')) {
            $this->authorizePermission('booking.create');
        } elseif ($this->hasRole('le_tan')) {
            $this->authorizePermission('booking.create_direct');
        }
        
        // Validate room_types array first
        $request->validate([
            'room_types' => 'required|array|min:1',
            'room_types.*' => 'required|integer|exists:loai_phong,id',
            'ngay_nhan' => 'required|date|after_or_equal:today',
            'ngay_tra' => 'required|date|after:ngay_nhan',
            'so_nguoi' => 'required|integer|min:1|max:20',
            'so_tre_em' => 'nullable|integer|min:0|max:10',
            'so_em_be' => 'nullable|integer|min:0|max:5',
            'rooms.*.so_nguoi' => 'nullable|integer|min:0|max:100',
            'rooms.*.so_tre_em' => 'nullable|integer|min:0|max:100',
            'rooms.*.so_em_be' => 'nullable|integer|min:0|max:100',
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
            'so_nguoi.integer' => 'Số người phải là số nguyên',
            'so_nguoi.min' => 'Số người phải lớn hơn 0',
            'so_nguoi.max' => 'Số người không được lớn hơn 20',
            'so_tre_em.integer' => 'Số trẻ em phải là số nguyên',
            'so_tre_em.min' => 'Số trẻ em không được nhỏ hơn 0',
            'so_tre_em.max' => 'Số trẻ em không được lớn hơn 10',
            'so_em_be.integer' => 'Số em bé phải là số nguyên',
            'so_em_be.min' => 'Số em bé không được nhỏ hơn 0',
            'so_em_be.max' => 'Số em bé không được lớn hơn 5',
            'rooms.*.so_nguoi.integer' => 'Số người lớn phải là số nguyên',
            'rooms.*.so_nguoi.min' => 'Số người lớn không được nhỏ hơn 0',
            'rooms.*.so_tre_em.integer' => 'Số trẻ em phải là số nguyên',
            'rooms.*.so_tre_em.min' => 'Số trẻ em không được nhỏ hơn 0',
            'rooms.*.so_em_be.integer' => 'Số em bé phải là số nguyên',
            'rooms.*.so_em_be.min' => 'Số em bé không được nhỏ hơn 0',
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
        $checkIn = Carbon::parse($request->ngay_nhan);
        $checkOut = Carbon::parse($request->ngay_tra);

        // Validate each room type and check availability
        $totalPrice = 0;
        $roomDetails = [];
        $validationErrors = [];

        // Configuration for extra guest surcharges
        $maxAdultsPerRoom = 2;
        $extraFeePercent = 0.2; // 20% cho người lớn
        $childFeePercent = 0.1; // 10% cho trẻ em
        $infantFeePercent = 0.05; // 5% cho em bé

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

            // Tính tiền phòng theo từng ngày (ngày thường/cuối tuần/ngày lễ)
            $roomBaseTotal = BookingPriceCalculator::calculateRoomTypePriceByDateRange(
                $loaiPhong,
                $checkIn,
                $checkOut,
                (int) $room['so_luong']
            );
            
            // Use base room total only; surcharge amounts will be computed globally after aggregation
            $sumAdults = isset($room['so_nguoi']) ? (int) $room['so_nguoi'] : ($maxAdultsPerRoom * (int) $room['so_luong']);
            $sumChildren = isset($room['so_tre_em']) ? (int) $room['so_tre_em'] : 0;
            $sumInfants = isset($room['so_em_be']) ? (int) $room['so_em_be'] : 0;

            // Base room total (do not add surcharges here)
            $roomTotal = $roomBaseTotal;
            $totalPrice += $roomTotal;

            // Tổng số người lớn, trẻ em, em bé (for global surcharge allocation)
            $totalGuests = ($totalGuests ?? 0) + max(0, $sumAdults);
            $totalChildren = ($totalChildren ?? 0) + $sumChildren;
            $totalInfants = ($totalInfants ?? 0) + $sumInfants;

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
        
        // Khởi tạo các biến tổng nếu chưa có
        $totalGuests = $totalGuests ?? 0;
        $totalChildren = $totalChildren ?? 0;
        $totalInfants = $totalInfants ?? 0;
        $totalChildFee = $totalChildFee ?? 0;
        $totalInfantFee = $totalInfantFee ?? 0;

        // Log room-level details to help debug parity
        $roomSummary = array_map(function($rd){
            return [
                'loai_phong_id' => $rd['loai_phong_id'] ?? null,
                'so_luong' => $rd['so_luong'] ?? null,
                'price' => $rd['price'] ?? null,
            ];
        }, $roomDetails);
        Log::info('DatPhong::store - roomDetails summary', ['rooms' => $roomSummary, 'totalGuests' => $totalGuests, 'totalChildren'=> $totalChildren, 'totalInfants'=> $totalInfants]);
        // Also log raw rooms input from request (to detect mismatches between UI and posted values)
        Log::info('DatPhong::store - raw rooms input', ['rooms_input' => $request->input('rooms', [])]);

        // Prefer top-level totals (from inputs) if present so server-side calculation matches the client preview
        $reqTotalAdults = (int) ($request->input('so_nguoi') ?? $totalGuests);
        $reqTotalChildren = (int) ($request->input('so_tre_em') ?? $totalChildren);
        $reqTotalInfants = (int) ($request->input('so_em_be') ?? $totalInfants);
        if ($reqTotalAdults !== ($totalGuests ?? 0) || $reqTotalChildren !== ($totalChildren ?? 0) || $reqTotalInfants !== ($totalInfants ?? 0)) {
            Log::warning('DatPhong::store - mismatch between per-room sums and top-level totals', [
                'per_room' => ['adults' => $totalGuests ?? 0, 'children' => $totalChildren ?? 0, 'infants' => $totalInfants ?? 0],
                'top_level' => ['adults' => $reqTotalAdults, 'children' => $reqTotalChildren, 'infants' => $reqTotalInfants]
            ]);
        }

        // Override aggregated per-room totals with top-level totals to be authoritative
        $totalGuests = $reqTotalAdults;
        $totalChildren = $reqTotalChildren;
        $totalInfants = $reqTotalInfants;

        // Compute surcharges based on totals using units approach (adult=2, child=1, infant=1)
        $totalSoLuong = array_sum(array_column($roomDetails, 'so_luong'));
        $totalUnits = ($totalGuests * 2) + $totalChildren + $totalInfants;
        $baseUnits = $totalSoLuong * 4;
        $maxUnits = $totalSoLuong * 6;

        if ($totalUnits > $maxUnits) {
            return back()->withErrors(['so_nguoi' => 'Tổng số khách vượt quá sức chứa tối đa theo số phòng đã chọn.'])->withInput();
        }

        $totalExtraFee = 0;
        $totalChildFee = 0;
        $totalInfantFee = 0;

        if ($totalUnits > $baseUnits) {
            $adultsUnits = $totalGuests * 2;
            $adultsExtraUnits = max(0, $adultsUnits - $baseUnits);
            $unitsRemainingBase = max(0, $baseUnits - $adultsUnits);

            $childrenAssignedUnits = min($totalChildren, $unitsRemainingBase);
            $childrenExtra = max(0, $totalChildren - $childrenAssignedUnits);
            $unitsRemainingBase -= $childrenAssignedUnits;

            $infantsAssignedUnits = min($totalInfants, $unitsRemainingBase);
            $infantsExtra = max(0, $totalInfants - $infantsAssignedUnits);

            $extraAdultsCount = intdiv($adultsExtraUnits, 2);
            $extraChildrenCount = $childrenExtra;
            $extraInfantsCount = $infantsExtra;

            // Distribute extras proportional to room revenue
            $remainingAdults = $extraAdultsCount;
            $remainingChildren = $extraChildrenCount;
            $remainingInfants = $extraInfantsCount;
            $sumRoomRevenue = array_sum(array_column($roomDetails, 'price'));

            foreach ($roomDetails as $idx => $rd) {
                $soLuong = $rd['so_luong'];
                $share = ($sumRoomRevenue > 0) ? ($rd['price'] / $sumRoomRevenue) : ($soLuong / max(1, $totalSoLuong));

                if ($idx < count($roomDetails) - 1) {
                    $a = (int) round($extraAdultsCount * $share);
                    $c = (int) round($extraChildrenCount * $share);
                    $f = (int) round($extraInfantsCount * $share);
                } else {
                    $a = $remainingAdults;
                    $c = $remainingChildren;
                    $f = $remainingInfants;
                }

                $remainingAdults -= $a;
                $remainingChildren -= $c;
                $remainingInfants -= $f;

                $loai = LoaiPhong::find($rd['loai_phong_id']);
                if (!$loai) continue;

                $totalExtraFee += BookingPriceCalculator::calculateExtraGuestSurcharge($loai, $checkIn, $checkOut, $a, $extraFeePercent);
                $totalChildFee += BookingPriceCalculator::calculateChildSurcharge($loai, $checkIn, $checkOut, $c, $childFeePercent);
                $totalInfantFee += BookingPriceCalculator::calculateInfantSurcharge($loai, $checkIn, $checkOut, $f, $infantFeePercent);
            }
        }

        // Now apply voucher (only if provided) - voucher applies to room base total only (not surcharges nor services)
        $voucherId = null;
        $roomSubtotal = $totalPrice; // Tổng tiền phòng chưa giảm (base only)
        $roomDiscount = 0;
        $roomNetTotal = $roomSubtotal;

        if ($request->voucher) {
            // Try to find the voucher by code first
            $voucher = Voucher::where('ma_voucher', $request->voucher)->first();

            if ($voucher) {
                $checkin = $checkIn;
                $vStart = null; $vEnd = null;
                try { $vStart = \Carbon\Carbon::parse($voucher->ngay_bat_dau)->startOfDay(); } catch (\Exception $e) { $vStart = null; }
                try { $vEnd = \Carbon\Carbon::parse($voucher->ngay_ket_thuc)->startOfDay(); } catch (\Exception $e) { $vEnd = null; }

                $validNow = ($voucher->so_luong > 0) && ($voucher->trang_thai === 'con_han');
                $dateOk = true;
                if ($vStart && $vEnd && $checkin) { if ($checkin->lt($vStart) || $checkin->gt($vEnd)) $dateOk = false; }

                if (! $validNow || ! $dateOk) {
                    return back()->withErrors(['voucher' => 'Mã giảm giá không áp dụng cho ngày nhận phòng đã chọn hoặc không khả dụng'])->withInput();
                }

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
                        $roomDiscount = ($applicableTotal * $discountValue) / 100;
                    } else {
                        $roomDiscount = min($discountValue, $applicableTotal);
                    }

                    $roomNetTotal = max(0, $roomSubtotal - $roomDiscount);
                    $voucherId = $voucher->id;
                    // decrement available quantity
                    try { $voucher->decrement('so_luong'); } catch (\Exception $e) { Log::warning('Failed to decrement voucher quantity: ' . $e->getMessage()); }
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

        // Validate totals against room capacity (units: adult=2, child=1, infant=1)
        $totalAdults = (int) ($request->input('so_nguoi') ?? 0);
        $totalChildren = (int) ($request->input('so_tre_em') ?? 0);
        $totalInfants = (int) ($request->input('so_em_be') ?? 0);
        $totalUnits = ($totalAdults * 2) + $totalChildren + $totalInfants;
        $maxUnits = $totalSoLuong * 6; // base 4 + extra 2 units per room

        if ($totalUnits > $maxUnits) {
            return back()->withErrors(['so_nguoi' => 'Tổng số khách vượt quá sức chứa tối đa theo số phòng đã chọn. Tối đa: ' . ($maxUnits/2) . ' người lớn tương đương.'])->withInput();
        }

        // Compute extra surcharge counts (only for units exceeding base capacity) and calculate per-type surcharges
        $baseUnits = $totalSoLuong * 4; // 2 adults * 2 units
        $extraAdultsCount = $extraChildrenCount = $extraInfantsCount = 0;
        $totalExtraFee = $totalChildFee = $totalInfantFee = 0;

        if ($totalUnits > $baseUnits) {
            $adultsUnits = $totalAdults * 2;
            $adultsExtraUnits = max(0, $adultsUnits - $baseUnits);
            $unitsRemainingBase = max(0, $baseUnits - $adultsUnits);

            $childrenAssignedUnits = min($totalChildren, $unitsRemainingBase);
            $childrenExtra = max(0, $totalChildren - $childrenAssignedUnits);
            $unitsRemainingBase -= $childrenAssignedUnits;

            $infantsAssignedUnits = min($totalInfants, $unitsRemainingBase);
            $infantsExtra = max(0, $totalInfants - $infantsAssignedUnits);

            $extraAdultsCount = intdiv($adultsExtraUnits, 2);
            $extraChildrenCount = $childrenExtra;
            $extraInfantsCount = $infantsExtra;

            // Distribute extras to room types proportionally by quantity
            $remainingAdults = $extraAdultsCount;
            $remainingChildren = $extraChildrenCount;
            $remainingInfants = $extraInfantsCount;

            foreach ($roomTypesArray as $idx => $rt) {
                $soLuong = $rt['so_luong'];
                $share = $soLuong / max(1, $totalSoLuong);

                if ($idx < count($roomTypesArray) - 1) {
                    $a = (int) round($extraAdultsCount * $share);
                    $c = (int) round($extraChildrenCount * $share);
                    $f = (int) round($extraInfantsCount * $share);
                } else {
                    $a = $remainingAdults;
                    $c = $remainingChildren;
                    $f = $remainingInfants;
                }

                $remainingAdults -= $a;
                $remainingChildren -= $c;
                $remainingInfants -= $f;

                $loai = LoaiPhong::find($rt['loai_phong_id']);
                if (!$loai) continue;

                $adultFee = BookingPriceCalculator::calculateExtraGuestSurcharge($loai, $checkIn, $checkOut, $a, $extraFeePercent);
                $childFee = BookingPriceCalculator::calculateChildSurcharge($loai, $checkIn, $checkOut, $c, $childFeePercent);
                $infantFee = BookingPriceCalculator::calculateInfantSurcharge($loai, $checkIn, $checkOut, $f, $infantFeePercent);

                $totalExtraFee += $adultFee;
                $totalChildFee += $childFee;
                $totalInfantFee += $infantFee;

                Log::info('DatPhong::store - surcharge per-type', [
                    'loai_phong_id' => $rt['loai_phong_id'] ?? null,
                    'extraAdults' => $a,
                    'extraChildren' => $c,
                    'extraInfants' => $f,
                    'adultFee' => $adultFee,
                    'childFee' => $childFee,
                    'infantFee' => $infantFee,
                ]);
            }
        }

        // Cộng phụ phí vào cuối cùng
        $finalPrice = $roomNetTotal + $totalServicePrice + $totalExtraFee + $totalChildFee + $totalInfantFee;

        // Debug logging: record computed components before persisting
        Log::info('DatPhong::store - computed totals', [
            'roomSubtotal' => $roomSubtotal,
            'roomNetTotal' => $roomNetTotal,
            'roomDiscount' => $roomDiscount,
            'totalServicePrice' => $totalServicePrice,
            'totalExtraFee' => $totalExtraFee,
            'totalChildFee' => $totalChildFee,
            'totalInfantFee' => $totalInfantFee,
            'finalPrice' => $finalPrice,
            'totalGuests' => $totalGuests,
            'totalChildren' => $totalChildren,
            'totalInfants' => $totalInfants,
        ]);

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
            $normalizedServices,
            $totalGuests,
            $totalChildren,
            $totalInfants,
            $totalExtraFee,
            $totalChildFee,
            $totalInfantFee
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
            // Note: loai_phong_id, phong_id, room_types, phong_ids được lưu trong bảng pivot thay vì JSON columns
            // Set loai_phong_id là loại phòng đầu tiên để đảm bảo backward compatibility
            $bookingData = [
                'nguoi_dung_id' => Auth::id(),
                'loai_phong_id' => $firstLoaiPhongId, // Loại phòng đầu tiên (cho backward compatibility)
                'so_luong_da_dat' => $totalSoLuong, // Tổng số lượng phòng
                'ngay_dat' => now(),
                'ngay_nhan' => $request->ngay_nhan,
                'ngay_tra' => $request->ngay_tra,
                'so_nguoi' => $totalGuests, // Tổng số người lớn từ các phòng
                'so_tre_em' => $totalChildren,
                'so_em_be' => $totalInfants,
                'phu_phi_tre_em' => $totalChildFee,
                'phu_phi_em_be' => $totalInfantFee,
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

            // Log booking saved values for debugging parity issues
            Log::info('DatPhong::store - booking created', [
                'booking_id' => $booking->id,
                'tong_tien' => $booking->tong_tien,
                'tien_phong' => $booking->tien_phong ?? null,
                'tien_dich_vu' => $booking->tien_dich_vu ?? null,
                'phu_phi_tre_em' => $booking->phu_phi_tre_em ?? null,
                'phu_phi_em_be' => $booking->phu_phi_em_be ?? null,
                'voucher_id' => $booking->voucher_id ?? null,
            ]);

            // Defensive guard: if persisted total differs from computed finalPrice, log and correct it
            try {
                $persisted = (float) ($booking->tong_tien ?? 0);
                $expected = (float) $finalPrice;
                if (abs($persisted - $expected) > 0.0001) {
                    Log::warning('DatPhong::store - persisted total mismatch; correcting booking', [
                        'booking_id' => $booking->id,
                        'persisted' => $persisted,
                        'expected' => $expected,
                    ]);
                    $booking->update([
                        'tong_tien' => $expected,
                        'phu_phi_tre_em' => $totalChildFee,
                        'phu_phi_em_be' => $totalInfantFee,
                        'tien_phong' => $roomNetTotal,
                        'tien_dich_vu' => $totalServicePrice,
                    ]);
                    Log::info('DatPhong::store - booking corrected', ['booking_id' => $booking->id, 'new_tong_tien' => $booking->tong_tien]);
                }
            } catch (\Throwable $e) {
                Log::error('DatPhong::store - failed to autocorrect booking total', ['error' => $e->getMessage(), 'booking_id' => $booking->id]);
            }

            // Lưu room_types vào bảng pivot booking_room_types
            $roomTypesForSync = [];
            foreach ($roomTypesArray as $rt) {
                $roomTypesForSync[$rt['loai_phong_id']] = [
                    'so_luong' => $rt['so_luong'],
                    'gia_rieng' => $rt['gia_rieng'],
                ];
            }
            $booking->syncRoomTypes($roomTypesForSync);

            // Gán phòng cho tất cả các loại phòng
            $allPhongIds = [];
            $requestedRooms = $request->input('rooms', []);
            foreach ($roomDetails as $roomDetail) {
                $loaiPhong = LoaiPhong::find($roomDetail['loai_phong_id']);
                $phongIdsForThisType = []; // Đếm riêng cho từng loại phòng

                // Nếu admin đã chọn phòng cụ thể cho loại phòng này, ưu tiên dùng các phòng đó
                $selectedForType = $requestedRooms[$loaiPhong->id]['phong_ids'] ?? null;
                if (is_array($selectedForType) && count($selectedForType) > 0) {
                    $addedCount = 0;
                    foreach ($selectedForType as $phongId) {
                        if ($addedCount >= $roomDetail['so_luong']) break; // Stop when enough rooms added
                        $phongLocked = Phong::lockForUpdate()->find($phongId);
                        if (!$phongLocked) continue;
                        // Kiểm tra phòng thuộc loại phòng tương ứng
                        if ($phongLocked->loai_phong_id != $loaiPhong->id) continue;
                        // Kiểm tra phòng có thực sự trống trong khoảng thời gian
                        if (!$phongLocked->isAvailableInPeriod($request->ngay_nhan, $request->ngay_tra, $booking->id)) continue;
                        $phongIdsForThisType[] = $phongLocked->id;
                        $allPhongIds[] = $phongLocked->id;
                        $addedCount++;
                    }

                    if (count($phongIdsForThisType) >= $roomDetail['so_luong']) {
                        // Enough rooms selected and added - continue to next room type
                        continue;
                    }
                    // Else: admin selected some rooms but not enough, will auto-assign remaining below
                }

                // Nếu cần thêm phòng, tìm phòng mới
                $soLuongCanThem = $roomDetail['so_luong'] - count($phongIdsForThisType);
                if ($soLuongCanThem > 0) {
                    $availableRooms = Phong::findAvailableRooms(
                        $loaiPhong->id,
                        $request->ngay_nhan,
                        $request->ngay_tra,
                        $soLuongCanThem, // Tìm đủ số lượng phòng cần thiết
                        $booking->id // Exclude booking hiện tại
                    )->values();

                    $addedCount = 0;
                    foreach ($availableRooms as $phong) {
                        if ($addedCount >= $soLuongCanThem) break; // Stop when enough added
                        // Lock phòng trước khi gán để tránh race condition
                        $phongLocked = Phong::lockForUpdate()->find($phong->id);
                        if (!$phongLocked) {
                            continue;
                        }

                        // Double-check availability sau khi lock
                        if ($phongLocked->isAvailableInPeriod($request->ngay_nhan, $request->ngay_tra, $booking->id)) {
                            $allPhongIds[] = $phongLocked->id;
                            $phongIdsForThisType[] = $phongLocked->id;
                            $addedCount++;
                        }
                    }
                }

                // Kiểm tra xem đã gán đủ phòng cho loại phòng này chưa
                if (count($phongIdsForThisType) < $roomDetail['so_luong']) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'error' => "Không thể gán đủ {$roomDetail['so_luong']} phòng cho loại phòng '{$loaiPhong->ten_loai}'. Chỉ gán được " . count($phongIdsForThisType) . " phòng. Vui lòng thử lại."
                    ]);
                }
            }

            // Lưu tất cả phong_ids vào bảng pivot booking_rooms
            $booking->syncPhongs($allPhongIds);

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
                                        'invoice_id' => $booking->invoice?->id ?? null,
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
                                    'invoice_id' => $booking->invoice?->id ?? null,
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
                                    'invoice_id' => $booking->invoice?->id ?? null,
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
        // Chỉ admin mới được chống phòng
        if (!$this->hasRole('admin')) {
            abort(403, 'Bạn không có quyền chống phòng.');
        }
        
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
     * Nhân viên: Xác nhận thanh toán đặt cọc
     */
    public function quickConfirm($id)
    {
        // Nhân viên: xác nhận thanh toán đặt cọc
        // Lễ tân: không được xác nhận
        if ($this->hasRole('nhan_vien')) {
            $this->authorizePermission('booking.confirm_deposit');
        } elseif ($this->hasRole('le_tan')) {
            abort(403, 'Bạn không có quyền xác nhận đặt phòng.');
        }
        
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

        $checkIn = Carbon::parse($booking->ngay_nhan);
        $checkOut = Carbon::parse($booking->ngay_tra);

        // Get room types
        $roomTypes = $booking->getRoomTypes();
        $totalPrice = 0;
        $totalExtraFee = 0;
        $totalChildFee = 0;
        $totalInfantFee = 0;
        $roomTypesArray = [];
        $totalSoLuong = 0;

        $maxAdultsPerRoom = 2;
        $extraFeePercent = 0.2; // 20% cho người lớn
        $childFeePercent = 0.1; // 10% cho trẻ em
        $infantFeePercent = 0.05; // 5% cho em bé

        // Tính tổng số phòng trước
        foreach ($roomTypes as $roomType) {
            $totalSoLuong += ($roomType['so_luong'] ?? 1);
        }

        // Calculate room prices với multiplier và phụ phí
        foreach ($roomTypes as $roomType) {
            $soLuong = $roomType['so_luong'] ?? 1;
            $loaiPhongId = $roomType['loai_phong_id'];
            $loaiPhong = LoaiPhong::find($loaiPhongId);
            if (!$loaiPhong) continue;

            // Tính tiền phòng theo từng ngày (ngày thường/cuối tuần/ngày lễ)
            $roomBaseTotal = BookingPriceCalculator::calculateRoomTypePriceByDateRange(
                $loaiPhong,
                $checkIn,
                $checkOut,
                $soLuong
            );
            
// compute base total only; surcharges will be computed globally after loop
            $roomTotal = $roomBaseTotal;
            $totalPrice += $roomTotal;

            $roomTypesArray[] = [
                'loai_phong_id' => $loaiPhongId,
                'so_luong' => $soLuong,
                'gia_rieng' => $roomBaseTotal,
            ];

            $roomTypesArray[] = [
                'loai_phong_id' => $loaiPhongId,
                'so_luong' => $soLuong,
                'gia_rieng' => $roomBaseTotal, // Chỉ lưu giá phòng gốc (để tính voucher)
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

        // Final total: room price (bao gồm phụ phí) + service price - voucher discount
        // Voucher chỉ áp dụng cho tiền phòng gốc (không bao gồm phụ phí)
        $roomBaseTotal = $totalPrice - $totalExtraFee - $totalChildFee - $totalInfantFee;
        $applicableForVoucher = $roomBaseTotal; // Voucher chỉ áp dụng cho giá phòng gốc
        
        // Tính lại voucher discount dựa trên giá phòng gốc
        $voucherDiscount = 0;
        if ($booking->voucher_id && $booking->voucher) {
            $voucher = $booking->voucher;
            if ($voucher->gia_tri) {
                if (empty($voucher->loai_phong_id)) {
                    $applicableForVoucher = $roomBaseTotal;
                } else {
                    $applicableForVoucher = 0;
                    foreach ($roomTypesArray as $rt) {
                        if (isset($rt['loai_phong_id']) && $rt['loai_phong_id'] == $voucher->loai_phong_id) {
                            $applicableForVoucher += $rt['gia_rieng'];
                        }
                    }
                }
                
                if ($applicableForVoucher > 0) {
                    if ($voucher->gia_tri <= 100) {
                        $voucherDiscount = intval(round($applicableForVoucher * ($voucher->gia_tri / 100)));
                    } else {
                        $voucherDiscount = intval(min(round($voucher->gia_tri), $applicableForVoucher));
                    }
                }
            }
        }
        
        // --- Compute surcharges (units logic like update()) ---
        $totalAdults = $booking->so_nguoi ?? ($maxAdultsPerRoom * $totalSoLuong);
        $totalChildren = $booking->so_tre_em ?? 0;
        $totalInfants = $booking->so_em_be ?? 0;

        $totalUnits = ($totalAdults * 2) + $totalChildren + $totalInfants;
        $baseUnits = $totalSoLuong * 4;

        if ($totalUnits > $baseUnits) {
            $adultsUnits = $totalAdults * 2;
            $adultsExtraUnits = max(0, $adultsUnits - $baseUnits);
            $unitsRemainingBase = max(0, $baseUnits - $adultsUnits);

            $childrenAssignedUnits = min($totalChildren, $unitsRemainingBase);
            $childrenExtra = max(0, $totalChildren - $childrenAssignedUnits);
            $unitsRemainingBase -= $childrenAssignedUnits;

            $infantsAssignedUnits = min($totalInfants, $unitsRemainingBase);
            $infantsExtra = max(0, $totalInfants - $infantsAssignedUnits);

            $extraAdultsCount = intdiv($adultsExtraUnits, 2);
            $extraChildrenCount = $childrenExtra;
            $extraInfantsCount = $infantsExtra;

            // Distribute extras proportional to room quantities
            $remainingAdults = $extraAdultsCount;
            $remainingChildren = $extraChildrenCount;
            $remainingInfants = $extraInfantsCount;

            foreach ($roomTypesArray as $idx => $rt) {
                $soLuong = $rt['so_luong'];
                $share = $soLuong / max(1, $totalSoLuong);

                if ($idx < count($roomTypesArray) - 1) {
                    $a = (int) round($extraAdultsCount * $share);
                    $c = (int) round($extraChildrenCount * $share);
                    $f = (int) round($extraInfantsCount * $share);
                } else {
                    $a = $remainingAdults;
                    $c = $remainingChildren;
                    $f = $remainingInfants;
                }

                $remainingAdults -= $a;
                $remainingChildren -= $c;
                $remainingInfants -= $f;

                $loai = LoaiPhong::find($rt['loai_phong_id']);
                if (!$loai) continue;

                $totalExtraFee += BookingPriceCalculator::calculateExtraGuestSurcharge($loai, $checkIn, $checkOut, $a, $extraFeePercent);
                $totalChildFee += BookingPriceCalculator::calculateChildSurcharge($loai, $checkIn, $checkOut, $c, $childFeePercent);
                $totalInfantFee += BookingPriceCalculator::calculateInfantSurcharge($loai, $checkIn, $checkOut, $f, $infantFeePercent);

                Log::info('DatPhong::quickConfirm - surcharge per-type', [
                    'loai_phong_id' => $rt['loai_phong_id'] ?? null,
                    'extraAdults' => $a,
                    'extraChildren' => $c,
                    'extraInfants' => $f,
                ]);
            }
        }

        // Final total: (room base - discount) + surcharges + services
        $finalTotal = max(0, ($roomBaseTotal - $voucherDiscount) + $totalExtraFee + $totalChildFee + $totalInfantFee + $totalServicePrice);

        // Update booking with calculated totals (include surcharge columns)
        $updateData = [
            'tong_tien' => $finalTotal,
            'phu_phi_tre_em' => $totalChildFee,
            'phu_phi_em_be' => $totalInfantFee,
        ];
        if (Schema::hasColumn('dat_phong', 'tien_phong')) {
            $updateData['tien_phong'] = $totalPrice;
        }
        if (Schema::hasColumn('dat_phong', 'tien_dich_vu')) {
            $updateData['tien_dich_vu'] = $totalServicePrice;
        }
        
        $booking->update($updateData);

        Log::info('DatPhong::quickConfirm - computed totals', [
            'totalExtraFee' => $totalExtraFee,
            'totalChildFee' => $totalChildFee,
            'totalInfantFee' => $totalInfantFee,
            'finalTotal' => $finalTotal,
        ]);

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
                $invoice = \App\Models\Invoice::create($invoiceData);
                // Link any existing booking-level services (invoice_id == NULL) to this new invoice
                if ($invoice && $invoice->id) {
                    try {
                        \App\Models\BookingService::where('dat_phong_id', $booking->id)
                            ->whereNull('invoice_id')
                            ->update(['invoice_id' => $invoice->id]);
                    } catch (\Throwable $e) {
                        Log::warning('Failed to link booking services to new invoice on confirmation: ' . $e->getMessage());
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to create invoice on confirmation: ' . $e->getMessage());
            }
        }

        // Ensure any booking-level services are assigned to the booking's invoice (even if invoice pre-existed)
        $linkedInvoiceId = $booking->invoice?->id ?? (isset($invoice) ? $invoice->id : null);
        if ($linkedInvoiceId) {
            try {
                \App\Models\BookingService::where('dat_phong_id', $booking->id)
                    ->whereNull('invoice_id')
                    ->update(['invoice_id' => $linkedInvoiceId]);
            } catch (\Throwable $e) {
                Log::warning('Failed to link booking services to invoice on confirmation: ' . $e->getMessage());
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
     * Nhân viên: Xác nhận thanh toán đặt cọc
     */
    public function markPaid($id)
    {
        // Nhân viên: xác nhận thanh toán đặt cọc
        // Lễ tân: không được đánh dấu đã thanh toán
        if ($this->hasRole('nhan_vien')) {
            $this->authorizePermission('booking.confirm_deposit');
        } elseif ($this->hasRole('le_tan')) {
            abort(403, 'Bạn không có quyền đánh dấu đã thanh toán.');
        }
        
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
            // Link booking-level services to this newly created invoice
            try {
                \App\Models\BookingService::where('dat_phong_id', $booking->id)
                    ->whereNull('invoice_id')
                    ->update(['invoice_id' => $invoice->id]);
            } catch (\Throwable $e) {
                Log::warning('Failed to link booking services to new invoice on markPaid: ' . $e->getMessage());
            }
        } else {
            $invoice->update([
                'trang_thai' => 'da_thanh_toan',
            ]);
            // Ensure any booking-level services are assigned to this invoice
            try {
                \App\Models\BookingService::where('dat_phong_id', $booking->id)
                    ->whereNull('invoice_id')
                    ->update(['invoice_id' => $invoice->id]);
            } catch (\Throwable $e) {
                Log::warning('Failed to link booking services to existing invoice on markPaid: ' . $e->getMessage());
            }
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
     * Lễ tân: Check-in khách đặt trước
     */
    public function checkin(Request $request, $id)
    {
        // Lễ tân: check-in
        if ($this->hasRole('le_tan')) {
            $this->authorizePermission('phong.checkin');
        }
        
        $validated = $request->validate([
            'ghi_chu_checkin' => 'nullable|string|max:500',
            'phong_ids' => 'required|array',
            'phong_ids.*' => 'integer',
        ], [
            'ghi_chu_checkin.max' => 'Ghi chú không được vượt quá 500 ký tự',
            'phong_ids.required' => 'Vui lòng chọn ít nhất một phòng để check-in',
        ]);

        try {
            DB::transaction(function () use ($id, $validated) {
                $booking = DatPhong::lockForUpdate()->findOrFail($id);

                // Validate booking trạng thái
                if ($booking->trang_thai !== 'da_xac_nhan') {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'error' => 'Không thể check-in. Booking phải ở trạng thái "Đã xác nhận".'
                    ]);
                }

                // Lọc danh sách phòng hợp lệ để check-in
                $phongIds = $validated['phong_ids'] ?? [];
                $phongsToCheckin = $booking->phongs()->whereIn('phong_id', $phongIds)->get();

                if ($phongsToCheckin->isEmpty()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'error' => 'Các phòng được chọn để check-in không hợp lệ.'
                    ]);
                }

                // Không cho phép check-in lại những phòng đã có thoi_gian_checkin trên pivot
                $allAlreadyCheckedIn = $phongsToCheckin->every(function ($phong) {
                    return !is_null($phong->pivot->thoi_gian_checkin);
                });

                if ($allAlreadyCheckedIn) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'error' => 'Tất cả phòng được chọn đã được check-in trước đó.'
                    ]);
                }

                // Tính phụ phí check-in sớm (nếu có) cho booking
                $checkinTime = now();
                $phiCheckinSom = \App\Services\CheckinCheckoutFeeCalculator::calculateEarlyCheckinFee($booking, $checkinTime);

                // Build ghi chú check-in
                $ghiChuCheckin = $validated['ghi_chu_checkin'] ?? '';
                if ($phiCheckinSom > 0) {
                    $ghiChuCheckin .= ($ghiChuCheckin ? "\n" : '') . "Phụ phí check-in sớm: " . number_format($phiCheckinSom, 0, ',', '.') . " VNĐ";
                }

                // Cập nhật phi_phat_sinh (cộng dồn với phụ phí check-in sớm)
                $phiPhatSinhHienTai = $booking->phi_phat_sinh ?? 0;
                $phiPhatSinhMoi = $phiPhatSinhHienTai + $phiCheckinSom;

                // Update booking (ghi nhận thời gian checkin đầu tiên nếu chưa có)
                if (!$booking->thoi_gian_checkin) {
                    $booking->thoi_gian_checkin = $checkinTime;
                    $booking->nguoi_checkin = Auth::user()->ho_ten;
                }

                $booking->ghi_chu_checkin = trim($ghiChuCheckin);
                $booking->phi_phat_sinh = $phiPhatSinhMoi;
                $booking->save();

                // Cập nhật tổng tiền booking và invoice
                \App\Services\BookingPriceCalculator::recalcTotal($booking);

                // Update room status to 'dang_thue' cho từng phòng được chọn (chỉ những phòng chưa checkin)
                foreach ($phongsToCheckin as $phong) {
                    if (is_null($phong->pivot->thoi_gian_checkin)) {
                        // Cập nhật trạng thái phòng tổng
                        $phong->update(['trang_thai' => 'dang_thue']);

                        // Cập nhật thông tin theo từng phòng trên pivot
                        $booking->phongs()->updateExistingPivot($phong->id, [
                            'thoi_gian_checkin' => $checkinTime,
                            'trang_thai_phong' => 'da_checkin',
                        ]);
                    }
                }

                Log::info('Booking checked in', [
                    'booking_id' => $booking->id,
                    'staff' => Auth::user()->ho_ten,
                    'phi_checkin_som' => $phiCheckinSom,
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
     * Lễ tân: Check-out
     */
    public function checkout(Request $request, $id)
    {
        // Lễ tân: check-out
        if ($this->hasRole('le_tan')) {
            $this->authorizePermission('phong.checkout');
        }
        
        $validated = $request->validate([
            'phi_phat_sinh' => 'nullable|numeric|min:0',
            'ly_do_phi' => 'nullable|string|max:500',
            'loai_thiet_hai' => 'nullable|string|max:100',
            'ghi_chu_checkout' => 'nullable|string|max:500',
            'phong_ids' => 'required|array',
            'phong_ids.*' => 'integer',
        ], [
            'phi_phat_sinh.numeric' => 'Phụ phí phải là số',
            'phi_phat_sinh.min' => 'Phụ phí không được âm',
            'ly_do_phi.max' => 'Lý do không được vượt quá 500 ký tự',
            'loai_thiet_hai.max' => 'Danh mục thiệt hại không được vượt quá 100 ký tự',
            'ghi_chu_checkout.max' => 'Ghi chú không được vượt quá 500 ký tự',
            'phong_ids.required' => 'Vui lòng chọn ít nhất một phòng để check-out',
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

                // Lọc danh sách phòng hợp lệ để check-out
                $phongIds = $validated['phong_ids'] ?? [];
                $phongsToCheckout = $booking->phongs()->whereIn('phong_id', $phongIds)->get();

                if ($phongsToCheckout->isEmpty()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'error' => 'Các phòng được chọn để check-out không hợp lệ.'
                    ]);
                }

                // Phòng hợp lệ để checkout phải đã checkin và chưa checkout trên pivot
                $allInvalidForCheckout = $phongsToCheckout->every(function ($phong) {
                    return is_null($phong->pivot->thoi_gian_checkin) || !is_null($phong->pivot->thoi_gian_checkout);
                });

                if ($allInvalidForCheckout) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'error' => 'Tất cả phòng được chọn không ở trạng thái có thể check-out.'
                    ]);
                }

                // Tính phụ phí check-out trễ (nếu có)
                $checkoutTime = now();
                $phiCheckoutTre = \App\Services\CheckinCheckoutFeeCalculator::calculateLateCheckoutFee($booking, $checkoutTime);

                // Lấy phụ phí hiện tại (có thể đã có phụ phí check-in sớm)
                $phiPhatSinhHienTai = $booking->phi_phat_sinh ?? 0;

                // Phụ phí thiệt hại tài sản từ form
                $phiThietHai = (float)($validated['phi_phat_sinh'] ?? 0);

                // Tổng phụ phí = phụ phí hiện tại + phụ phí check-out trễ + phụ phí thiệt hại
                $tongPhiPhatSinh = $phiPhatSinhHienTai + $phiCheckoutTre + $phiThietHai;

                // Build checkout note
                $ghiChuCheckout = $validated['ghi_chu_checkout'] ?? '';

                if ($phiCheckoutTre > 0) {
                    $ghiChuCheckout .= ($ghiChuCheckout ? "\n" : '') . "Phụ phí check-out trễ: " . number_format($phiCheckoutTre, 0, ',', '.') . " VNĐ";
                }

                if ($phiThietHai > 0) {
                    $ghiChuCheckout .= ($ghiChuCheckout ? "\n" : '') . "Phụ phí thiệt hại: " . number_format($phiThietHai, 0, ',', '.') . " VNĐ";
                    if (!empty($validated['ly_do_phi'])) {
                        $ghiChuCheckout .= "\n[LY_DO_PHI: " . $validated['ly_do_phi'] . "]";
                    }
                    if (!empty($validated['loai_thiet_hai'])) {
                        $ghiChuCheckout .= "\nDanh mục: " . $validated['loai_thiet_hai'];
                    }
                }

                // Update booking-level phụ phí & ghi chú
                $booking->phi_phat_sinh = $tongPhiPhatSinh;
                $booking->ghi_chu_checkout = trim($ghiChuCheckout);
                $booking->nguoi_checkout = Auth::user()->ho_ten;

                // Chưa set thoi_gian_checkout & trang_thai = 'da_tra' vội,
                // sẽ quyết định sau khi xử lý từng phòng

                // Cập nhật tổng tiền booking và invoice
                \App\Services\BookingPriceCalculator::recalcTotal($booking);

                // Refresh booking để lấy giá trị mới nhất
                $booking->refresh();

                // Update invoice với giá trị mới từ booking
                if ($booking->invoice) {
                    $tongMoi = $booking->invoice->tien_phong
                        + $booking->invoice->tien_dich_vu
                        + $tongPhiPhatSinh
                        - $booking->invoice->giam_gia;

                    $booking->invoice->update([
                        'phi_phat_sinh' => $tongPhiPhatSinh,
                        'tong_tien' => $booking->tong_tien,
                        'con_lai' => max(0, $booking->tong_tien - ($booking->invoice->da_thanh_toan ?? 0)),
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

                // Update room status to 'trong' ngay sau checkout để phòng có thể đặt được cho những ngày tiếp theo
                // (Hệ thống cho phép đặt trước, nếu để 'dang_don' quá lâu sẽ mất lượt đặt)
                foreach ($phongsToCheckout as $phong) {
                    if (!is_null($phong->pivot->thoi_gian_checkin) && is_null($phong->pivot->thoi_gian_checkout)) {
                        // CRITICAL: Kiểm tra xem phòng có đang được đặt cho booking khác trong tương lai không
                        // Nếu có, giữ nguyên trạng thái để tránh conflict
                        $hasOtherBooking = DatPhong::where('id', '!=', $booking->id)
                            ->whereHas('phongs', function ($q) use ($phong) {
                                $q->where('phong_id', $phong->id);
                            })
                            ->where(function ($q) use ($booking) {
                                $q->where('ngay_tra', '>', $booking->ngay_nhan)
                                    ->where('ngay_nhan', '<', $booking->ngay_tra);
                            })
                            ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
                            ->exists();

                        // Chỉ chuyển về 'trong' nếu không có booking conflict
                        if (!$hasOtherBooking) {
                            // Sử dụng DB facade để update trực tiếp, tránh trigger observer trùng lặp
                            \Illuminate\Support\Facades\DB::table('phong')
                                ->where('id', $phong->id)
                                ->update(['trang_thai' => 'trong']);
                        }

                        // Cập nhật thông tin theo từng phòng trên pivot
                        $booking->phongs()->updateExistingPivot($phong->id, [
                            'thoi_gian_checkout' => $checkoutTime,
                            'trang_thai_phong' => 'da_checkout',
                        ]);
                    }
                }

                // Sau khi cập nhật các phòng được chọn, kiểm tra xem tất cả phòng
                // trong booking đã checkout hết chưa. Nếu tất cả đều 'da_checkout'
                // thì mới đặt booking về trạng thái 'da_tra'.
                $allRooms = $booking->phongs()->get();
                $hasNotCheckoutRoom = $allRooms->contains(function ($phong) {
                    return $phong->pivot->trang_thai_phong !== 'da_checkout';
                });

                if (!$hasNotCheckoutRoom) {
                    $booking->thoi_gian_checkout = $checkoutTime;
                    $booking->trang_thai = 'da_tra';
                }

                $booking->save();

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
