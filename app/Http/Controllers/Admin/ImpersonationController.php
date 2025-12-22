<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function impersonate(Request $request, $userId)
    {
        $admin = Auth::user();
        if (! $admin || $admin->vai_tro !== 'admin') {
            abort(403);
        }

        $target = User::findOrFail($userId);

        // Kiểm tra tài khoản bị khóa
        if ($target->trang_thai !== 'hoat_dong') {
            return redirect()->back()->with('error', 'Không thể đăng nhập vào tài khoản đã bị khóa.');
        }

        // store original admin id to stop impersonation later
        session(['impersonator_id' => $admin->id]);

        Auth::login($target);

        return redirect()->route('client.dashboard')->with('impersonating', true);
    }

    public function stop(Request $request)
    {
        $impersonatorId = session('impersonator_id');
        if ($impersonatorId) {
            $admin = User::find($impersonatorId);
            if ($admin) {
                Auth::login($admin);
            }
        }

        session()->forget('impersonator_id');

        return redirect()->route('admin.dashboard')->with('impersonating', false);
    }
}
