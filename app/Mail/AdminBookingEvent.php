<?php

namespace App\Mail;

use App\Models\DatPhong;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminBookingEvent extends Mailable
{
    use Queueable, SerializesModels;

    public DatPhong $booking;
    public string $eventType; // created | pending | paid

    public function __construct(DatPhong $booking, string $eventType)
    {
        $this->booking = $booking;
        $this->eventType = $eventType;
    }

    public function build()
    {
        $subjectMap = [
            'created' => 'Thông báo: Có đơn đặt phòng mới',
            'pending' => 'Thông báo: Đơn đặt phòng chờ xác nhận',
            'paid'    => 'Thông báo: Khách hàng đã thanh toán thành công',
        ];

        $subject = $subjectMap[$this->eventType] ?? 'Thông báo đặt phòng';

        return $this->subject($subject)
            ->view('emails.admin_booking_event')
            ->with([
                'booking'   => $this->booking,
                'eventType' => $this->eventType,
            ]);
    }
}




