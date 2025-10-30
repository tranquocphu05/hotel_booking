@extends('layouts.admin')

@section('title','Users')

@section('admin_content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold">Users</h1>
        <!-- Make the create button more visible -->
        <a href="{{ route('admin.users.create') }}" class="btn-primary btn-animate inline-flex items-center gap-2"> 
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Create User
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow p-6 mt-8 mb-8 w-full">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-600 border border-gray-200 rounded-lg">
                <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">USERNAME</th>
                        <th class="px-4 py-3">EMAIL</th>
                        <th class="px-4 py-3">SĐT</th>
                        <th class="px-4 py-3">CCCD</th>
                        <th class="px-4 py-3">ROLE</th>
                        <th class="px-4 py-3">STATUS</th>
                        <th class="px-4 py-3 text-center">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $u)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2">{{ $u->id }}</td>
                            <td class="px-4 py-2">{{ $u->username }}</td>
                            <td class="px-4 py-2">{{ $u->email }}</td>
                            <td class="px-4 py-2">{{ $u->sdt ?? 'Chưa cập nhật' }}</td>
                            <td class="px-4 py-2">{{ $u->cccd ?? 'Chưa cập nhật' }}</td>
                            <td class="px-4 py-2">{{ $u->vai_tro }}</td>
                            <td class="px-4 py-2">
                                @if($u->trang_thai === 'hoat_dong')
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">Active</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700">Locked</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-center space-x-2">
                                @if($u->vai_tro === 'admin')
                                    @if(($activeAdminCount ?? 0) > 1)
                                        <a href="{{ route('admin.users.edit', $u) }}" class="table-btn btn-info link-hover">Edit</a>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Admin cuối cùng (không sửa)</span>
                                    @endif
                                @else
                                    <a href="{{ route('admin.users.edit', $u) }}" class="table-btn btn-info link-hover">Edit</a>
                                @endif

                                @if(auth()->user() && auth()->user()->id !== $u->id)
                                    <form method="POST" action="{{ route('admin.impersonate', $u->id) }}" style="display:inline">
                                        @csrf
                                        <button type="submit" class="table-btn btn-warning btn-animate">Impersonate</button>
                                    </form>
                                @endif

                                @if($u->vai_tro !== 'admin' || (($activeAdminCount ?? 0) > 1))
                                    <form method="POST" action="{{ route('admin.users.toggle', $u) }}" style="display:inline">
                                        @csrf
                                        @method('PUT')
                                        @if($u->trang_thai === 'hoat_dong')
                                            <button type="submit" class="table-btn btn-danger btn-animate" onclick="return confirm('Vô hiệu hóa tài khoản này?')">Vô hiệu hóa</button>
                                        @else
                                            <button type="submit" class="table-btn btn-success btn-animate" onclick="return confirm('Kích hoạt lại tài khoản này?')">Kích hoạt</button>
                                        @endif
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400 italic">Admin cuối cùng (không vô hiệu hóa)</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-gray-400">Không có user nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">
            {{ $users->links() }}
        </div>
    </div>
@endsection
