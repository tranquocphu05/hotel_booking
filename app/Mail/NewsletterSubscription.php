<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewsletterSubscription extends Mailable
{
    use Queueable, SerializesModels;

    public string $email;

    public function __construct(string $email)
    {
        $this->email = $email;
    }

    public function build(): self
    {
        return $this->subject('Cảm ơn bạn đã đăng ký nhận tin từ Ozia Hotel')
            ->view('emails.newsletter_subscription');
    }
}


