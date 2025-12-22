<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsReceptionist
{
    /**
     * Handle an incoming request.
     * Cho phép admin và le_tan
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            // Kiểm tra tài khoản bị khóa
            if ($user->trang_thai !== 'hoat_dong') {
                Auth::logout();
                return redirect()->route('login')->with('error', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.');
            }
            
            $role = $user->vai_tro;
            if (in_array($role, ['admin', 'le_tan'])) {
                return $next($request);
            }
        }

        return redirect()->route('client.dashboard')->with('error', 'Bạn không có quyền truy cập.');
    }
}


