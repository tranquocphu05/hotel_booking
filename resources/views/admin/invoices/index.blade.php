@extends('layouts.admin')

@section('title','Users')

@section('admin_content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold">Users</h1>
        <a href="{{ route('admin.users.create') }}" class="px-3 py-1 bg-blue-600 text-white rounded">Create</a>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
      
    </div>
@endsection
