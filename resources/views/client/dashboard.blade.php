{{-- resources/views/client/dashboard.blade.php (Hoặc home.blade.php) --}}

@extends('layouts.base') 
{{-- extends file layout vừa sửa --}}

{{-- ĐẨY HEADER LÊN @yield('fullwidth_header') --}}
@section('fullwidth_header')
    @include('client.header.header') 
@endsection

{{-- NỘI DUNG CHÍNH (Chứa các khối giới hạn) LÊN @yield('content') --}}
@section('content')
    <div class="main">
        {{-- Bằng cách @include ở đây, nội dung giới hạn sẽ nằm trong div.main --}}
        @include('client.content.content')
    </div>
@endsection