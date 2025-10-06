@extends('layouts.client')

@section('title','Loại phòng')

@section('client_content')
    <h1>Danh sách loại phòng</h1>
    <ul>
        @foreach($items as $it)
            <li>{{ $it->ten_loai }} - {{ $it->gia_co_ban }}</li>
        @endforeach
    </ul>
@endsection
