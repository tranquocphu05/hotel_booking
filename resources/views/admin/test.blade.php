@extends('layouts.admin')

@section('title', 'Test Page')

@section('admin_content')
<div class="bg-white rounded-lg shadow p-6">
    <h1 class="text-2xl font-bold text-gray-900 mb-4">Test Page</h1>
    <p class="text-gray-600">Nếu bạn thấy nội dung này, layout đã hoạt động đúng!</p>
    
    <div class="mt-4">
        <button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Test Button
        </button>
    </div>
    
    <div class="mt-4">
        <table class="min-w-full bg-white border border-gray-200">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-4 py-2 text-left">ID</th>
                    <th class="px-4 py-2 text-left">Name</th>
                    <th class="px-4 py-2 text-left">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="px-4 py-2">1</td>
                    <td class="px-4 py-2">Test Item</td>
                    <td class="px-4 py-2">
                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Active</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection


