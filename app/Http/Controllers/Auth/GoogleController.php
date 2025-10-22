<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    /**
     * Redirect to Google authentication page
     */
    public function redirectToGoogle()
    {
        session(['oauth_intent' => 'login']);
        return Socialite::driver('google')
            ->with(['prompt' => 'select_account']) // Bắt buộc chọn tài khoản
            ->redirect();
    }

    /**
     * Redirect to Google for REGISTER flow
     */
    public function redirectToGoogleRegister()
    {
        session(['oauth_intent' => 'register']);
        return Socialite::driver('google')
            ->with(['prompt' => 'select_account']) // Bắt buộc chọn tài khoản
            ->redirect();
    }

    /**
     * Handle Google callback
     */
    public function handleGoogleCallback()
    {
        try {
            // Dùng stateless để tránh lỗi state mismatch ở môi trường local
            $googleUser = Socialite::driver('google')->stateless()->user();
            $intent = session('oauth_intent', 'login');
            
            // Check if user already exists
            $user = User::where('email', $googleUser->email)->first();
            
            if ($intent === 'login') {
                // LOGIN: nếu chưa có tài khoản => báo lỗi, không tự tạo
                if (!$user) {
                    return redirect()->route('login')
                        ->with('error', 'Email Google chưa đăng ký tài khoản. Vui lòng đăng ký trước.');
                }
                // Update google_id nếu thiếu
                if (!$user->google_id) {
                    $user->google_id = $googleUser->id;
                    $user->save();
                }
            } else { // register
                // REGISTER: nếu đã có tài khoản => báo lỗi
                if ($user) {
                    return redirect()->route('register')
                        ->with('error', 'Email đã tồn tại. Vui lòng đăng nhập.');
                }
                // Tạo tài khoản mới
                $user = User::create([
                    'username' => $googleUser->email, // dùng email làm username
                    'ho_ten' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'password' => Hash::make(Str::random(24)),
                    'email_verified_at' => now(),
                    'vai_tro' => 'khach_hang',
                    'trang_thai' => 'hoat_dong',
                ]);
                if ($googleUser->avatar) {
                    try {
                        $avatarContents = @file_get_contents($googleUser->avatar);
                        if ($avatarContents !== false) {
                            if (!is_dir(public_path('uploads/avatars'))) {
                                @mkdir(public_path('uploads/avatars'), 0775, true);
                            }
                            $avatarName = 'google_' . $user->id . '.jpg';
                            file_put_contents(public_path('uploads/avatars/' . $avatarName), $avatarContents);
                            $user->img = 'uploads/avatars/' . $avatarName;
                            $user->save();
                        }
                    } catch (\Throwable $e) {
                        // ignore avatar errors
                    }
                }
            }
            
            // Login user
            Auth::login($user);
            
            if ($intent === 'register') {
                return redirect()->intended(route('client.dashboard'))
                    ->with('success', 'Đăng ký tài khoản Google thành công! Chào mừng bạn đến với khách sạn của chúng tôi.');
            } else {
                return redirect()->intended(route('client.dashboard'))
                    ->with('success', 'Đăng nhập thành công với Google!');
            }
                
        } catch (\Exception $e) {
            $intent = session('oauth_intent', 'login');
            $message = 'Đăng nhập Google thất bại. Vui lòng thử lại.';
            if (config('app.debug')) {
                $message .= ' ['.$e->getMessage().']';
            }
            return redirect()->route($intent === 'register' ? 'register' : 'login')
                ->with('error', $message);
        }
    }
}

