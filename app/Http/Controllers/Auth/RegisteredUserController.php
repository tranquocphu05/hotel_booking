<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'cccd' => ['nullable', 'string', 'max:20'],
            'sdt' => ['nullable', 'string', 'max:20'],
            'dia_chi' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::create([
            'ho_ten' => $request->name,
            'email' => $request->email,
            'cccd' => $request->cccd,
            'sdt' => $request->sdt,
            'dia_chi' => $request->dia_chi,
            'password' => Hash::make($request->password),
            'vai_tro' => 'khach_hang',
            'trang_thai' => 'hoat_dong',
        ]);

        event(new Registered($user));

        // Do not auto-login the user. Redirect to the login page so the user can authenticate.
        return redirect()->route('login')->with('status', 'Registration successful. Please login.');
    }
}
