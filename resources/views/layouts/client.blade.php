@extends('layouts.base')

@section('title', 'Client - ' . ($title ?? 'Dashboard'))

@section('content')
    
    {{-- 📢 SECTION FULL-WIDTH MỚI - Đặt ngoài div.main --}}
    @yield('fullwidth_content')

    <div class="main w-full">
        @if(session('impersonator_id'))
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4">
                <div class="flex items-center justify-between">
                    <div>You are impersonating another user.</div>
                    <form method="POST" action="{{ route('impersonate.stop.public') }}">
                        @csrf
                        <button class="px-3 py-1 bg-red-600 text-white rounded">Stop impersonation</button>
                    </form>
                </div>
            </div>
        @endif
        
        {{-- SECTION NỘI DUNG GIỚI HẠN (Sẽ được gọi sau) --}}
        @yield('client_content')
    </div>
@endsection