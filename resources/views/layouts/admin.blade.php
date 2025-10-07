@extends('layouts.base')

@section('title', 'Admin - ' . ($title ?? 'Dashboard'))

@section('content')
    @section('fullwidth')@endsection
    @section('hideGlobalFooter')@endsection
    <div class="layout">
        @section('fullwidth_header')
            <div class="bg-white shadow-sm">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    @include('partials.admin.header')
                </div>
            </div>
        @endsection

        <!-- Mobile backdrop shown when sidebar opens -->
        <div id="admin-backdrop" class="fixed inset-0 bg-black bg-opacity-40 z-20 hidden md:hidden transition-opacity duration-200"></div>

        <div class="flex gap-6">
            @include('partials.admin.sidebar')
            <div class="main flex-1">
                @if(session('success'))
                    <div class="mb-4 px-4 py-2 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="mb-4 px-4 py-2 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
                @endif

                @yield('admin_content')
            </div>
        </div>

        @section('fullwidth_footer')
            <div class="bg-white mt-6">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    @include('partials.admin.footer')
                </div>
            </div>
        @endsection
    </div>
@endsection
