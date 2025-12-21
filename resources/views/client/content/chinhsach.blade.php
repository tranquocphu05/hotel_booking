@extends('layouts.client')

@section('title', 'Chính sách Khách sạn')

@section('client_content')
    {{-- Hero Section --}}
    <div class="relative w-full bg-cover bg-center bg-no-repeat -mt-2"
        style="background-image: url('{{ asset('img/blog/bg-6.jpg') }}');">

        {{-- Lớp phủ tối giúp chữ nổi bật --}}
        <div class="absolute inset-0 bg-black bg-opacity-40"></div>

        <div class="relative py-28 px-4 text-center text-white">
            <nav class="text-sm text-gray-200 mb-4">
                <a href="{{ url('/') }}" class="hover:text-[#D4AF37] transition-colors">Trang chủ</a> /
                <span class="text-[#FFD700] font-semibold">Chính sách</span>
            </nav>

            <h1 class="text-5xl md:text-7xl font-bold mb-8">Chính sách Khách sạn</h1>

            <p class="text-lg md:text-xl text-gray-100 leading-relaxed max-w-4xl mx-auto">
                Tìm hiểu về các chính sách và quy định của Ozia Hotel để có trải nghiệm nghỉ dưỡng tốt nhất
            </p>
        </div>
    </div>

    {{-- Main Content --}}
    <section class="py-20 bg-gradient-to-b from-gray-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Chính sách Đặt phòng --}}
            <div class="mb-20">
                <div class="flex flex-col md:flex-row items-center md:items-start gap-8 mb-8">
                    <div class="w-full md:w-1/2 order-2 md:order-1">
                        <div class="flex items-center mb-6">
                            <div class="w-20 h-20 bg-gradient-to-br from-red-400 to-red-600 rounded-2xl flex items-center justify-center mr-4 shadow-lg transform hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-calendar-check text-white text-3xl"></i>
                            </div>
                            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 bg-gradient-to-r from-red-600 to-red-800 bg-clip-text text-transparent">Chính sách Đặt phòng</h2>
                        </div>
                        <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xl border border-gray-100 hover:shadow-2xl transition-shadow duration-300">
                    <div class="space-y-4 text-gray-700 leading-relaxed">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2 flex items-center">
                                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                Yêu cầu đặt phòng
                            </h3>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li>Khách hàng phải từ 18 tuổi trở lên để đặt phòng</li>
                                <li>Cần cung cấp đầy đủ thông tin cá nhân chính xác khi đặt phòng</li>
                                <li>Thanh toán đặt cọc 30% giá trị phòng để xác nhận đặt phòng</li>
                                <li>Xác nhận đặt phòng sẽ được gửi qua email sau khi thanh toán thành công</li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2 flex items-center">
                                <i class="fas fa-clock text-blue-500 mr-2"></i>
                                Thời gian đặt phòng
                            </h3>
                            <p>Khách hàng có thể đặt phòng trực tuyến 24/7 hoặc liên hệ trực tiếp với khách sạn từ 7:00 - 22:00 hàng ngày.</p>
                        </div>
                    </div>
                </div>
                    </div>
                    <div class="w-full md:w-1/2 order-1 md:order-2">
                        <div class="relative group overflow-hidden rounded-2xl shadow-2xl">
                            <img src="{{ asset('img/room/room-1.jpg') }}" 
                                 alt="Đặt phòng khách sạn" 
                                 class="w-full h-80 object-cover transform group-hover:scale-110 transition-transform duration-700">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                                <h3 class="text-2xl font-bold mb-2">Đặt phòng dễ dàng</h3>
                                <p class="text-sm opacity-90">Trải nghiệm đặt phòng nhanh chóng và tiện lợi</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chính sách Hủy phòng --}}
            <div class="mb-20">
                <div class="flex flex-col md:flex-row-reverse items-center md:items-start gap-8 mb-8">
                    <div class="w-full md:w-1/2">
                        <div class="flex items-center mb-6">
                            <div class="w-20 h-20 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-2xl flex items-center justify-center mr-4 shadow-lg transform hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-times-circle text-white text-3xl"></i>
                            </div>
                            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 bg-gradient-to-r from-yellow-600 to-orange-600 bg-clip-text text-transparent">Chính sách Hủy phòng</h2>
                        </div>
                        <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xl border border-gray-100 hover:shadow-2xl transition-shadow duration-300">
                    <div class="space-y-4 text-gray-700 leading-relaxed">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2 flex items-center">
                                <i class="fas fa-calendar-times text-red-500 mr-2"></i>
                                Hủy phòng miễn phí
                            </h3>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li>Hủy trước 14 ngày: Hoàn tiền 100%</li>
                                <li>Hủy trước 7 ngày: Hoàn tiền 70%</li>
                                <li>Hủy trước 3 ngày: Hoàn tiền 50%</li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2 flex items-center">
                                <i class="fas fa-exclamation-triangle text-orange-500 mr-2"></i>
                                Hủy phòng có phí
                            </h3>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li>Hủy trong vòng 48 giờ trước ngày check-in: Phí hủy 30% giá trị phòng</li>
                                <li>Hủy trong vòng 24 giờ hoặc không đến (No-show): Không hoàn tiền</li>
                                <li>Thời gian hủy được tính theo múi giờ địa phương của khách sạn</li>
                            </ul>
                        </div>
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                            <p class="text-blue-800"><strong>Lưu ý:</strong> Đối với các đặt phòng trong mùa cao điểm hoặc sự kiện đặc biệt, chính sách hủy phòng có thể khác. Vui lòng kiểm tra khi đặt phòng.</p>
                        </div>
                    </div>
                </div>
                    </div>
                    <div class="w-full md:w-1/2">
                        <div class="relative group overflow-hidden rounded-2xl shadow-2xl">
                            <img src="{{ asset('img/room/room-2.jpg') }}" 
                                 alt="Hủy phòng khách sạn" 
                                 class="w-full h-80 object-cover transform group-hover:scale-110 transition-transform duration-700">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                                <h3 class="text-2xl font-bold mb-2">Linh hoạt hủy phòng</h3>
                                <p class="text-sm opacity-90">Chính sách hủy phòng công bằng và minh bạch</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chính sách Thanh toán --}}
            <div class="mb-20">
                <div class="flex flex-col md:flex-row items-center md:items-start gap-8 mb-8">
                    <div class="w-full md:w-1/2 order-2 md:order-1">
                        <div class="flex items-center mb-6">
                            <div class="w-20 h-20 bg-gradient-to-br from-green-400 to-emerald-600 rounded-2xl flex items-center justify-center mr-4 shadow-lg transform hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-credit-card text-white text-3xl"></i>
                            </div>
                            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 bg-gradient-to-r from-green-600 to-emerald-700 bg-clip-text text-transparent">Chính sách Thanh toán</h2>
                        </div>
                        <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xl border border-gray-100 hover:shadow-2xl transition-shadow duration-300">
                    <div class="space-y-4 text-gray-700 leading-relaxed">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2 flex items-center">
                                <i class="fas fa-money-bill-wave text-green-500 mr-2"></i>
                                Phương thức thanh toán
                            </h3>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li>Thanh toán trực tuyến: Thẻ tín dụng/ghi nợ (Visa, Mastercard, JCB)</li>
                                <li>Ví điện tử: MoMo, ZaloPay, VNPay</li>
                                <li>Chuyển khoản ngân hàng</li>
                                <li>Thanh toán tại khách sạn: Tiền mặt, thẻ tín dụng</li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2 flex items-center">
                                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                Quy định thanh toán
                            </h3>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li>Đặt cọc 30% khi đặt phòng, thanh toán phần còn lại khi check-in</li>
                                <li>Đối với đặt phòng trong vòng 48 giờ: Thanh toán toàn bộ khi đặt phòng</li>
                                <li>Khách sạn có quyền yêu cầu đặt cọc bổ sung cho các dịch vụ đặc biệt</li>
                                <li>Tất cả giao dịch được mã hóa và bảo mật theo tiêu chuẩn PCI DSS</li>
                            </ul>
                        </div>
                    </div>
                </div>
                    </div>
                    <div class="w-full md:w-1/2 order-1 md:order-2">
                        <div class="relative group overflow-hidden rounded-2xl shadow-2xl">
                            <img src="{{ asset('img/room/room-3.jpg') }}" 
                                 alt="Thanh toán khách sạn" 
                                 class="w-full h-80 object-cover transform group-hover:scale-110 transition-transform duration-700">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                                <h3 class="text-2xl font-bold mb-2">Thanh toán an toàn</h3>
                                <p class="text-sm opacity-90">Nhiều phương thức thanh toán tiện lợi và bảo mật</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chính sách Check-in/Check-out --}}
            <div class="mb-20">
                <div class="flex flex-col md:flex-row-reverse items-center md:items-start gap-8 mb-8">
                    <div class="w-full md:w-1/2">
                        <div class="flex items-center mb-6">
                            <div class="w-20 h-20 bg-gradient-to-br from-purple-400 to-indigo-600 rounded-2xl flex items-center justify-center mr-4 shadow-lg transform hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-door-open text-white text-3xl"></i>
                            </div>
                            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 bg-gradient-to-r from-purple-600 to-indigo-700 bg-clip-text text-transparent">Check-in & Check-out</h2>
                        </div>
                        <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xl border border-gray-100 hover:shadow-2xl transition-shadow duration-300">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-3 flex items-center">
                                <i class="fas fa-sign-in-alt text-green-500 mr-2"></i>
                                Check-in
                            </h3>
                            <ul class="space-y-2 text-gray-700">
                                <li class="flex items-start">
                                    <i class="fas fa-clock text-gray-400 mr-2 mt-1"></i>
                                    <span>Thời gian: Từ 14:00 hàng ngày</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-id-card text-gray-400 mr-2 mt-1"></i>
                                    <span>Yêu cầu: CMND/CCCD hoặc hộ chiếu hợp lệ</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-credit-card text-gray-400 mr-2 mt-1"></i>
                                    <span>Thanh toán: Hoàn tất phần còn lại của hóa đơn</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-early-bird text-gray-400 mr-2 mt-1"></i>
                                    <span>Check-in sớm: Có thể yêu cầu (phụ thu nếu có)</span>
                                </li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-3 flex items-center">
                                <i class="fas fa-sign-out-alt text-red-500 mr-2"></i>
                                Check-out
                            </h3>
                            <ul class="space-y-2 text-gray-700">
                                <li class="flex items-start">
                                    <i class="fas fa-clock text-gray-400 mr-2 mt-1"></i>
                                    <span>Thời gian: Trước 12:00 trưa</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-hourglass-half text-gray-400 mr-2 mt-1"></i>
                                    <span>Check-out muộn: Có thể yêu cầu (phụ thu 50% giá phòng/giờ)</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-box text-gray-400 mr-2 mt-1"></i>
                                    <span>Gửi hành lý: Dịch vụ miễn phí sau check-out</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-receipt text-gray-400 mr-2 mt-1"></i>
                                    <span>Hóa đơn: Được cung cấp khi check-out</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                    </div>
                    <div class="w-full md:w-1/2">
                        <div class="relative group overflow-hidden rounded-2xl shadow-2xl">
                            <img src="{{ asset('img/room/room-4.jpg') }}" 
                                 alt="Check-in khách sạn" 
                                 class="w-full h-80 object-cover transform group-hover:scale-110 transition-transform duration-700">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                                <h3 class="text-2xl font-bold mb-2">Quy trình đơn giản</h3>
                                <p class="text-sm opacity-90">Check-in và check-out nhanh chóng, thuận tiện</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chính sách Trẻ em --}}
            <div class="mb-20">
                <div class="flex flex-col md:flex-row items-center md:items-start gap-8 mb-8">
                    <div class="w-full md:w-1/2 order-2 md:order-1">
                        <div class="flex items-center mb-6">
                            <div class="w-20 h-20 bg-gradient-to-br from-pink-400 to-rose-600 rounded-2xl flex items-center justify-center mr-4 shadow-lg transform hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-baby text-white text-3xl"></i>
                            </div>
                            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 bg-gradient-to-r from-pink-600 to-rose-700 bg-clip-text text-transparent">Chính sách Trẻ em</h2>
                        </div>
                        <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xl border border-gray-100 hover:shadow-2xl transition-shadow duration-300">
                    <div class="space-y-4 text-gray-700 leading-relaxed">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2 flex items-center">
                                <i class="fas fa-child text-pink-500 mr-2"></i>
                                Quy định về trẻ em
                            </h3>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li><strong>Trẻ em dưới 6 tuổi:</strong> Miễn phí khi ngủ chung giường với bố mẹ (không tính vào số lượng khách)</li>
                                <li><strong>Trẻ em từ 6-12 tuổi:</strong> Phụ thu 30% giá phòng/đêm khi ngủ chung giường</li>
                                <li><strong>Trẻ em từ 12 tuổi trở lên:</strong> Tính như người lớn</li>
                                <li>Giường phụ cho trẻ em: Phụ thu 500.000 VNĐ/đêm (nếu có)</li>
                            </ul>
                        </div>
                        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                            <p class="text-yellow-800"><strong>Lưu ý:</strong> Trẻ em phải được giám sát bởi người lớn tại mọi thời điểm trong khách sạn. Khách sạn không chịu trách nhiệm về trẻ em không được giám sát.</p>
                        </div>
                    </div>
                </div>
                    </div>
                    <div class="w-full md:w-1/2 order-1 md:order-2">
                        <div class="relative group overflow-hidden rounded-2xl shadow-2xl">
                            <img src="{{ asset('img/room/room-5.jpg') }}" 
                                 alt="Trẻ em tại khách sạn" 
                                 class="w-full h-80 object-cover transform group-hover:scale-110 transition-transform duration-700">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                                <h3 class="text-2xl font-bold mb-2">Gia đình thân thiện</h3>
                                <p class="text-sm opacity-90">Chính sách ưu đãi cho trẻ em và gia đình</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chính sách Vật nuôi --}}
            <div class="mb-20">
                <div class="flex flex-col md:flex-row-reverse items-center md:items-start gap-8 mb-8">
                    <div class="w-full md:w-1/2">
                        <div class="flex items-center mb-6">
                            <div class="w-20 h-20 bg-gradient-to-br from-indigo-400 to-blue-600 rounded-2xl flex items-center justify-center mr-4 shadow-lg transform hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-paw text-white text-3xl"></i>
                            </div>
                            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 bg-gradient-to-r from-indigo-600 to-blue-700 bg-clip-text text-transparent">Chính sách Vật nuôi</h2>
                        </div>
                        <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xl border border-gray-100 hover:shadow-2xl transition-shadow duration-300">
                    <div class="space-y-4 text-gray-700 leading-relaxed">
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded mb-4">
                            <p class="text-red-800"><strong>Quan trọng:</strong> Khách sạn không cho phép mang vật nuôi vào phòng nghỉ, trừ các trường hợp đặc biệt được thỏa thuận trước với ban quản lý.</p>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2 flex items-center">
                                <i class="fas fa-info-circle text-indigo-500 mr-2"></i>
                                Trường hợp ngoại lệ
                            </h3>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li>Chó dẫn đường cho người khuyết tật: Được phép miễn phí</li>
                                <li>Các vật nuôi khác: Cần liên hệ trước để được xem xét</li>
                                <li>Phụ thu vật nuôi: 200.000 VNĐ/đêm (nếu được chấp nhận)</li>
                                <li>Vật nuôi phải được giữ trong lồng hoặc dây xích tại khu vực công cộng</li>
                            </ul>
                        </div>
                    </div>
                </div>
                    </div>
                    <div class="w-full md:w-1/2">
                        <div class="relative group overflow-hidden rounded-2xl shadow-2xl">
                            <img src="{{ asset('img/room/room-6.jpg') }}" 
                                 alt="Vật nuôi tại khách sạn" 
                                 class="w-full h-80 object-cover transform group-hover:scale-110 transition-transform duration-700">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                                <h3 class="text-2xl font-bold mb-2">Quy định vật nuôi</h3>
                                <p class="text-sm opacity-90">Thông tin về chính sách vật nuôi tại khách sạn</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chính sách Hút thuốc --}}
            <div class="mb-20">
                <div class="flex flex-col md:flex-row items-center md:items-start gap-8 mb-8">
                    <div class="w-full md:w-1/2 order-2 md:order-1">
                        <div class="flex items-center mb-6">
                            <div class="w-20 h-20 bg-gradient-to-br from-gray-400 to-gray-600 rounded-2xl flex items-center justify-center mr-4 shadow-lg transform hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-smoking-ban text-white text-3xl"></i>
                            </div>
                            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 bg-gradient-to-r from-gray-600 to-gray-800 bg-clip-text text-transparent">Chính sách Hút thuốc</h2>
                        </div>
                        <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xl border border-gray-100 hover:shadow-2xl transition-shadow duration-300">
                    <div class="space-y-4 text-gray-700 leading-relaxed">
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded mb-4">
                            <p class="text-red-800"><strong>Khách sạn không hút thuốc:</strong> Tất cả các phòng nghỉ và khu vực trong nhà đều cấm hút thuốc hoàn toàn.</p>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2 flex items-center">
                                <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                                Phạt vi phạm
                            </h3>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li>Phí làm sạch: 1.000.000 VNĐ nếu hút thuốc trong phòng</li>
                                <li>Khách sạn có quyền yêu cầu khách rời khỏi khách sạn nếu vi phạm nghiêm trọng</li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2 flex items-center">
                                <i class="fas fa-map-marker-alt text-green-500 mr-2"></i>
                                Khu vực được phép
                            </h3>
                            <p>Khách hàng có thể hút thuốc tại các khu vực ngoài trời được chỉ định, cách xa lối vào chính ít nhất 10 mét.</p>
                        </div>
                    </div>
                </div>
                    </div>
                    <div class="w-full md:w-1/2 order-1 md:order-2">
                        <div class="relative group overflow-hidden rounded-2xl shadow-2xl">
                            <img src="{{ asset('img/blog/bg-6.jpg') }}" 
                                 alt="Không hút thuốc" 
                                 class="w-full h-80 object-cover transform group-hover:scale-110 transition-transform duration-700">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                                <h3 class="text-2xl font-bold mb-2">Môi trường trong lành</h3>
                                <p class="text-sm opacity-90">Khách sạn không hút thuốc vì sức khỏe của bạn</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chính sách Bảo mật & An toàn --}}
            <div class="mb-20">
                <div class="flex flex-col md:flex-row-reverse items-center md:items-start gap-8 mb-8">
                    <div class="w-full md:w-1/2">
                        <div class="flex items-center mb-6">
                            <div class="w-20 h-20 bg-gradient-to-br from-blue-400 to-cyan-600 rounded-2xl flex items-center justify-center mr-4 shadow-lg transform hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-shield-alt text-white text-3xl"></i>
                            </div>
                            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 bg-gradient-to-r from-blue-600 to-cyan-700 bg-clip-text text-transparent">Bảo mật & An toàn</h2>
                        </div>
                        <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xl border border-gray-100 hover:shadow-2xl transition-shadow duration-300">
                    <div class="space-y-4 text-gray-700 leading-relaxed">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2 flex items-center">
                                <i class="fas fa-lock text-blue-500 mr-2"></i>
                                Bảo mật thông tin
                            </h3>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li>Khách sạn cam kết bảo mật tuyệt đối thông tin cá nhân của khách hàng</li>
                                <li>Thông tin chỉ được sử dụng cho mục đích phục vụ và cải thiện dịch vụ</li>
                                <li>Không chia sẻ thông tin với bên thứ ba mà không có sự đồng ý</li>
                                <li>Tuân thủ Luật Bảo vệ Dữ liệu Cá nhân</li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2 flex items-center">
                                <i class="fas fa-user-shield text-green-500 mr-2"></i>
                                An toàn khách sạn
                            </h3>
                            <ul class="list-disc list-inside ml-4 space-y-2">
                                <li>Hệ thống camera an ninh 24/7 tại các khu vực công cộng</li>
                                <li>Nhân viên bảo vệ túc trực 24/7</li>
                                <li>Hệ thống khóa điện tử an toàn cho mỗi phòng</li>
                                <li>Tủ an toàn trong phòng để cất giữ đồ quý giá</li>
                            </ul>
                        </div>
                    </div>
                </div>
                    </div>
                    <div class="w-full md:w-1/2">
                        <div class="relative group overflow-hidden rounded-2xl shadow-2xl">
                            <img src="{{ asset('img/blog/17.jpg') }}" 
                                 alt="Bảo mật khách sạn" 
                                 class="w-full h-80 object-cover transform group-hover:scale-110 transition-transform duration-700">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                                <h3 class="text-2xl font-bold mb-2">An toàn tuyệt đối</h3>
                                <p class="text-sm opacity-90">Hệ thống bảo mật và an ninh 24/7</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chính sách Khác --}}
            <div class="mb-20">
                <div class="text-center mb-12">
                    <div class="inline-flex items-center justify-center mb-6">
                        <div class="w-20 h-20 bg-gradient-to-br from-teal-400 to-cyan-600 rounded-2xl flex items-center justify-center shadow-lg transform hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-list-alt text-white text-3xl"></i>
                        </div>
                    </div>
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 bg-gradient-to-r from-teal-600 to-cyan-700 bg-clip-text text-transparent mb-4">Các Quy định Khác</h2>
                    <p class="text-gray-600 max-w-2xl mx-auto">Những quy định và dịch vụ bổ sung tại khách sạn</p>
                </div>
                <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xl border border-gray-100 hover:shadow-2xl transition-shadow duration-300">
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-3 flex items-center">
                                <i class="fas fa-wifi text-blue-500 mr-2"></i>
                                WiFi & Internet
                            </h3>
                            <p class="text-gray-700">WiFi miễn phí tốc độ cao được cung cấp tại tất cả các phòng và khu vực công cộng. Mật khẩu được cung cấp khi check-in.</p>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-3 flex items-center">
                                <i class="fas fa-parking text-gray-500 mr-2"></i>
                                Bãi đỗ xe
                            </h3>
                            <p class="text-gray-700">Bãi đỗ xe miễn phí cho khách lưu trú. Khách sạn không chịu trách nhiệm về tài sản trong xe.</p>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-3 flex items-center">
                                <i class="fas fa-utensils text-orange-500 mr-2"></i>
                                Dịch vụ ăn uống
                            </h3>
                            <p class="text-gray-700">Khách sạn cung cấp dịch vụ phục vụ phòng 24/7. Menu và giá cả có sẵn trong phòng.</p>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-3 flex items-center">
                                <i class="fas fa-gem text-purple-500 mr-2"></i>
                                Tài sản & Trách nhiệm
                            </h3>
                            <p class="text-gray-700">Khách sạn không chịu trách nhiệm về mất mát hoặc hư hỏng tài sản cá nhân. Khách hàng được khuyến khích sử dụng tủ an toàn trong phòng.</p>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-3 flex items-center">
                                <i class="fas fa-volume-up text-red-500 mr-2"></i>
                                Quy định về tiếng ồn
                            </h3>
                            <p class="text-gray-700">Vui lòng giữ yên lặng từ 22:00 đến 7:00 để đảm bảo không làm phiền các khách khác. Khách sạn có quyền yêu cầu khách vi phạm rời khỏi.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Liên hệ --}}
            <div class="relative overflow-hidden bg-gradient-to-r from-red-600 via-red-700 to-red-800 rounded-3xl p-8 md:p-12 text-white text-center shadow-2xl transform hover:scale-[1.02] transition-transform duration-300">
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute top-0 left-0 w-64 h-64 bg-white rounded-full -translate-x-1/2 -translate-y-1/2"></div>
                    <div class="absolute bottom-0 right-0 w-96 h-96 bg-white rounded-full translate-x-1/3 translate-y-1/3"></div>
                </div>
                <div class="relative z-10">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 rounded-full mb-6 backdrop-blur-sm">
                        <i class="fas fa-headset text-3xl"></i>
                    </div>
                    <h2 class="text-3xl md:text-4xl font-bold mb-4">Có câu hỏi về chính sách?</h2>
                    <p class="text-lg md:text-xl mb-8 opacity-95 max-w-2xl mx-auto">Đội ngũ nhân viên chuyên nghiệp của chúng tôi luôn sẵn sàng hỗ trợ bạn 24/7</p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="{{ route('client.lienhe') }}" 
                           class="inline-flex items-center justify-center px-8 py-4 bg-white text-red-600 rounded-xl font-semibold hover:bg-gray-100 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                            <i class="fas fa-envelope mr-2 text-lg"></i>
                            Liên hệ ngay
                        </a>
                        <a href="tel:+84123456789" 
                           class="inline-flex items-center justify-center px-8 py-4 bg-white/20 backdrop-blur-sm text-white rounded-xl font-semibold hover:bg-white/30 transition-all duration-300 border-2 border-white/50 hover:border-white shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                            <i class="fas fa-phone mr-2 text-lg"></i>
                            Gọi: 0123 456 789
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </section>

@push('styles')
<style>
    /* Custom animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .policy-section {
        animation: fadeInUp 0.6s ease-out;
    }

    /* Hover effects for cards */
    .policy-card {
        transition: all 0.3s ease;
    }

    .policy-card:hover {
        transform: translateY(-5px);
    }

    /* Gradient text effect */
    .gradient-text {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Icon animations */
    .policy-icon {
        transition: all 0.3s ease;
    }

    .policy-icon:hover {
        transform: rotate(5deg) scale(1.1);
    }

    /* List item styling */
    .policy-list li {
        position: relative;
        padding-left: 1.5rem;
        transition: all 0.2s ease;
    }

    .policy-list li:hover {
        padding-left: 2rem;
        color: #1f2937;
    }

    .policy-list li::before {
        content: "✓";
        position: absolute;
        left: 0;
        color: #10b981;
        font-weight: bold;
    }

    /* Image overlay effect */
    .image-overlay {
        position: relative;
        overflow: hidden;
    }

    .image-overlay::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(168, 85, 247, 0.1) 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .image-overlay:hover::after {
        opacity: 1;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .policy-section {
            margin-bottom: 3rem;
        }
    }

    /* Smooth scroll */
    html {
        scroll-behavior: smooth;
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 10px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    ::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 5px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    }
</style>
@endpush
@endsection
