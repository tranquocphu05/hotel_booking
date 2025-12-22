@extends('layouts.admin')

@section('title','Users')

@section('admin_content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold">Users</h1>
        <!-- Nút thêm user: Chỉ Admin -->
        @hasRole('admin')
        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Create User
        </a>
        @endhasRole
    </div>

    <div class="bg-white rounded-2xl shadow p-6 mt-8 mb-8 w-full">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600 border border-gray-200 rounded-lg">
                <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-3 py-3">ID</th>
                        <th class="px-3 py-3">USERNAME</th>
                        <th class="px-3 py-3">EMAIL</th>
                        <th class="px-3 py-3">SĐT</th>
                        <th class="px-2 py-3">CCCD</th>
                        <th class="px-2 py-3 whitespace-nowrap">ROLE</th>
                        <th class="px-2 py-3 whitespace-nowrap">STATUS</th>
                        <th class="px-3 py-3 text-center whitespace-nowrap">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $u)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2">{{ $u->id }}</td>
                            <td class="px-3 py-2">{{ $u->username }}</td>
                            <td class="px-3 py-2">{{ $u->email }}</td>
                            <td class="px-3 py-2">{{ $u->sdt ?? 'Chưa cập nhật' }}</td>
                            <td class="px-2 py-2">{{ $u->cccd ?? 'Chưa cập nhật' }}</td>
                            <td class="px-2 py-2 whitespace-nowrap">
                                @php
                                    $roleNames = [
                                        'admin' => 'Admin',
                                        'nhan_vien' => 'Nhân viên',
                                        'le_tan' => 'Lễ tân',
                                        'khach_hang' => 'Khách hàng'
                                    ];
                                @endphp
                                <span class="px-1.5 py-0.5 text-xs rounded-full whitespace-nowrap
                                    {{ $u->vai_tro === 'admin' ? 'bg-purple-100 text-purple-700' : '' }}
                                    {{ $u->vai_tro === 'nhan_vien' ? 'bg-blue-100 text-blue-700' : '' }}
                                    {{ $u->vai_tro === 'le_tan' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $u->vai_tro === 'khach_hang' ? 'bg-gray-100 text-gray-700' : '' }}">
                                    {{ $roleNames[$u->vai_tro] ?? $u->vai_tro }}
                                </span>
                            </td>
                            <td class="px-2 py-2 whitespace-nowrap">
                                @if($u->trang_thai === 'hoat_dong')
                                    <span class="px-1.5 py-0.5 text-xs rounded-full bg-green-100 text-green-700">Active</span>
                                @else
                                    <span class="px-1.5 py-0.5 text-xs rounded-full bg-red-100 text-red-700">Locked</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-center space-x-2 whitespace-nowrap">
                                @hasRole('admin')
                                @php
                                    $isAdmin = $u->vai_tro === 'admin';
                                    $isActive = $u->trang_thai === 'hoat_dong';
                                    $hasMultipleActiveAdmins = ($activeAdminCount ?? 0) > 1;
                                    // Cho phép sửa nếu: không phải admin HOẶC (là admin VÀ (có >= 2 admin active HOẶC admin đó đang bị khóa))
                                    $canEdit = !$isAdmin || ($hasMultipleActiveAdmins || !$isActive);
                                    // Cho phép khóa/mở nếu: không phải admin HOẶC (là admin VÀ (đang active thì cần >= 2 admin active, đang locked thì luôn cho mở))
                                    $canToggle = !$isAdmin || ($isActive ? $hasMultipleActiveAdmins : true);
                                @endphp
                                
                                @if($canEdit)
                                    <a href="{{ route('admin.users.edit', $u) }}" class="inline-flex items-center px-2.5 py-1 rounded bg-yellow-500 text-white text-xs font-semibold hover:bg-yellow-600 transition whitespace-nowrap">Sửa</a>
                                @else
                                    <span class="inline-block max-w-[140px] text-xs text-gray-400 italic truncate align-middle">Admin cuối cùng (không sửa)</span>
                                @endif

                                @if($canToggle)
                                    <form method="POST" action="{{ route('admin.users.toggle', $u) }}" style="display:inline">
                                        @csrf
                                        @method('PUT')
                                        @if($u->trang_thai === 'hoat_dong')
                                            <button type="submit" class="inline-flex items-center px-2.5 py-1 rounded bg-red-500 text-white text-xs font-semibold hover:bg-red-600 transition whitespace-nowrap" onclick="return confirm('Vô hiệu hóa tài khoản này?')">Khóa</button>
                                        @else
                                            <button type="submit" class="inline-flex items-center px-2.5 py-1 rounded bg-green-500 text-white text-xs font-semibold hover:bg-green-600 transition whitespace-nowrap" onclick="return confirm('Kích hoạt lại tài khoản này?')">Mở</button>
                                        @endif
                                    </form>
                                @else
                                    <span class="inline-block max-w-[160px] text-xs text-gray-400 italic truncate align-middle">Admin cuối cùng (không vô hiệu hóa)</span>
                                @endif
                                @else
                                {{-- Nhân viên và Lễ tân chỉ xem --}}
                                <span class="text-xs text-gray-400 italic">Chỉ xem</span>
                                @endhasRole
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
