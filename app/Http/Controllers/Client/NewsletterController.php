<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Mail\NewsletterSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        // Validate email
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ], [
            'email.required' => 'Vui lòng nhập email của bạn.',
            'email.email' => 'Email không hợp lệ.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $email = $request->input('email');

        try {
            // Gửi email xác nhận đăng ký
            Mail::to($email)->send(new NewsletterSubscription($email));

            return back()->with('success', 'Cảm ơn bạn đã đăng ký nhận tin! Chúng tôi đã gửi email xác nhận đến địa chỉ email của bạn.');
        } catch (\Exception $e) {
            Log::error('Newsletter subscription error: ' . $e->getMessage());
            
            return back()
                ->with('error', 'Đã có lỗi xảy ra khi gửi email. Vui lòng thử lại sau.')
                ->withInput();
        }
    }
}

