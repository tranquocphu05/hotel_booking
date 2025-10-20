@extends('layouts.client')

@section('title', $post->tieu_de ?? 'Chi tiết Tin tức')

@section('client_content')


<div class="bg-gray-50 py-16 bg-cover bg-center">
    <div class="max-w-7xl mx-auto px-4 text-center text-gray-800">
        <h2 class="text-4xl font-serif font-bold mb-3">Tin Tức Khách Sạn</h2>    
        <div class="text-lg text-gray-600 flex justify-center items-center flex-wrap"> 
            
            <a href="{{ url('/') }}" class="hover:text-red-600 transition whitespace-nowrap">Trang Chủ</a>
            <span class="mx-2">/</span>
            <a href="{{ route('client.tintuc') }}" class="hover:text-red-600 transition whitespace-nowrap">Tin Tức</a>
            <span class="mx-2">/</span>
        
            <span class="font-bold text-gray-900 
                         max-w-full md:max-w-lg lg:max-w-xl 
                         overflow-hidden line-clamp-1" 
                  title="{{ $post->tieu_de ?? 'N/A' }}">
                {{ $post->tieu_de ?? 'Đang tải...' }}
            </span>
        </div>
    </div>
</div>

<section class="relative py-24 md:py-32 bg-cover bg-center" 
    style="background-image: url('{{ $post->hinh_anh ? asset($post->hinh_anh) : asset('img/blog/blog-details/blog-details-hero.jpg') }}');">
    
    <div class="absolute inset-0 bg-black bg-opacity-40"></div>
    
    <div class="container max-w-7xl mx-auto px-4 relative z-10">
        <div class="flex justify-center">
            <div class="w-full lg:w-10/12 xl:w-8/12 text-center text-white">
                <div class="bd-hero-text">
                    <span class="inline-block bg-red-600 text-white text-sm uppercase px-3 py-1 mb-4 font-semibold rounded-full tracking-wider">
                        Tin tức
                    </span>
                    <h1 class="text-4xl md:text-5xl font-serif font-bold mb-5 leading-tight">
                        {{ $post->tieu_de ?? 'Tiêu đề bài viết' }}
                    </h1>
                    <ul class="flex justify-center items-center space-x-6 text-sm opacity-90">
                        <li class="flex items-center">
                            <i class="fa fa-clock mr-2 text-red-400"></i> {{ $post->created_at->format('d/m/Y') }}
                        </li>
                        <li class="flex items-center">
                            <i class="fa fa-user mr-2 text-red-400"></i> {{ $post->admin->ho_ten ?? 'Admin' }}
                        </li>
                        <li class="flex items-center">
                            <i class="fa fa-eye mr-2 text-red-400"></i> {{ number_format($post->luot_xem) }} lượt xem
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="py-16 md:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-center">
            <div class="w-full lg:w-10/12 xl:w-8/12">
                <div class="blog-details-text space-y-8">
                    
                    <div class="bd-title prose max-w-none text-lg text-gray-700 leading-relaxed space-y-6">
                        <p class="text-xl font-semibold text-gray-800 mb-4">{{ $post->tom_tat }}</p>
                        <div class="content">
                            {!! nl2br(e($post->noi_dung)) !!}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 py-4">
                        <div class="overflow-hidden rounded-lg shadow-md">
                            <img src="{{ asset('img/blog/blog-details/blog-details-1.jpg') }}" alt="Hình ảnh Blog 1" class="w-full h-auto object-cover">
                        </div>
                        <div class="overflow-hidden rounded-lg shadow-md">
                            <img src="{{ asset('img/blog/blog-details/blog-details-2.jpg') }}" alt="Hình ảnh Blog 2" class="w-full h-auto object-cover">
                        </div>
                        <div class="overflow-hidden rounded-lg shadow-md">
                            <img src="{{ asset('img/blog/blog-details/blog-details-3.jpg') }}" alt="Hình ảnh Blog 3" class="w-full h-auto object-cover">
                        </div>
                    </div>
                    
                    <div class="space-y-6 pt-4 text-gray-700">
                        <div class="bm-item">
                            <h4 class="text-xl font-bold text-gray-900 mb-2">Nếu bạn sống ở Thành phố New York</h4>
                            <p>Bạn biết tất cả về giao thông ở đó. Việc di chuyển thường gần như không thể, ngay cả với hàng nghìn
                                chiếc taxi màu vàng. Nếu bạn giống tôi, bạn thường nhìn với sự ghen tị những chiếc limousine sáng bóng
                                với tài xế đồng phục và ước rằng bạn có thể ngồi trong một chiếc.</p>
                        </div>
                    </div>
                    
                    {{-- Thẻ (Tags) & Chia sẻ (Share) --}}
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-t border-b border-gray-200 py-6 space-y-4 md:space-y-0">
                        <div class="tags flex flex-wrap gap-2">
                            <span class="font-semibold text-gray-800 mr-2">Thẻ (Tags):</span>
                            <a href="#" class="text-sm bg-gray-100 hover:bg-red-100 text-gray-700 px-3 py-1 rounded-full transition duration-300">
                                Tin tức
                            </a>
                            <a href="#" class="text-sm bg-gray-100 hover:bg-red-100 text-gray-700 px-3 py-1 rounded-full transition duration-300">
                                Khách sạn
                            </a>
                        </div>
                        <div class="social-share flex items-center space-x-3">
                            <span class="font-semibold text-gray-800">Chia sẻ:</span>
                            <a href="#" class="text-gray-500 hover:text-red-600 transition"><i class="fa fa-facebook text-lg"></i></a>
                            <a href="#" class="text-gray-500 hover:text-red-600 transition"><i class="fa fa-twitter text-lg"></i></a>
                            <a href="#" class="text-gray-500 hover:text-red-600 transition"><i class="fa fa-tripadvisor text-lg"></i></a>
                            <a href="#" class="text-gray-500 hover:text-red-600 transition"><i class="fa fa-instagram text-lg"></i></a>
                            <a href="#" class="text-gray-500 hover:text-red-600 transition"><i class="fa fa-youtube-play text-lg"></i></a>
                        </div>
                    </div>
                    
                    {{-- Phần Bình luận --}}
                    <div class="comment-option pt-8">
                        <h4 class="text-2xl font-serif font-bold text-gray-900 mb-6 border-b pb-3">2 Bình luận</h4>
                        
                        {{-- Mục bình luận đơn --}}
                        <div class="single-comment-item flex space-x-4 mb-8">
                            <div class="sc-author flex-shrink-0">
                                <img src="{{ asset('img/blog/blog-details/avatar/avatar-1.jpg') }}" alt="Ảnh đại diện" class="w-16 h-16 rounded-full object-cover shadow-md">
                            </div>
                            <div class="sc-text flex-grow">
                                <span class="text-xs text-gray-500">27 Thg 8, 2019</span>
                                <h5 class="text-lg font-bold text-gray-800 mt-1">Brandon Kelley</h5>
                                <p class="text-gray-700 mt-2">Nội dung bình luận mẫu.</p>
                                <div class="mt-3 space-x-3">
                                    <a href="#" class="text-sm text-red-600 hover:text-red-800 font-semibold transition">Thích</a>
                                    <a href="#" class="text-sm text-red-600 hover:text-red-800 font-semibold transition">Trả lời</a>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Form để lại Bình luận --}}
                        <div class="leave-comment pt-10">
                            <h4 class="text-2xl font-serif font-bold text-gray-900 mb-6">Để lại Bình luận</h4>
                            <form action="#" class="comment-form space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <input type="text" placeholder="Tên" class="w-full p-3 border border-gray-300 rounded focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                                    <input type="text" placeholder="Email" class="w-full p-3 border border-gray-300 rounded focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                                </div>
                                <input type="text" placeholder="Trang web (Website)" class="w-full p-3 border border-gray-300 rounded focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                                <textarea placeholder="Nội dung" rows="6" class="w-full p-3 border border-gray-300 rounded focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50 resize-none"></textarea>
                                <div class="text-center md:text-left">
                                    <button type="submit" class="site-btn inline-block px-8 py-3 bg-red-600 text-white font-semibold uppercase tracking-wider rounded transition duration-300 hover:bg-red-700">
                                        Gửi Tin nhắn
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="py-16 md:py-24 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-serif font-bold text-gray-900">Bài viết Đề xuất</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            @if(isset($relatedPosts) && $relatedPosts->count() > 0)
                @foreach ($relatedPosts as $item)
                    <div class="relative h-80 rounded-lg overflow-hidden shadow-xl group">
                        <div class="absolute inset-0 bg-cover bg-center transition-transform duration-500 group-hover:scale-105" 
                            style="background-image: url('{{ $item->hinh_anh ? asset($item->hinh_anh) : 'https://placehold.co/600x400/D9D9D9/333333?text=Hotel+Blog' }}');">
                        </div>
                        
                        {{-- Overlay & Nội dung --}}
                        <div class="absolute inset-0 bg-black bg-opacity-40 group-hover:bg-opacity-20 transition duration-300"></div>
                        
                        <div class="absolute bottom-0 left-0 p-5 text-white z-10">
                            <span class="inline-block bg-red-600 text-white text-xs uppercase px-3 py-1 mb-2 font-semibold rounded-full tracking-wider">
                                Tin tức
                            </span>
                            <h4 class="text-xl font-serif font-bold leading-snug hover:text-red-300 transition">
                                <a href="{{ route('client.tintuc.show', $item->slug) }}">{{ $item->tieu_de }}</a>
                            </h4>
                            <div class="text-sm mt-1 flex items-center opacity-90">
                                <i class="fa fa-clock mr-2 text-red-400"></i> {{ $item->created_at->format('d/m/Y') }}
                            </div>
                            <div class="text-sm mt-1 flex items-center opacity-90">
                                <i class="fa fa-eye mr-2 text-red-400"></i> {{ number_format($item->luot_xem) }} lượt xem
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-span-full text-center text-gray-500">
                    <p>Chưa có bài viết liên quan nào.</p>
                </div>
            @endif
            
        </div>
    </div>
</section>
{{-- END BÀI VIẾT ĐỀ XUẤT --}}

@endsection