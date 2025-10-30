<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(5);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|max:100|unique:nguoi_dung,username',
            'email' => 'required|email|unique:nguoi_dung,email',
            'password' => 'required|string|min:6',
            'ho_ten' => 'nullable|string|max:100',
            'sdt' => 'nullable|string|max:20',
            'dia_chi' => 'nullable|string|max:255',
            'cccd' => 'nullable|string|max:20',
            'vai_tro' => 'required|in:admin,nhan_vien,khach_hang',
            'trang_thai' => 'required|in:hoat_dong,khoa',
        ]);

        $data['password'] = Hash::make($data['password']);
        // model User maps to nguoi_dung table in this project; ensure fillable matches
        User::create($data);

        return redirect()->route('admin.users.index')->with('success','User created');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'username' => 'required|string|max:100|unique:nguoi_dung,username,'.$user->id,
            'email' => 'required|email|unique:nguoi_dung,email,'.$user->id,
            'password' => 'nullable|string|min:6',
            'ho_ten' => 'nullable|string|max:100',
            'sdt' => 'nullable|string|max:20',
            'dia_chi' => 'nullable|string|max:255',
            'cccd' => 'nullable|string|max:20',
            'vai_tro' => 'required|in:admin,nhan_vien,khach_hang',
            'trang_thai' => 'required|in:hoat_dong,khoa',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        return redirect()->route('admin.users.index')->with('success','User updated');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success','User deleted');
    }
}
