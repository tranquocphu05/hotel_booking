@extends('layouts.admin')

@section('title','Users')

@section('admin_content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold">Users</h1>
        <!-- Make the create button more visible -->
        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded shadow"> 
            + Create
        </a>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($users as $u)
                    <tr>
                        <td class="px-6 py-4">{{ $u->id }}</td>
                        <td class="px-6 py-4">{{ $u->username }}</td>
                        <td class="px-6 py-4">{{ $u->email }}</td>
                        <td class="px-6 py-4">{{ $u->vai_tro }}<`/td>
                        <td class="px-6 py-4">
                            @if($u->trang_thai === 'hoat_dong')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Active</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Locked</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.users.edit', $u) }}" class="text-blue-600 mr-2">Edit</a>
                            <!-- Impersonate button: admin can impersonate other users -->
                            @if(auth()->user() && auth()->user()->id !== $u->id)
                                <form method="POST" action="{{ route('admin.impersonate', $u->id) }}" style="display:inline">
                                    @csrf
                                    <button type="submit" class="text-indigo-600 mr-2">Impersonate</button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('admin.users.destroy', $u) }}" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
