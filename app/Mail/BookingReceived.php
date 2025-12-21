<?php

namespace App\Mail;

use App\Models\DatPhong;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingReceived extends Mailable
{
    use Queueable, SerializesModels;

    public DatPhong $booking;

    public function __construct(DatPhong $booking)
    {
        $this->booking = $booking;
    }

    public function build(): self
    {
        return $this->subject('Đơn đặt phòng của bạn đã được nhận')
            ->view('emails.booking_received');
    }
}

