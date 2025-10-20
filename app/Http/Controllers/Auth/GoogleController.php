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
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google callback
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Check if user already exists
            $user = User::where('email', $googleUser->email)->first();
            
            if ($user) {
                // Update Google ID if not set
                if (!$user->google_id) {
                    $user->google_id = $googleUser->id;
                    $user->save();
                }
            } else {
                // Create new user
                $user = User::create([
                    'ho_ten' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'password' => Hash::make(Str::random(24)), // Random password
                    'email_verified_at' => now(),
                    'vai_tro' => 'khach_hang',
                    'trang_thai' => 'hoat_dong',
                ]);
                
                // Save avatar if available
                if ($googleUser->avatar) {
                    $avatarContents = file_get_contents($googleUser->avatar);
                    $avatarName = 'google_' . $user->id . '.jpg';
                    file_put_contents(public_path('uploads/avatars/' . $avatarName), $avatarContents);
                    $user->img = 'uploads/avatars/' . $avatarName;
                    $user->save();
                }
            }
            
            // Login user
            Auth::login($user);
            
            return redirect()->intended(route('client.dashboard'))
                ->with('success', 'Đăng nhập thành công với Google!');
                
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Đăng nhập Google thất bại. Vui lòng thử lại.');
        }
    }
}

