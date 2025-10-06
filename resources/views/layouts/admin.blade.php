@extends('layouts.base')

@section('title', 'Admin - ' . ($title ?? 'Dashboard'))

@section('content')
    <div class="layout">
        @include('partials.admin.sidebar')
        <div class="main">
            @yield('admin_content')
        </div>
    </div>
@endsection
