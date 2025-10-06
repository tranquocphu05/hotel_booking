@extends('layouts.admin')

@section('title','Create User')

@section('admin_content')
    <h1>Create User</h1>
    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm">Username</label>
            <input name="username" class="border p-2 w-full" />
        </div>
        <div>
            <label class="block text-sm">Email</label>
            <input name="email" class="border p-2 w-full" />
        </div>
        <div>
            <label class="block text-sm">Password</label>
            <input name="password" type="password" class="border p-2 w-full" />
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
        <button class="px-3 py-1 bg-green-600 text-white rounded">Save</button>
    </form>
@endsection
