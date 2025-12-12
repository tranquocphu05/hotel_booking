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
            $role = Auth::user()->vai_tro;
            if (in_array($role, ['admin', 'le_tan'])) {
                return $next($request);
            }
        }

        return redirect()->route('client.dashboard')->with('error', 'Bạn không có quyền truy cập.');
    }
}


