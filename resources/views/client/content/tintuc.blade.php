@extends('layouts.client')

@section('title', 'Tin Tức & Blog')

@section('client_content')

<div class="bg-gray-50 py-16 bg-cover bg-center">
    <div class="max-w-7xl mx-auto px-4 text-center text-gray-800">
        <h2 class="text-4xl font-serif font-bold mb-3">Tin Tức Khách Sạn</h2>
        <div class="text-lg text-gray-600">
            <a href="{{ url('/') }}" class="hover:text-red-600 transition">Trang Chủ</a>
            <span class="mx-2">/</span>
            <span class="font-semibold text-gray-800">Tin Tức</span>
        </div>
    </div>
</div>
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        
        @if ($posts->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                @foreach ($posts as $post)
                    <div class="relative h-96 rounded-lg overflow-hidden shadow-xl hover:shadow-2xl transition duration-300 group"
                            style="background-image: url('{{ $post->hinh_anh ? asset($post->hinh_anh) : 'https://placehold.co/600x400/D9D9D9/333333?text=Hotel+Blog' }}'); background-size: cover; background-position: center;">
                        
                        <div class="absolute inset-0 bg-black bg-opacity-30 group-hover:bg-opacity-10 transition duration-300"></div>
                        
                        <div class="absolute bottom-0 left-0 p-6 text-white z-10">
                            <span class="inline-block bg-red-600 text-white text-xs uppercase px-3 py-1 mb-3 font-semibold rounded-full tracking-wider">
                                Tin tức
                            </span>
                            
                            <h4 class="text-2xl font-serif font-bold leading-snug hover:text-red-300 transition">
                                <a href="{{ route('client.tintuc.show', $post->slug) }}">{{ $post->tieu_de }}</a>
                            </h4>
                            <div class="text-sm mt-2 flex items-center opacity-90">
                                <i class="fa fa-clock mr-2 text-red-400"></i> {{ $post->created_at->format('d/m/Y') }}
                            </div>
                            <div class="text-sm mt-1 flex items-center opacity-90">
                                <i class="fa fa-eye mr-2 text-red-400"></i> {{ number_format($post->luot_xem) }} lượt xem
                            </div>
                        </div>
                    </div>
                @endforeach
                
            </div>

            @if($posts->hasPages())
                <div class="text-center mt-12">
                    {{ $posts->links() }}
                </div>
            @endif
        @else
            <p class="text-center text-gray-500 text-lg py-10">Hiện chưa có bài viết nào được đăng tải.</p>
        @endif
    </div>
</section>
@endsection