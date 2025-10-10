@extends('layouts.client')

@section('title', 'Dashboard')

@section('client_content')
    <div class="py-6">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h2 class="text-xl font-semibold">Dashboard</h2>
            <div class="mt-4 p-4 bg-green-50 border border-green-100 rounded text-green-800">
                {{ __("You're logged in!") }}
            </div>
        </div>
    </div>
@endsection
