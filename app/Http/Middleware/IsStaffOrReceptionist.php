<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsStaffOrReceptionist
{
    /**
     * Handle an incoming request.
     * Cho phép admin, nhan_vien và le_tan
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $role = Auth::user()->vai_tro;
            if (in_array($role, ['admin', 'nhan_vien', 'le_tan'])) {
                return $next($request);
            }
        }

        return redirect()->route('client.dashboard')->with('error', 'Bạn không có quyền truy cập.');
    }
}


