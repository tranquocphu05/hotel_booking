<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AllowClient
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            // Kiểm tra tài khoản bị khóa
            if ($user->trang_thai !== 'hoat_dong') {
                Auth::logout();
                return redirect()->route('login')->with('error', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.');
            }
            
            if (in_array($user->vai_tro, ['admin','khach_hang'])) {
                return $next($request);
            }
        }

        return redirect()->route('login');
    }
}
