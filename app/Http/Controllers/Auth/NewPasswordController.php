<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'token.required' => 'Mã xác nhận không hợp lệ hoặc đã hết hạn.',
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.confirmed' => 'Mật khẩu xác nhận không trùng khớp.',
            'password.min' => 'Mật khẩu phải có ít nhất :min ký tự.',
        ]);

        // Tiến hành reset mật khẩu
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->password = Hash::make($request->password);
                $user->save();

                event(new PasswordReset($user));
            }
        );

        // Trả kết quả
        if ($status == Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('status', 'Mật khẩu đã được đặt lại thành công. Vui lòng đăng nhập lại.');
        } else {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.']);
        }
    }

}
