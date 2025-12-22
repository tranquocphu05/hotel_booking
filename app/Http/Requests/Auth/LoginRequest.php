<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // keep the field name 'email' for the login input (Breeze uses this),
            // but allow it to be either the email address or username depending on DB schema
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */

    public function messages(): array
    {
        return [
            'email.required' => 'Vui lòng nhập email hoặc tên đăng nhập.',
            'email.string' => 'Trường email hoặc tên đăng nhập không hợp lệ.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.string' => 'Mật khẩu không hợp lệ.',
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();
        // determine login column (email or username)
        $userModel = new User();
        $table = $userModel->getTable();
        $loginColumn = Schema::hasColumn($table, 'email') ? 'email' : 'username';

        $credentials = [$loginColumn => $this->input('email'), 'password' => $this->input('password')];

        if (!Auth::attempt($credentials, $this->boolean('remember'))) {
            // attempt fallback for legacy hash formats (e.g. MD5 hex) by manual check
            $userQuery = DB::table($table)->where($loginColumn, $this->input('email'))->first();
            if ($userQuery && isset($userQuery->password)) {
                $stored = $userQuery->password;
                // detect 32-char hex MD5
                if (preg_match('/^[0-9a-f]{32}$/i', $stored)) {
                    if (md5($this->input('password')) === strtolower($stored) || md5($this->input('password')) === $stored) {
                        // rehash to bcrypt and update user record
                        try {
                            $id = $userQuery->id;
                            DB::table($table)->where('id', $id)->update(['password' => Hash::make($this->input('password'))]);
                            // now attempt to login again
                            if (Auth::attempt($credentials, $this->boolean('remember'))) {
                                // Kiểm tra trạng thái tài khoản sau khi đăng nhập thành công
                                $user = Auth::user();
                                if ($user && $user->trang_thai !== 'hoat_dong') {
                                    Auth::logout();
                                    RateLimiter::hit($this->throttleKey());
                                    $message = 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.';
                                    session()->flash('login_error', $message);
                                    throw ValidationException::withMessages([
                                        'email' => [$message],
                                    ]);
                                }
                                RateLimiter::clear($this->throttleKey());
                                return;
                            }
                        } catch (\Throwable $e) {
                            // ignore and continue to standard failure handling
                        }
                    }
                }
            }

            RateLimiter::hit($this->throttleKey());

            // provide a clearer error message and store a session key for blade to display
            // $message = trans('auth.failed');
            $message = 'Email hoặc mật khẩu không đúng.';

            session()->flash('login_error', $message);

            throw ValidationException::withMessages([
                'email' => [$message],
            ]);
        }

        // Kiểm tra trạng thái tài khoản sau khi đăng nhập thành công
        $user = Auth::user();
        if ($user && $user->trang_thai !== 'hoat_dong') {
            Auth::logout();
            RateLimiter::hit($this->throttleKey());
            $message = 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.';
            session()->flash('login_error', $message);
            throw ValidationException::withMessages([
                'email' => [$message],
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }



    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        // use the same login field for throttling (email or username)
        $userModel = new User();
        $table = $userModel->getTable();
        $loginColumn = Schema::hasColumn($table, 'email') ? 'email' : 'username';

        $loginValue = $this->input('email');
        return Str::transliterate(Str::lower($loginValue) . '|' . $this->ip());
    }
}
