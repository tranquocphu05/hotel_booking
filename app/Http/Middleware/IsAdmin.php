<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->vai_tro === 'admin') {
            return $next($request);
        }

        // If not admin, redirect to client dashboard
        return redirect()->route('client.dashboard')->with('error', 'You do not have admin access.');
    }
}
