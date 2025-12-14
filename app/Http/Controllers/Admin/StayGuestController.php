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

        // Check capacity: default 2 per room
        $capacity = 2;

        // Count current people in the room
        if (\Illuminate\Support\Facades\Schema::hasColumn('stay_guests', 'phong_id')) {
            $currentStayGuestsCount = $booking->stayGuests()->where('phong_id', $room->id)->count();
        } else {
            // If column doesn't exist, fallback to counting all stayGuests for booking (safe fallback)
            $currentStayGuestsCount = $booking->stayGuests->count();
        }

        // If booking has exactly one room assigned (legacy / single), include booking->so_nguoi
        $assignedPhongIds = $booking->getPhongIds();
        $baseGuestsInRoom = 0;
        if (count($assignedPhongIds) == 1 && (int)$assignedPhongIds[0] === (int)$room->id) {
            $baseGuestsInRoom = (int)($booking->so_nguoi ?? 0);
        }

        // If capacity exceeded, reject
        if (($baseGuestsInRoom + $currentStayGuestsCount + 1) > $capacity) {
            return back()->with('error', 'Sức chứa phòng đã đầy (mặc định 2 khách mỗi phòng). Vui lòng chọn phòng khác hoặc liên hệ quản lý.');
        }

        // Compute age
        $age = null;
        if ($request->filled('dob')) {
            try {
                $age = Carbon::parse($request->dob)->age;
            } catch (\Exception $e) {
                $age = null;
            }
        }

        // Determine charge rule: <6 free, 6-11 50%, >=12 adult
        $modifier = 1.0;
        if (!is_null($age)) {
            if ($age < 6) {
                $modifier = 0.0;
            } elseif ($age >= 6 && $age <= 11) {
                $modifier = 0.5;
            } else {
                $modifier = 1.0;
            }
        }

        $giaBoSung = $room->gia_bo_sung ?? 0;
        $extraFee = (float) round($giaBoSung * $modifier, 2);

        DB::transaction(function() use ($booking, $room, $request, $age, $extraFee) {
            $userId = Auth::id();

            // Use full_name or fallback to legacy column 'ten_khach'
            $guestData = [
                'dat_phong_id' => $booking->id,
                'phong_id' => $room->id,
                'dob' => $request->dob ? Carbon::parse($request->dob)->toDateString() : null,
                'age' => $age,
                'extra_fee' => $extraFee,
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
                    $guestData['loai_khach'] = $age < 12 ? 'tre_em' : 'nguoi_lon';
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

            // Create invoice item for this extra guest
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
                    'description' => 'Thêm 1 ' . ($age !== null && $age < 12 ? 'trẻ em' : 'người lớn'),
                    'quantity' => 1,
                    'unit_price' => $unitPrice,
                    'days' => $days,
                    'amount' => $amount,
                    'start_date' => $sDate->toDateString(),
                    'end_date' => $eDate->toDateString(),
                    'meta' => json_encode(['guest_type' => ($age !== null && $age < 12 ? 'child' : 'adult'), 'age' => $age]),
                    'created_by' => $userId,
                    'reason' => $request->reason ?? null,
                ]);

                // Link invoice item to stay guest for later adjustments
                if (isset($item->id)) {
                    $guest->invoice_item_id = $item->id;
                    $guest->save();
                }

                // Update booking so_nguoi
                $booking->increment('so_nguoi', 1);

                // Update target invoice totals
                if ($targetInvoice) {
                    // Track per-guest totals on the invoice using the new column
                    if (\Illuminate\Support\Facades\Schema::hasColumn('hoa_don', 'phi_them_nguoi')) {
                        $targetInvoice->increment('phi_them_nguoi', $amount);
                    } else {
                        // fallback to phi_phat_sinh for older schemas
                        $targetInvoice->increment('phi_phat_sinh', $amount);
                    }
                    $targetInvoice->increment('tong_tien', $amount);

                    // If the booking is still unpaid, update booking totals so the
                    // UI shows the updated total immediately. Prefer booking.phi_them_nguoi
                    if (!in_array($booking->trang_thai, ['da_thanh_toan', 'hoan_tien'])) {
                        if (\Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'phi_them_nguoi')) {
                            $booking->increment('phi_them_nguoi', $amount);
                        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'phi_phat_sinh')) {
                            $booking->increment('phi_phat_sinh', $amount);
                        }
                        if (\Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'tong_tien')) {
                            $booking->increment('tong_tien', $amount);
                        }
                    }
                }
            } else {
                // If invoice not writable or doesn't exist, still increment booking so_nguoi
                $booking->increment('so_nguoi', 1);
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
                        'description' => 'Giảm 1 ' . (($origItem && $origItem->meta && isset($origItem->meta['guest_type']) && $origItem->meta['guest_type'] === 'child') || ($guest->age && $guest->age < 12) ? 'trẻ em' : 'người lớn') . ' (trả sớm)',
                        'quantity' => -1,
                        'unit_price' => $unitPrice,
                        'days' => $adjustDays,
                        'amount' => -1 * $adjustAmount,
                        'start_date' => $leftDate->toDateString(),
                        'end_date' => $originalEnd->toDateString(),
                        'meta' => json_encode(['guest_type' => ($guest->age && $guest->age < 12 ? 'child' : 'adult'), 'original_invoice_item_id' => $origItem->id ?? null, 'original_end_date' => $originalEnd->toDateString()]),
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

            // Decrement invoice totals where appropriate.
            $invoice = $booking->invoice;
            if ($invoice && !$invoice->isExtra() && !in_array($invoice->trang_thai, ['da_thanh_toan', 'hoan_tien'])) {
                if ($adjustAmount > 0) {
                    // If adjustment was applied to the booking invoice, it has already been decremented above.
                    if (!isset($adjustInvoice) || $adjustInvoice->id !== $invoice->id) {
                        if ($origItem && ($origItem->type ?? '') === 'extra_guest' && \Illuminate\Support\Facades\Schema::hasColumn('hoa_don', 'phi_them_nguoi')) {
                            $invoice->decrement('phi_them_nguoi', $adjustAmount);
                        } else {
                            $invoice->decrement('phi_phat_sinh', $adjustAmount);
                        }
                        $invoice->decrement('tong_tien', $adjustAmount);
                        if (!in_array($booking->trang_thai, ['da_thanh_toan', 'hoan_tien'])) {
                            if ($origItem && ($origItem->type ?? '') === 'extra_guest' && \Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'phi_them_nguoi')) {
                                $booking->decrement('phi_them_nguoi', $adjustAmount);
                            } elseif (\Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'phi_phat_sinh')) {
                                $booking->decrement('phi_phat_sinh', $adjustAmount);
                            }
                            if (\Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'tong_tien')) {
                                $booking->decrement('tong_tien', $adjustAmount);
                            }
                        }
                    }
                } else {
                    // Decrement the per-guest column if available, otherwise fallback
                    if (\Illuminate\Support\Facades\Schema::hasColumn('hoa_don', 'phi_them_nguoi')) {
                        $invoice->decrement('phi_them_nguoi', $extraFee);
                    } else {
                        $invoice->decrement('phi_phat_sinh', $extraFee);
                    }
                    $invoice->decrement('tong_tien', $extraFee);
                    if (!in_array($booking->trang_thai, ['da_thanh_toan', 'hoan_tien'])) {
                        if (\Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'phi_them_nguoi')) {
                            $booking->decrement('phi_them_nguoi', $extraFee);
                        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('dat_phong', 'phi_phat_sinh')) {
                        }
                    }
                }
            }

            // Decrement booking so_nguoi
            $booking->decrement('so_nguoi', 1);

            // Delete guest
            $guest->delete();

            Log::info('Removed stay guest', ['booking_id' => $booking->id, 'guest_id' => $guest->id, 'by' => Auth::id() ?? null]);
        });

        return redirect()->route('admin.dat_phong.show', $booking->id)
            ->with('success', 'Đã xoá khách khỏi danh sách lưu trú.');
    }
}
