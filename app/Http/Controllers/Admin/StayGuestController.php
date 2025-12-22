<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DatPhong;
use App\Models\Phong;
use App\Models\StayGuest;
use App\Models\InvoiceItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StayGuestController extends Controller
{
    public function store(Request $request, $datPhongId)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'dob' => 'nullable|date',
            'phong_id' => 'nullable|exists:phong,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'reason' => 'nullable|string',
        ]);

        $booking = DatPhong::with(['phongs', 'stayGuests', 'invoice'])->findOrFail($datPhongId);

        // Ensure booking is checked in and not checked out
        if (!$booking->thoi_gian_checkin || $booking->thoi_gian_checkout) {
            return back()->with('error', 'Chỉ có thể thêm khách khi khách đang ở (sau khi check-in và chưa checkout).');
        }

        $phongId = $request->phong_id;
        if (!$phongId) {
            // fallback: take first assigned room
            $assigned = $booking->getPhongIds();
            $phongId = $assigned[0] ?? null;
        }

        $room = $phongId ? Phong::find($phongId) : null;
        if (!$room) {
            return back()->with('error', 'Vui lòng chọn phòng hợp lệ để gán khách.');
        }

        // Ensure selected room is assigned to the booking
        $assignedPhongIds = $booking->getPhongIds();
        if (!in_array($room->id, $assignedPhongIds)) {
            return back()->with('error', 'Phòng được chọn không thuộc booking này. Vui lòng chọn phòng hợp lệ.');
        }

        // Compute age for the incoming guest (needed for slot simulation and pricing)
        $age = null;
        if ($request->filled('dob')) {
            try {
                $age = Carbon::parse($request->dob)->age;
            } catch (\Exception $e) {
                // Try common alternative formats used in the UI (dd/mm/YYYY, dd-mm-YYYY)
                try {
                    $d = Carbon::createFromFormat('d/m/Y', $request->dob);
                    $age = $d->age;
                } catch (\Exception $e2) {
                    try {
                        $d = Carbon::createFromFormat('d-m-Y', $request->dob);
                        $age = $d->age;
                    } catch (\Exception $e3) {
                        // final fallback: try Y-m-d
                        try {
                            $d = Carbon::createFromFormat('Y-m-d', $request->dob);
                            $age = $d->age;
                        } catch (\Exception $e4) {
                            $age = null;
                            Log::warning('StayGuestController: failed to parse dob', ['dob' => $request->dob, 'booking_id' => $booking->id, 'input' => $request->all()]);
                        }
                    }
                }
            }
        }

        // If age still null but client provided 'age' field, accept it as a fallback
        if (is_null($age) && $request->filled('age')) {
            $a = (int) $request->age;
            if ($a > 0 && $a < 120) {
                $age = $a;
                Log::info('StayGuestController: using provided age field as fallback', ['age' => $age, 'booking_id' => $booking->id]);
            }
        }

        // If the client supplied a DOB but we could not parse it, ask them to correct the format
        if ($request->filled('dob') && is_null($age)) {
            return back()->with('error', 'Không thể xác định tuổi từ ngày sinh đã nhập. Vui lòng sử dụng định dạng YYYY-MM-DD hoặc DD/MM/YYYY.');
        }

        // NOTE: global feasibility is checked per-room and across checked-in rooms in canAddGuestToRoom.
        // We no longer rely on booking-level declared totals (`dat_phong.so_*`) for live feasibility checks.

        // Determine guest category for per-room validation and later pricing
        $guestCategory = 'adult';
        if (!is_null($age) && $age < 6) {
            $guestCategory = 'infant';
        } elseif (!is_null($age) && $age >= 6 && $age <= 12) {
            $guestCategory = 'child';
        }

        // Ensure selected room is currently checked-in for this booking
        $checkedInIds = $booking->getCheckedInPhongs()->pluck('id')->toArray();
        if (!in_array($room->id, $checkedInIds)) {
            return back()->with('error', 'Phòng được chọn hiện không trong trạng thái đang lưu trú. Vui lòng chọn phòng đang check-in.');
        }

        // Per-room validation: ensure adding this guest to the selected room is feasible
        if (!$booking->canAddGuestToRoom($room->id, $guestCategory)) {
            return back()->with('error', 'Không thể thêm khách vào phòng này: sẽ vượt quá giới hạn của phòng (mỗi phòng tối đa +1 người lớn, +2 trẻ em, +1 em bé). Vui lòng chọn phòng khác.');
        }

        // Determine charge rule: <6 free, 6-12 50%, >=13 adult
        $modifier = 1.0;
        if (!is_null($age)) {
            if ($age < 6) {
                $modifier = 0.0;
            } elseif ($age >= 6 && $age <= 12) {
                $modifier = 0.5;
            } else {
                $modifier = 1.0;
            }
        }

        // NOTE: global feasibility is checked per-room and across checked-in rooms in canAddGuestToRoom.
        // We no longer rely on booking-level declared totals (`dat_phong.so_*`) for live feasibility checks.

        // Determine guest category for per-room validation and later pricing
        $guestCategory = 'adult';
        if (!is_null($age) && $age < 6) {
            $guestCategory = 'infant';
        } elseif (!is_null($age) && $age >= 6 && $age <= 12) {
            $guestCategory = 'child';
        }

        // Ensure selected room is currently checked-in for this booking
        $checkedInIds = $booking->getCheckedInPhongs()->pluck('id')->toArray();
        if (!in_array($room->id, $checkedInIds)) {
            return back()->with('error', 'Phòng được chọn hiện không trong trạng thái đang lưu trú. Vui lòng chọn phòng đang check-in.');
        }

        // Per-room validation: ensure adding this guest to the selected room is feasible
        if (!$booking->canAddGuestToRoom($room->id, $guestCategory)) {
            return back()->with('error', 'Không thể thêm khách vào phòng này: sẽ vượt quá giới hạn của phòng (mỗi phòng tối đa +1 người lớn hoặc +2 trẻ em hoặc +2 em bé). Vui lòng chọn phòng khác.');
        }

        // Determine charge rule: <6 free, 6-12 50%, >=13 adult
        $modifier = 1.0;
        if (!is_null($age)) {
            if ($age < 6) {
                $modifier = 0.0;
            } elseif ($age >= 6 && $age <= 12) {
                $modifier = 0.5;
            } else {
                $modifier = 1.0;
            }
        }

        // Giá cố định theo độ tuổi:
        // - Người lớn (≥12 tuổi): 300.000đ
        // - Trẻ em (6-11 tuổi): 150.000đ
        // - Em bé (0-5 tuổi): Miễn phí
        $extraFee = 0;
        if (!is_null($age)) {
            if ($age >= 12) {
                $extraFee = 300000; // Người lớn
            } elseif ($age >= 6 && $age <= 11) {
                $extraFee = 150000; // Trẻ em
            } else {
                $extraFee = 0; // Em bé miễn phí
            }
        } else {
            // Nếu không có tuổi, mặc định tính như người lớn
            $extraFee = 300000;
        }

        // Decide if this guest should be chargeable: only charge when room's base seats are full
        $baseSeatsRemaining = $booking->getRoomBaseSeatsRemaining($room->id);
        $isChargeable = $baseSeatsRemaining <= 0;

        DB::transaction(function() use ($booking, $room, $request, $age, $extraFee, $isChargeable) {
            $userId = Auth::id();

            // Use full_name or fallback to legacy column 'ten_khach'
            $guestData = [
                'dat_phong_id' => $booking->id,
                'phong_id' => $room->id,
                'dob' => $request->dob ? Carbon::parse($request->dob)->toDateString() : null,
                'age' => $age,
                'extra_fee' => ($isChargeable ? $extraFee : 0),
                'created_by' => $userId,
                'created_at' => now(),
            ];

            // Set both new and legacy full name columns to be safe
            if (\Illuminate\Support\Facades\Schema::hasColumn('stay_guests', 'full_name')) {
                $guestData['full_name'] = $request->full_name;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('stay_guests', 'ten_khach')) {
                $guestData['ten_khach'] = $request->full_name;
            }

            // Extra fee: set both new and legacy columns
            if (\Illuminate\Support\Facades\Schema::hasColumn('stay_guests', 'extra_fee')) {
                $guestData['extra_fee'] = $extraFee;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('stay_guests', 'phu_phi_them')) {
                $guestData['phu_phi_them'] = $extraFee;
            }
            // New dedicated per-guest fee column
            if (\Illuminate\Support\Facades\Schema::hasColumn('stay_guests', 'phi_them_nguoi')) {
                $guestData['phi_them_nguoi'] = $extraFee;
            }

            // Creator: new and legacy
            if (\Illuminate\Support\Facades\Schema::hasColumn('stay_guests', 'created_by')) {
                $guestData['created_by'] = $userId;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('stay_guests', 'nguoi_them')) {
                $guestData['nguoi_them'] = $userId;
            }

            // loai_khach (enum) - required in legacy schema
            if (\Illuminate\Support\Facades\Schema::hasColumn('stay_guests', 'loai_khach')) {
                if (!is_null($age)) {
                    $guestData['loai_khach'] = $age <= 12 ? 'tre_em' : 'nguoi_lon';
                } else {
                    $guestData['loai_khach'] = 'nguoi_lon';
                }
            }

            // Compute apply date range for the extra guest
            $startDate = $request->start_date ? Carbon::parse($request->start_date)->toDateString() : now()->toDateString();
            $endDate = $request->end_date ? Carbon::parse($request->end_date)->toDateString() : ($booking->ngay_tra ? Carbon::parse($booking->ngay_tra)->toDateString() : now()->toDateString());
            $guestData['start_date'] = $startDate;
            $guestData['end_date'] = $endDate;

            $guest = StayGuest::create($guestData);

            // Upsert pivot phu_phi in booking_rooms will be applied after computing the actual $amount below
            // (moved to after invoice item creation so it uses the correct computed amount)

                // Determine guest category
                $guestCategory = 'adult';
                if (!is_null($age) && $age < 6) {
                    $guestCategory = 'infant';
                } elseif (!is_null($age) && $age >= 6 && $age <= 12) {
                    $guestCategory = 'child';
                }

                // Update per-room occupancy (booking_rooms) which is the source of truth for live room occupancy
                $booking->incrementBookingRoomCount($room->id, $guestCategory, 1);

                // If the guest is chargeable, create invoice item, update pivot and monetary columns
                if ($isChargeable) {
                    // If the main invoice is paid or marked EXTRA, create a separate EXTRA invoice instead
                    $invoice = $booking->invoice;
                    $targetInvoice = null;
                    if ($invoice && !$invoice->isExtra() && !in_array($invoice->trang_thai, ['da_thanh_toan', 'hoan_tien'])) {
                        $targetInvoice = $invoice;
                    } else {
                        // create a new EXTRA invoice for this booking
                        $targetInvoice = \App\Models\Invoice::create([
                            'dat_phong_id' => $booking->id,
                            'tong_tien' => 0,
                            'tien_phong' => 0,
                            'tien_dich_vu' => 0,
                            'phi_phat_sinh' => 0,
                            'giam_gia' => 0,
                            'trang_thai' => 'cho_thanh_toan',
                            'invoice_type' => 'EXTRA',
                        ]);
                    }

                    if ($targetInvoice) {
                        $sDate = Carbon::parse($startDate);
                        $eDate = Carbon::parse($endDate);
                        $days = max(1, $eDate->diffInDays($sDate));
                        $unitPrice = $extraFee; // per guest per night
                        $amount = round($unitPrice * 1 * $days, 2);

                        // Upsert pivot phu_phi in booking_rooms using ON DUPLICATE KEY UPDATE
                        // Increment by the FULL amount for this invoice item (not per-night)
                        $now = now();
                        DB::statement(
                            'INSERT INTO booking_rooms (dat_phong_id, phong_id, phu_phi, created_at, updated_at) VALUES (?, ?, ?, ?, ?) ' .
                            'ON DUPLICATE KEY UPDATE phu_phi = COALESCE(phu_phi, 0) + VALUES(phu_phi), updated_at = VALUES(updated_at)',
                            [$booking->id, $room->id, $amount, $now, $now]
                        );

                        $item = InvoiceItem::create([
                            'invoice_id' => $targetInvoice->id,
                            'type' => 'extra_guest',
                            'description' => 'Thêm 1 ' . ($age !== null && $age <= 12 ? 'trẻ em' : 'người lớn') . ' - Phòng ' . $room->so_phong,
                            'quantity' => 1,
                            'unit_price' => $unitPrice,
                            'days' => $days,
                            'amount' => $amount,
                            'start_date' => $sDate->toDateString(),
                            'end_date' => $eDate->toDateString(),
                            'meta' => json_encode([
                                'guest_type' => ($age !== null && $age <= 12 ? 'child' : 'adult'),
                                'age' => $age,
                                'phong_id' => $room->id,
                                'so_phong' => $room->so_phong,
                            ]),
                            'created_by' => $userId,
                            'reason' => $request->reason ?? null,
                        ]);

                        // Link invoice item to stay guest for later adjustments
                        if (isset($item->id)) {
                            $guest->invoice_item_id = $item->id;
                            $guest->save();
                        }

                        // Prefer category-specific invoice columns when present
                        if ($guestCategory === 'child' && \Illuminate\Support\Facades\Schema::hasColumn('hoa_don', 'phu_phi_tre_em')) {
                            $targetInvoice->increment('phu_phi_tre_em', $amount);
                        } elseif ($guestCategory === 'infant' && \Illuminate\Support\Facades\Schema::hasColumn('hoa_don', 'phu_phi_em_be')) {
                            $targetInvoice->increment('phu_phi_em_be', $amount);
                        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('hoa_don', 'phi_them_nguoi')) {
                            $targetInvoice->increment('phi_them_nguoi', $amount);
                        } else {
                            // fallback to phi_phat_sinh for older schemas
                            $targetInvoice->increment('phi_phat_sinh', $amount);
                        }

                        $targetInvoice->increment('tong_tien', $amount);

                        // If the booking is still unpaid, update booking totals so the
                        // UI shows the updated total immediately. Prefer explicit columns
                        if (!in_array($booking->trang_thai, ['da_thanh_toan', 'hoan_tien'])) {
                            if ($guestCategory === 'child') {
                                if (\Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'phu_phi_tre_em')) {
                                    $booking->increment('phu_phi_tre_em', $amount);
                                } elseif (\Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'phi_phat_sinh')) {
                                    $booking->increment('phi_phat_sinh', $amount);
                                }
                        } elseif ($guestCategory === 'infant') {
                            if (\Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'phu_phi_em_be')) {
                                $booking->increment('phu_phi_em_be', $amount);
                            } elseif (\Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'phi_phat_sinh')) {
                                $booking->increment('phi_phat_sinh', $amount);
                            }
                        } else {
                            if (\Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'phi_them_nguoi')) {
                                $booking->increment('phi_them_nguoi', $amount);
                            } elseif (\Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'phi_phat_sinh')) {
                                $booking->increment('phi_phat_sinh', $amount);
                            }
                        }

                        if (\Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'tong_tien')) {
                            $booking->increment('tong_tien', $amount);
                        }
                    }
                } else {
                    // If invoice not writable or doesn't exist, still update booking counts (already incremented)
                    // Nothing further required here because booking columns have been updated above
                }
                }

            Log::info('Added stay guest', ['booking_id' => $booking->id, 'guest' => $guest->toArray(), 'extra_fee' => $extraFee, 'by' => $userId]);

            Log::info('Added stay guest', ['booking_id' => $booking->id, 'guest' => $guest->toArray(), 'extra_fee' => $extraFee, 'by' => $userId]);
        });

        return redirect()->route('admin.dat_phong.show', $booking->id)
            ->with('success', 'Đã thêm khách thành công.');
    }

public function destroy(Request $request, $datPhongId, $guestId)
    {
        $booking = DatPhong::with(['phongs', 'stayGuests', 'invoice'])->findOrFail($datPhongId);
        $guest = StayGuest::findOrFail($guestId);

        if ($guest->dat_phong_id != $booking->id) {
            abort(403);
        }

        $room = $guest->phong;
        // Prefer new dedicated column 'phi_them_nguoi' then fall back to older columns
        $extraFee = $guest->phi_them_nguoi ?? $guest->extra_fee ?? $guest->phu_phi_them ?? 0;
        $guestName = $guest->full_name ?? $guest->ten_khach ?? 'N/A';

        DB::transaction(function() use ($booking, $guest, $room, $extraFee, $guestName, $request) {
            // Handle invoice adjustments when a guest leaves early
            $leftDate = $request->left_date ? Carbon::parse($request->left_date) : now();
            $adjustAmount = 0;
            $origItem = null;
            $adjustInvoice = null;
            if ($guest->invoice_item_id) {
                $origItem = InvoiceItem::find($guest->invoice_item_id);
            }

            $originalEnd = null;
            if ($origItem && $origItem->end_date) {
                $originalEnd = Carbon::parse($origItem->end_date);
            } elseif ($guest->end_date) {
                $originalEnd = Carbon::parse($guest->end_date);
            } else {
                $originalEnd = $booking->ngay_tra ? Carbon::parse($booking->ngay_tra) : null;
            }

            if ($originalEnd && $leftDate->lt($originalEnd)) {
                $adjustDays = $originalEnd->diffInDays($leftDate);
                $unitPrice = $origItem->unit_price ?? ($guest->phi_them_nguoi ?? $guest->extra_fee ?? $guest->phu_phi_them ?? 0);
                $adjustAmount = round($unitPrice * $adjustDays * 1, 2);

                // Create adjustment invoice item (negative amount) on the same invoice where the original item was recorded.
                $adjustInvoice = null;
                if ($origItem && $origItem->invoice_id) {
                    $adjustInvoice = \App\Models\Invoice::find($origItem->invoice_id);
                }
                // If we couldn't find original invoice or it's paid, fallback to booking invoice if writable
                if ((!$adjustInvoice || in_array($adjustInvoice->trang_thai, ['da_thanh_toan', 'hoan_tien'])) && $booking->invoice && !$booking->invoice->isExtra() && !in_array($booking->invoice->trang_thai, ['da_thanh_toan', 'hoan_tien'])) {
                    $adjustInvoice = $booking->invoice;
                }
                // If still no writable invoice, create an EXTRA invoice to record the adjustment
                if (!$adjustInvoice || in_array($adjustInvoice->trang_thai, ['da_thanh_toan', 'hoan_tien'])) {
                    $adjustInvoice = \App\Models\Invoice::create([
                        'dat_phong_id' => $booking->id,
                        'tong_tien' => 0,
                        'tien_phong' => 0,
                        'tien_dich_vu' => 0,
                        'phi_phat_sinh' => 0,
                        'giam_gia' => 0,
                        'trang_thai' => 'cho_thanh_toan',
                        'invoice_type' => 'EXTRA',
                    ]);
                }

                if ($adjustInvoice) {
                    $adjustItem = InvoiceItem::create([
                        'invoice_id' => $adjustInvoice->id,
                        'type' => 'adjustment',
                        'description' => 'Giảm 1 ' . (($origItem && $origItem->meta && isset($origItem->meta['guest_type']) && $origItem->meta['guest_type'] === 'child') || ($guest->age && $guest->age <= 12) ? 'trẻ em' : 'người lớn') . ' (trả sớm)',
                        'quantity' => -1,
                        'unit_price' => $unitPrice,
                        'days' => $adjustDays,
                        'amount' => -1 * $adjustAmount,
                        'start_date' => $leftDate->toDateString(),
                        'end_date' => $originalEnd->toDateString(),
                        'meta' => json_encode(['guest_type' => ($guest->age && $guest->age <= 12 ? 'child' : 'adult'), 'original_invoice_item_id' => $origItem->id ?? null, 'original_end_date' => $originalEnd->toDateString()]),
                        'created_by' => Auth::id(),
                        'reason' => $request->reason ?? null,
                    ]);

                    // If adjustment applied to the same invoice as the original charge, decrement that invoice totals.
                    // Otherwise adjustment lives on a new EXTRA invoice and its negative amount will be reflected there.
                    if ($adjustInvoice) {
                        if ($origItem && ($origItem->type ?? '') === 'extra_guest' && \Illuminate\Support\Facades\Schema::hasColumn('hoa_don', 'phi_them_nguoi')) {
                            $adjustInvoice->decrement('phi_them_nguoi', $adjustAmount);
                        } else {
                            $adjustInvoice->decrement('phi_phat_sinh', $adjustAmount);
                        }
                        $adjustInvoice->decrement('tong_tien', $adjustAmount);
                    }
                }
            }

            // Decrement pivot phu_phi by the prorated amount (if we computed adjust), otherwise decrement full extraFee
            if ($room) {
                $decrementValue = $adjustAmount > 0 ? $adjustAmount : $extraFee;
                DB::table('booking_rooms')
                    ->where('dat_phong_id', $booking->id)
                    ->where('phong_id', $room->id)
                    ->decrement('phu_phi', $decrementValue);

                // Ensure no negative phu_phi
                DB::table('booking_rooms')
                    ->where('dat_phong_id', $booking->id)
                    ->where('phong_id', $room->id)
                    ->where('phu_phi', '<', 0)
                    ->update(['phu_phi' => 0]);
            }

            // Decrement invoice totals where appropriate and ensure booking totals are reduced by the correct amount.
            $removeAmount = $adjustAmount > 0 ? $adjustAmount : ($origItem->amount ?? $extraFee);

            // Determine guest category for booking-level decrements
            $guestCategory = 'adult';
            $gAge = $guest->age;
            if (!is_null($gAge) && $gAge < 6) {
                $guestCategory = 'infant';
            } elseif (!is_null($gAge) && $gAge >= 6 && $gAge <= 12) {
                $guestCategory = 'child';
            }

            // Try to adjust the original invoice item if it exists and the invoice is writable
            $origInvoice = null;
            if ($origItem && ($origItem->invoice_id ?? null)) {
                $origInvoice = \App\Models\Invoice::find($origItem->invoice_id);
            }

            if ($origItem && $origInvoice && !in_array($origInvoice->trang_thai, ['da_thanh_toan', 'hoan_tien'])) {
                // Remove original invoice item and subtract its amount from that invoice
                $amt = $origItem->amount ?? $removeAmount;

                if (($origItem->type ?? '') === 'extra_guest' && \Illuminate\Support\Facades\Schema::hasColumn('hoa_don', 'phi_them_nguoi')) {
                    $origInvoice->decrement('phi_them_nguoi', $amt);
                } else {
                    $origInvoice->decrement('phi_phat_sinh', $amt);
                }
                $origInvoice->decrement('tong_tien', $amt);

                // delete the original invoice item (we're removing the charge)
                try {
                    $origItem->delete();
                } catch (\Exception $e) {
                    Log::warning('Failed to delete original invoice item while removing stay guest', ['item_id' => $origItem->id, 'error' => $e->getMessage()]);
                }

                // Also decrement booking totals if booking is not finalized
                if (!in_array($booking->trang_thai, ['da_thanh_toan', 'hoan_tien'])) {
                    if ($guestCategory === 'child' && \Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'phu_phi_tre_em')) {
                        $booking->decrement('phu_phi_tre_em', $amt);
                    } elseif ($guestCategory === 'infant' && \Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'phu_phi_em_be')) {
                        $booking->decrement('phu_phi_em_be', $amt);
                    } elseif (\Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'phi_them_nguoi')) {
                        $booking->decrement('phi_them_nguoi', $amt);
                    } else {
                        $booking->decrement('phi_phat_sinh', $amt);
                    }

                    if (\Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'tong_tien')) {
                        $booking->decrement('tong_tien', $amt);
                    }
                }
            } else {
                // Could not remove original item directly (no original item or invoice not writable)
                // Create a negative adjustment invoice item on a writable invoice so totals reflect removal.
                $targetInvoice = null;
                if ($origInvoice && !in_array($origInvoice->trang_thai, ['da_thanh_toan', 'hoan_tien'])) {
                    $targetInvoice = $origInvoice;
                } elseif ($booking->invoice && !$booking->invoice->isExtra() && !in_array($booking->invoice->trang_thai, ['da_thanh_toan', 'hoan_tien'])) {
                    $targetInvoice = $booking->invoice;
                } else {
                    $targetInvoice = \App\Models\Invoice::create([
                        'dat_phong_id' => $booking->id,
                        'tong_tien' => 0,
                        'tien_phong' => 0,
                        'tien_dich_vu' => 0,
                        'phi_phat_sinh' => 0,
                        'giam_gia' => 0,
                        'trang_thai' => 'cho_thanh_toan',
                        'invoice_type' => 'EXTRA',
                    ]);
                }

                if ($targetInvoice) {
                    $adjItem = InvoiceItem::create([
                        'invoice_id' => $targetInvoice->id,
                        'type' => 'adjustment',
                        'description' => 'Điều chỉnh - Xoá phụ phí khách thêm',
                        'quantity' => -1,
                        'unit_price' => $removeAmount,
                        'days' => 1,
                        'amount' => -1 * $removeAmount,
                        'start_date' => now()->toDateString(),
                        'end_date' => now()->toDateString(),
                        'meta' => json_encode(['guest_type' => ($guestCategory === 'child' ? 'child' : ($guestCategory === 'infant' ? 'infant' : 'adult')), 'original_invoice_item_id' => $origItem->id ?? null]),
                        'created_by' => Auth::id(),
                        'reason' => $request->reason ?? null,
                    ]);

                    if ($adjItem) {
                        if ($guestCategory === 'child' && \Illuminate\Support\Facades\Schema::hasColumn('hoa_don', 'phu_phi_tre_em')) {
                            $targetInvoice->decrement('phu_phi_tre_em', $removeAmount);
                        } elseif ($guestCategory === 'infant' && \Illuminate\Support\Facades\Schema::hasColumn('hoa_don', 'phu_phi_em_be')) {
                            $targetInvoice->decrement('phu_phi_em_be', $removeAmount);
                        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('hoa_don', 'phi_them_nguoi')) {
                            $targetInvoice->decrement('phi_them_nguoi', $removeAmount);
                        } else {
                            $targetInvoice->decrement('phi_phat_sinh', $removeAmount);
                        }

                        $targetInvoice->decrement('tong_tien', $removeAmount);
                    }
                }

                // Ensure booking totals also reflect removal if booking not finalized
                if (!in_array($booking->trang_thai, ['da_thanh_toan', 'hoan_tien'])) {
                    if ($guestCategory === 'child' && \Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'phu_phi_tre_em')) {
                        $booking->decrement('phu_phi_tre_em', $removeAmount);
                    } elseif ($guestCategory === 'infant' && \Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'phu_phi_em_be')) {
                        $booking->decrement('phu_phi_em_be', $removeAmount);
                    } elseif (\Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'phi_them_nguoi')) {
                        $booking->decrement('phi_them_nguoi', $removeAmount);
                    } else {
                        $booking->decrement('phi_phat_sinh', $removeAmount);
                    }

                    if (\Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'tong_tien')) {
                        $booking->decrement('tong_tien', $removeAmount);
                    }
                }
            }

// Decrement per-room occupancy counters (booking_rooms) - source of truth
            $roomId = $guest->phong_id;
            $gAge = $guest->age;
            if (!is_null($gAge) && $gAge < 6) {
                $booking->decrementBookingRoomCount($roomId, 'infant', 1);
            } elseif (!is_null($gAge) && $gAge >= 6 && $gAge <= 12) {
                $booking->decrementBookingRoomCount($roomId, 'child', 1);
            } else {
                // adult (or unknown age) - decrement adult slot
                $booking->decrementBookingRoomCount($roomId, 'adult', 1);
            }

            // Normalize booking numeric columns to avoid negatives and ensure consistency
            $sets = [];
            if (\Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'so_tre_em')) {
                $sets[] = 'so_tre_em = GREATEST(so_tre_em, 0)';
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'so_em_be')) {
                $sets[] = 'so_em_be = GREATEST(so_em_be, 0)';
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'so_nguoi')) {
                $sets[] = 'so_nguoi = GREATEST(so_nguoi, 0)';
            }
            if (!empty($sets)) {
                DB::statement('UPDATE dat_phong SET ' . implode(', ', $sets) . ' WHERE id = ?', [$booking->id]);
            }

            // Note: do NOT enforce so_nguoi >= so_tre_em + so_em_be because
            // so_nguoi represents number of adults only per booking schema.

            // Normalize monetary/surcharge columns on booking and invoices to avoid negatives
            $bookingSets = [];
            if (\Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'phu_phi_tre_em')) $bookingSets[] = 'phu_phi_tre_em = GREATEST(phu_phi_tre_em, 0)';
            if (\Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'phu_phi_em_be')) $bookingSets[] = 'phu_phi_em_be = GREATEST(phu_phi_em_be, 0)';
            if (\Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'phi_them_nguoi')) $bookingSets[] = 'phi_them_nguoi = GREATEST(phi_them_nguoi, 0)';
            if (\Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'phi_phat_sinh')) $bookingSets[] = 'phi_phat_sinh = GREATEST(phi_phat_sinh, 0)';
            if (\Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'tong_tien')) $bookingSets[] = 'tong_tien = GREATEST(tong_tien, 0)';
            if (!empty($bookingSets)) {
                DB::statement('UPDATE dat_phong SET ' . implode(', ', $bookingSets) . ' WHERE id = ?', [$booking->id]);
            }

            // Normalize corresponding invoice rows
            $invoiceSets = [];
            if (\Illuminate\Support\Facades\Schema::hasColumn('hoa_don', 'phu_phi_tre_em')) $invoiceSets[] = 'phu_phi_tre_em = GREATEST(phu_phi_tre_em, 0)';
            if (\Illuminate\Support\Facades\Schema::hasColumn('hoa_don', 'phu_phi_em_be')) $invoiceSets[] = 'phu_phi_em_be = GREATEST(phu_phi_em_be, 0)';
            if (\Illuminate\Support\Facades\Schema::hasColumn('hoa_don', 'phi_them_nguoi')) $invoiceSets[] = 'phi_them_nguoi = GREATEST(phi_them_nguoi, 0)';
            if (\Illuminate\Support\Facades\Schema::hasColumn('hoa_don', 'phi_phat_sinh')) $invoiceSets[] = 'phi_phat_sinh = GREATEST(phi_phat_sinh, 0)';
            if (\Illuminate\Support\Facades\Schema::hasColumn('hoa_don', 'tong_tien')) $invoiceSets[] = 'tong_tien = GREATEST(tong_tien, 0)';
            if (!empty($invoiceSets)) {
                DB::statement('UPDATE hoa_don SET ' . implode(', ', $invoiceSets) . ' WHERE dat_phong_id = ?', [$booking->id]);
            }
            // Delete guest
            $guest->delete();

            Log::info('Removed stay guest', ['booking_id' => $booking->id, 'guest_id' => $guest->id, 'by' => Auth::id() ?? null]);
        });

        return redirect()->route('admin.dat_phong.show', $booking->id)
            ->with('success', 'Đã xoá khách khỏi danh sách lưu trú.');
    }
}
