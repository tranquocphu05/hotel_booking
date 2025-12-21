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
                            <img src="{{ asset('img/blog/blog-details/blog-details-1.jpg') }}" alt="Hình ảnh Blog 1" class="w-full h-auto object-cover" loading="lazy" decoding="async">
                        </div>
                        <div class="overflow-hidden rounded-lg shadow-md">
                            <img src="{{ asset('img/blog/blog-details/blog-details-2.jpg') }}" alt="Hình ảnh Blog 2" class="w-full h-auto object-cover" loading="lazy" decoding="async">
                        </div>
                        <div class="overflow-hidden rounded-lg shadow-md">
                            <img src="{{ asset('img/blog/blog-details/blog-details-3.jpg') }}" alt="Hình ảnh Blog 3" class="w-full h-auto object-cover" loading="lazy" decoding="async">
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
            <h2 class="text-3xl md:text-4xl font-serif font-bold text-gray-900 mb-2">Bài viết Đề xuất</h2>
            <p class="text-gray-600">Khám phá thêm những bài viết thú vị khác</p>
        </div>
        
        @if(isset($relatedPosts) && $relatedPosts->count() > 0)
            {{-- Swiper Container --}}
            <div class="relative">
                <div class="swiper relatedPostsSwiper">
                    <div class="swiper-wrapper">
                        @foreach ($relatedPosts as $item)
                            <div class="swiper-slide">
                                <div class="relative h-80 rounded-lg overflow-hidden shadow-xl group cursor-pointer">
                                    <a href="{{ route('client.tintuc.show', $item->slug) }}" class="block h-full">
                                        <div class="absolute inset-0 bg-cover bg-center transition-transform duration-500 group-hover:scale-110" 
                                            style="background-image: url('{{ $item->hinh_anh ? asset($item->hinh_anh) : 'https://placehold.co/600x400/D9D9D9/333333?text=Hotel+Blog' }}');">
                                        </div>
                                        
                                        {{-- Overlay & Nội dung --}}
                                        <div class="absolute inset-0 bg-gradient-to-t from-black via-black/50 to-transparent group-hover:from-black/80 transition duration-300"></div>
                                        
                                        <div class="absolute bottom-0 left-0 right-0 p-5 text-white z-10">
                                            <span class="inline-block bg-red-600 text-white text-xs uppercase px-3 py-1 mb-2 font-semibold rounded-full tracking-wider">
                                                Tin tức
                                            </span>
                                            <h4 class="text-xl font-serif font-bold leading-snug mb-2 group-hover:text-red-300 transition">
                                                {{ Str::limit($item->tieu_de, 60) }}
                                            </h4>
                                            <div class="flex items-center gap-4 text-sm opacity-90">
                                                <div class="flex items-center">
                                                    <i class="fa fa-clock mr-2 text-red-400"></i> 
                                                    <span>{{ $item->created_at->format('d/m/Y') }}</span>
                                                </div>
                                                <div class="flex items-center">
                                                    <i class="fa fa-eye mr-2 text-red-400"></i> 
                                                    <span>{{ number_format($item->luot_xem) }} lượt xem</span>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    {{-- Navigation Buttons --}}
                    <div class="swiper-button-next relatedPostsNext"></div>
                    <div class="swiper-button-prev relatedPostsPrev"></div>
                    
                    {{-- Pagination --}}
                    <div class="swiper-pagination relatedPostsPagination"></div>
                </div>
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-newspaper text-gray-300 text-5xl mb-4"></i>
                <p class="text-gray-500 text-lg">Chưa có bài viết liên quan nào.</p>
            </div>
        @endif
    </div>
</section>
{{-- END BÀI VIẾT ĐỀ XUẤT --}}

@push('styles')
<style>
    /* Related Posts Swiper Styles */
    .relatedPostsSwiper {
        padding: 20px 50px 60px !important;
        overflow: visible;
    }

    .relatedPostsSwiper .swiper-slide {
        height: auto;
        transition: transform 0.3s ease;
    }

    .relatedPostsSwiper .swiper-slide:hover {
        transform: translateY(-5px);
    }

    /* Navigation Buttons */
    .relatedPostsNext,
    .relatedPostsPrev {
        width: 44px;
        height: 44px;
        background: white;
        border-radius: 50%;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        color: #dc2626;
        transition: all 0.3s ease;
    }

    .relatedPostsNext:hover,
    .relatedPostsPrev:hover {
        background: #dc2626;
        color: white;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        transform: scale(1.1);
    }

    .relatedPostsNext::after,
    .relatedPostsPrev::after {
        font-size: 18px;
        font-weight: bold;
    }

    .relatedPostsNext {
        right: 0;
    }

    .relatedPostsPrev {
        left: 0;
    }

    /* Pagination */
    .relatedPostsPagination {
        bottom: 20px !important;
    }

    .relatedPostsPagination .swiper-pagination-bullet {
        width: 12px;
        height: 12px;
        background: #dc2626;
        opacity: 0.3;
        transition: all 0.3s ease;
    }

    .relatedPostsPagination .swiper-pagination-bullet-active {
        opacity: 1;
        transform: scale(1.2);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .relatedPostsSwiper {
            padding: 20px 40px 60px !important;
        }

        .relatedPostsNext,
        .relatedPostsPrev {
            width: 36px;
            height: 36px;
        }

        .relatedPostsNext::after,
        .relatedPostsPrev::after {
            font-size: 14px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Swiper === 'undefined') {
        console.error('Swiper library not found!');
        return;
    }

    const relatedPostsSwiperEl = document.querySelector('.relatedPostsSwiper');
    if (relatedPostsSwiperEl) {
        const postCount = {{ isset($relatedPosts) ? $relatedPosts->count() : 0 }};
        const shouldLoop = postCount > 3;
        
        const swiper = new Swiper('.relatedPostsSwiper', {
            // Số slide hiển thị
            slidesPerView: 1,
            slidesPerGroup: 1,
            spaceBetween: 24,

            // Tự động chuyển slide (chỉ khi có nhiều slide)
            autoplay: shouldLoop ? {
                delay: 4000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            } : false,

            // Loop (chỉ khi có nhiều hơn số slide hiển thị)
            loop: shouldLoop,

            // Navigation buttons
            navigation: {
                nextEl: '.relatedPostsNext',
                prevEl: '.relatedPostsPrev',
            },

            // Pagination
            pagination: {
                el: '.relatedPostsPagination',
                clickable: true,
                dynamicBullets: postCount > 5,
            },

            // Responsive breakpoints
            breakpoints: {
                640: {
                    slidesPerView: 2,
                    slidesPerGroup: 2,
                    spaceBetween: 20,
                },
                1024: {
                    slidesPerView: 3,
                    slidesPerGroup: 3,
                    spaceBetween: 24,
                },
            },

            // Hiệu ứng chuyển slide
            effect: 'slide',
            speed: 600,

            // Grab cursor
            grabCursor: true,

            // Ẩn navigation nếu không đủ slide
            on: {
                init: function() {
                    if (postCount <= 3) {
                        const nextBtn = document.querySelector('.relatedPostsNext');
                        const prevBtn = document.querySelector('.relatedPostsPrev');
                        if (nextBtn) nextBtn.style.display = 'none';
                        if (prevBtn) prevBtn.style.display = 'none';
                    }
                }
            }
        });
    }
});
</script>
@endpush

@endsection