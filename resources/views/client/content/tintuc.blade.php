@extends('layouts.client')

@section('title', 'Tin Tức & Blog')

@section('client_content')

<div class="relative w-full bg-cover bg-center bg-no-repeat"
    style="background-image: url('{{ asset('img/blog/blog-13.jpg') }}');">

    {{-- Lớp phủ tối giúp chữ nổi bật --}}
    <div class="absolute inset-0 bg-black bg-opacity-40"></div>

    <div class="relative py-28 px-4 text-center text-white">
        <div class="text-lg text-gray-200 mb-4">
            <a href="{{ url('/') }}" class="hover:text-[#FFD700] transition-colors">Trang Chủ</a>
            <span class="mx-2 text-gray-300">/</span>
            <span class="text-[#FFD700] font-semibold">Tin Tức</span>
        </div>

        <h2 class="text-5xl md:text-7xl font-bold mb-4">Tin Tức Khách Sạn</h2>

        <p class="text-lg md:text-xl text-gray-100 leading-relaxed max-w-3xl mx-auto">
            Cập nhật những tin tức, sự kiện và ưu đãi mới nhất từ Ozia Hotel để bạn luôn là người đầu tiên biết đến.
        </p>
    </div>
</div>

    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4">

            @if ($posts->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

                    @foreach ($posts as $post)
                        <div class="relative h-96 rounded-lg overflow-hidden shadow-xl hover:shadow-2xl transition duration-300 group"
                            style="background-image: url('{{ $post->hinh_anh ? asset($post->hinh_anh) : 'https://placehold.co/600x400/D9D9D9/333333?text=Hotel+Blog' }}'); background-size: cover; background-position: center;">

                            <div
                                class="absolute inset-0 bg-black bg-opacity-30 group-hover:bg-opacity-10 transition duration-300">
                            </div>

                            <div class="absolute bottom-0 left-0 p-6 text-white z-10">
                                <span
                                    class="inline-block bg-red-600 text-white text-xs uppercase px-3 py-1 mb-3 font-semibold rounded-full tracking-wider">
                                    Tin tức
                                </span>

                                <h4 class="text-2xl font-serif font-bold leading-snug hover:text-red-300 transition">
                                    <a href="{{ route('client.tintuc.show', $post->slug) }}">{{ $post->tieu_de }}</a>
                                </h4>
                                <div class="text-sm mt-2 flex items-center opacity-90">
                                    <i class="fa fa-clock mr-2 text-red-400"></i> {{ $post->created_at->format('d/m/Y') }}
                                </div>
                                <div class="text-sm mt-1 flex items-center opacity-90">
                                    <i class="fa fa-eye mr-2 text-red-400"></i> {{ number_format($post->luot_xem) }} lượt
                                    xem
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>

                @if ($posts->hasPages())
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
