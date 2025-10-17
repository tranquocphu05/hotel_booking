@extends('layouts.client') 

{{-- 1. BANNER/HEADER (FULL-WIDTH) --}}
@section('fullwidth_header')
    @include('client.header.header') 
@endsection

{{-- 2. NỘI DUNG CHÍNH (CONTAINER GIỚI HẠN) --}}
@section('client_content') 
    
  @include('client.content.content')
@endsection
