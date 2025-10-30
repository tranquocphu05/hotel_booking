<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequests;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(5);
        $activeAdminCount = User::where('vai_tro', 'admin')
            ->where('trang_thai', 'hoat_dong')
            ->count();
        return view('admin.users.index', compact('users', 'activeAdminCount'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(UserRequests $request)
    {
        $data = $request->validated();

        $data['password'] = Hash::make($data['password']);
        // model User maps to nguoi_dung table in this project; ensure fillable matches
        User::create($data);

        return redirect()->route('admin.users.index')->with('success', 'User created');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(UserRequests $request, User $user)
    {
        try {
            $data = $request->validated();

            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            // Nếu là admin cuối cùng: không được hạ vai trò hoặc khóa
            if ($user->vai_tro === 'admin') {
                $activeAdmins = User::where('vai_tro', 'admin')->where('trang_thai', 'hoat_dong')->count();
                $willDemote = array_key_exists('vai_tro', $data) && $data['vai_tro'] !== 'admin';
                $willLock  = array_key_exists('trang_thai', $data) && $data['trang_thai'] !== 'hoat_dong';
                // Không cho tự hạ vai trò/khóa chính mình
                if (Auth::check() && Auth::user()->id === $user->id && ($willDemote || $willLock)) {
                    return redirect()->route('admin.users.index')->with('error', 'Không thể tự vô hiệu hóa hoặc hạ vai trò tài khoản admin đang đăng nhập.');
                }
                if ($activeAdmins <= 1 && ($willDemote || $willLock)) {
                    return redirect()->route('admin.users.index')->with('error', 'Phải còn ít nhất 1 tài khoản admin hoạt động.');
                }
            }

            $user->update($data);

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'Cập nhật người dùng thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function destroy(User $user)
    {
        // Không xóa cứng; nếu là admin cuối cùng thì không cho khóa
        if ($user->vai_tro === 'admin') {
            // Không cho tự khóa chính mình
            if (Auth::check() && Auth::user()->id === $user->id) {
                return redirect()->route('admin.users.index')->with('error', 'Không thể tự vô hiệu hóa tài khoản admin đang đăng nhập.');
            }
            $activeAdmins = User::where('vai_tro', 'admin')->where('trang_thai', 'hoat_dong')->count();
            if ($activeAdmins <= 1) {
                return redirect()->route('admin.users.index')->with('error', 'Không thể vô hiệu hóa admin cuối cùng.');
            }
        }

        $user->update(['trang_thai' => 'khoa']);
        return redirect()->route('admin.users.index')->with('success', 'Đã vô hiệu hóa tài khoản (không xóa dữ liệu).');
    }

    public function toggleStatus(User $user)
    {
        $newStatus = $user->trang_thai === 'hoat_dong' ? 'khoa' : 'hoat_dong';

        if ($user->vai_tro === 'admin' && $newStatus === 'khoa') {
            // Không cho tự khóa chính mình
            if (Auth::check() && Auth::user()->id === $user->id) {
                return redirect()->route('admin.users.index')->with('error', 'Không thể tự vô hiệu hóa tài khoản admin đang đăng nhập.');
            }
            $activeAdmins = User::where('vai_tro', 'admin')->where('trang_thai', 'hoat_dong')->count();
            if ($activeAdmins <= 1) {
                return redirect()->route('admin.users.index')->with('error', 'Phải còn ít nhất 1 tài khoản admin hoạt động.');
            }
        }

        $user->update(['trang_thai' => $newStatus]);

        return redirect()->route('admin.users.index')->with('success', $newStatus === 'khoa' ? 'Đã vô hiệu hóa tài khoản.' : 'Đã kích hoạt tài khoản.');
    }
}
