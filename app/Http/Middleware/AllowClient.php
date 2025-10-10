<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AllowClient
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && in_array(Auth::user()->vai_tro, ['admin','khach_hang'])) {
            return $next($request);
        }

        return redirect()->route('login');
    }
}
