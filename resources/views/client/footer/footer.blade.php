{{-- resources/views/partials/footer.blade.php --}}
<footer class="bg-gray-900 text-gray-300 pt-12 pb-6">
    <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-3 gap-10">
        {{-- GIỚI THIỆU --}}
        <div class="p-3">
            <div class="flex items-center mb-3">
                <a href="javascript:void(0)" onclick="window.location.reload()"
                    class="text-3xl font-serif font-bold text-white cursor-pointer tracking-wide hover:text-[#D4AF37] transition duration-300">
                    OZIA HOTEL
                </a>
            </div>

            <p class="text-sm text-gray-300 mb-6 leading-relaxed">
                Chào mừng bạn đến với <span class="font-semibold text-[#D4AF37]">Ozia Hotel</span> —
                đối tác đáng tin cậy giúp bạn tìm kiếm những khách sạn chất lượng với mức giá hợp lý.
                Chúng tôi mang đến trải nghiệm đặt phòng tiện lợi, nhanh chóng và an toàn trên toàn quốc.
            </p>

            <div class="flex space-x-4 text-gray-400">
                <a href="#" class="hover:text-[#D4AF37] transition duration-300"><i
                        class="fab fa-facebook-f"></i></a>
                <a href="#" class="hover:text-[#D4AF37] transition duration-300"><i class="fab fa-twitter"></i></a>
                <a href="#" class="hover:text-[#D4AF37] transition duration-300"><i
                        class="fab fa-instagram"></i></a>
                <a href="#" class="hover:text-[#D4AF37] transition duration-300"><i class="fab fa-youtube"></i></a>
            </div>
        </div>

        {{-- LIÊN HỆ --}}
        <div>
            <h6 class="text-white font-semibold mb-4">Liên hệ với chúng tôi</h6>
            <ul class="space-y-2 text-sm">
                <li class="hover:text-[#D4AF37] transition duration-300 cursor-pointer"><i class="fa fa-phone mr-2"></i> (+84) 987 654 321</li>
                <li class="hover:text-[#D4AF37] transition duration-300 cursor-pointer"><i class="fa fa-envelope mr-2"></i> support@oziahotel.vn</li>
                <li class="hover:text-[#D4AF37] transition duration-300 cursor-pointer"><i class="fa fa-map-marker mr-2"></i> FPT Complex, đường Trịnh Văn Bô, Nam Từ Liêm, Hà Nội</li>
            </ul>
        </div>

        {{-- ĐĂNG KÝ NHẬN TIN --}}
        <div>
            <h6 class="text-white font-semibold mb-4">Đăng ký nhận tin</h6>
            <p class="text-sm mb-4">Nhận ngay các ưu đãi và khuyến mãi khách sạn mới nhất từ Ozia Hotel.</p>
            
            @if ($errors->has('email'))
                <div class="mb-4 p-3 bg-red-500 text-white rounded-lg text-sm">
                    {{ $errors->first('email') }}
                </div>
            @endif
            <form action="{{ route('client.newsletter.subscribe') }}" method="POST" class="flex bg-gray-800 rounded-lg overflow-hidden">
                @csrf
                <input type="email" name="email" placeholder="Nhập email của bạn..." required
                    value="{{ old('email') }}"
                    class="w-full px-4 py-2 text-gray-100 bg-transparent focus:outline-none">
                <button type="submit" class="bg-[#D4AF37] hover:bg-[#b68b00] px-4 py-2 text-white transition duration-300">
                    Gửi
                </button>
            </form>
        </div>
    </div>

    {{-- BẢN QUYỀN --}}
    <div class="border-t border-gray-700 mt-10 pt-6">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center text-sm">
            <ul class="flex flex-wrap justify-center md:justify-start space-x-4 mb-4 md:mb-0">
                <li><a href="#" class="hover:text-[#D4AF37]">Liên hệ</a></li>
                <li><a href="#" class="hover:text-[#D4AF37]">Điều khoản sử dụng</a></li>
                <li><a href="#" class="hover:text-[#D4AF37]">Chính sách bảo mật</a></li>
                <li><a href="{{ route('client.gioithieu') }}" class="hover:text-[#D4AF37]">Giới thiệu</a></li>
            </ul>
            <p class="text-gray-400 text-center md:text-right">
                © {{ date('Y') }} <span class="text-white font-semibold">Ozia Hotel</span> —
                Được phát triển ở
                FPT Trịnh Văn Bô, Hà Nội.
            </p>
        </div>
    </div>
</footer>