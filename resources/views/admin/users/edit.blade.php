@extends('layouts.admin')

@section('title','Edit User')

@section('admin_content')
    <div class="flex items-center justify-between mb-4">
        <h1>Edit User</h1>
    </div>
    <form id="user-form" method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
        @csrf
        @method('PUT')
        <div class="flex justify-end">
            <button type="submit" class="px-3 py-1 bg-green-600 text-white rounded">Update</button>
        </div>
        <div>
            <label class="block text-sm">Username</label>
            <input name="username" value="{{ $user->username }}" class="border p-2 w-full" />
        </div>
        <div>
            <label class="block text-sm">Email</label>
            <input name="email" value="{{ $user->email }}" class="border p-2 w-full" />
        </div>
        <div>
            <label class="block text-sm">Password (leave blank to keep)</label>
            <input name="password" type="password" class="border p-2 w-full" />
        </div>
        <div>
            <label class="block text-sm">Role</label>
            <select name="vai_tro" class="border p-2 w-full">
                <option value="admin" {{ $user->vai_tro=='admin'?'selected':'' }}>admin</option>
                <option value="nhan_vien" {{ $user->vai_tro=='nhan_vien'?'selected':'' }}>nhan_vien</option>
                <option value="khach_hang" {{ $user->vai_tro=='khach_hang'?'selected':'' }}>khach_hang</option>
            </select>
        </div>
        <div>
            <label class="block text-sm">Status</label>
            <select name="trang_thai" class="border p-2 w-full">
                <option value="hoat_dong" {{ $user->trang_thai=='hoat_dong'?'selected':'' }}>hoat_dong</option>
                <option value="khoa" {{ $user->trang_thai=='khoa'?'selected':'' }}>khoa</option>
            </select>
        </div>
        <button class="px-3 py-1 bg-green-600 text-white rounded">Update</button>
    </form>

    <!-- Sticky submit bar (mobile/long pages) -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t p-3 shadow-lg z-50">
        <div class="max-w-7xl mx-auto flex justify-end">
            <button form="user-form" type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Update</button>
        </div>
    </div>
@endsection
