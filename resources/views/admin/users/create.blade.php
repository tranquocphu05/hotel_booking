@extends('layouts.admin')

@section('title','Create User')

@section('admin_content')
    <h1>Create User</h1>
    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4" autocomplete="off">
        @csrf
        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 text-red-700 rounded">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div>
            <label class="block text-sm">Username</label>
            <input name="username" value="{{ old('username') }}" class="border p-2 w-full" autocomplete="off" />
        </div>
        <div>
            <label class="block text-sm">Email</label>
            <input name="email" type="email" value="{{ old('email') }}" class="border p-2 w-full" autocomplete="email" />
        </div>
        <div>
            <label class="block text-sm">Password</label>
            <input name="password" type="password" class="border p-2 w-full" autocomplete="new-password" />
        </div>
        <div>
            <label class="block text-sm">Full name</label>
            <input name="ho_ten" value="{{ old('ho_ten') }}" class="border p-2 w-full" />
        </div>
        <div>
            <label class="block text-sm">Phone</label>
            <input name="sdt" value="{{ old('sdt') }}" class="border p-2 w-full" />
        </div>
        <div>
            <label class="block text-sm">CCCD</label>
            <input name="cccd" value="{{ old('cccd') }}" class="border p-2 w-full" />
        </div>
        <div>
            <label class="block text-sm">Address</label>
            <input name="dia_chi" value="{{ old('dia_chi') }}" class="border p-2 w-full" />
        </div>
        <div>
            <label class="block text-sm">Role</label>
            <select name="vai_tro" class="border p-2 w-full">
                <option value="admin">admin</option>
                <option value="nhan_vien">nhan_vien</option>
                <option value="khach_hang">khach_hang</option>
            </select>
        </div>
        <div>
            <label class="block text-sm">Status</label>
            <select name="trang_thai" class="border p-2 w-full">
                <option value="hoat_dong">hoat_dong</option>
                <option value="khoa">khoa</option>
            </select>
        </div>
        <button class="btn-success btn-animate btn-pulse">Save User</button>
    </form>
@endsection
