@extends('layouts.admin')

@section('title','Dashboard')

@section('admin_content')
    <h1>Admin Dashboard</h1>
    <p>Welcome, admin.</p>
    <div class="mt-4">
        <a href="{{ route('admin.users.index') }}" class="px-3 py-1 bg-blue-600 text-white rounded">Manage Users</a>
    </div>
@endsection
