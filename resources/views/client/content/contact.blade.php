@extends('layouts.client')

@section('client_content')
    <!-- BREADCRUMB -->
<div class="relative w-full bg-cover bg-center bg-no-repeat"
    style="background-image: url('{{ asset('img/blog/blog-14.jpg') }}');">

    {{-- Lớp phủ tối giúp chữ nổi bật --}}
    <div class="absolute inset-0 bg-black bg-opacity-40"></div>

    <div class="relative py-28 px-4 text-center text-white">
        <div class="text-lg text-gray-200 mb-4">
            <a href="{{ url('/') }}" class="hover:text-[#FFD700] transition-colors">Trang Chủ</a>
            <span class="mx-2 text-gray-300">/</span>
            <span class="text-[#FFD700] font-semibold">Liên Hệ</span>
        </div>

        <h2 class="text-5xl md:text-7xl font-bold mb-4">Liên Hệ Với Chúng Tôi</h2>

        <p class="text-lg md:text-xl text-gray-100 leading-relaxed max-w-3xl mx-auto">
            Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn. Hãy liên hệ ngay để được tư vấn và giải đáp nhanh chóng.
        </p>
    </div>
</div>

    <!-- CONTACT SECTION -->
    <section class="max-w-7xl mx-auto px-6 py-20">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">

            <!-- Google Map -->
            <div class="shadow-2xl rounded-2xl overflow-hidden transform hover:scale-[1.02] transition duration-700 ml-[-40px]">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3022.0606825994123!2d-72.8735845851828!3d40.760690042573295!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89e85b24c9274c91%3A0xf310d41b791bcb71!2sWilliam%20Floyd%20Pkwy%2C%20Mastic%20Beach%2C%20NY%2C%20USA!5e0!3m2!1sen!2sbd!4v1578582744646!5m2!1sen!2sbd"
                    class="w-full h-[550px] border-0" allowfullscreen="" loading="lazy"></iframe>
            </div>

            <!-- Contact Info -->
            <div class="relative mr-[-40px]">
                <h3
                    class="text-3xl md:text-4xl font-bold text-gray-800">
                    Thông Tin Liên Hệ
                    <span
                        class="block w-28 h-[4px] bg-gradient-to-r from-yellow-400 via-amber-500 to-yellow-600 mt-3 rounded-full shadow-lg shadow-yellow-300"></span>
                </h3>

                <p class="text-gray-700 mb-10 leading-[1.9] text-[16px]">
                    Chào mừng đến với <span class="text-yellow-500 font-semibold">Ozia Hotel</span> – điểm dừng chân lý
                    tưởng cho những ai yêu thích sự tinh tế và yên bình. Tại đây, mỗi khoảnh khắc nghỉ dưỡng đều được nâng
                    niu bằng dịch vụ tận tâm, không gian đẳng cấp và trải nghiệm khó quên giữa lòng thành phố.
                </p>

                <ul class="text-gray-700 space-y-5 text-[16px] leading-[1.8]">
                    <li class="flex items-start">
                        <span class="text-yellow-500 mr-3 mt-[2px]"><i class="fas fa-map-marker-alt"></i></span>
                        <span><strong>Địa chỉ:</strong> 1481 Creekside Lane, Avila Beach, California, USA</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-yellow-500 mr-3 mt-[2px]"><i class="fas fa-phone-alt"></i></span>
                        <span><strong>Điện thoại:</strong> +84 0123456789</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-yellow-500 mr-3 mt-[2px]"><i class="fas fa-envelope"></i></span>
                        <span><strong>Email:</strong> info@oziahotel.com</span>
                    </li>
                </ul>

                <div class="flex space-x-6 mt-10 text-gray-500 text-xl">
                    <a href="#" class="hover:text-yellow-500 transition transform hover:scale-110"><i
                            class="fab fa-facebook-f"></i></a>
                    <a href="#" class="hover:text-yellow-500 transition transform hover:scale-110"><i
                            class="fab fa-twitter"></i></a>
                    <a href="#" class="hover:text-yellow-500 transition transform hover:scale-110"><i
                            class="fab fa-instagram"></i></a>
                    <a href="#" class="hover:text-yellow-500 transition transform hover:scale-110"><i
                            class="fab fa-linkedin-in"></i></a>
                </div>

                <!-- Decorative gold line -->
                <div
                    class="absolute -bottom-6 left-0 w-28 h-[2px] bg-gradient-to-r from-yellow-400 via-amber-500 to-transparent opacity-80"></div>
            </div>

        </div>
    </section>
@endsection
