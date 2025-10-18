{{-- File: resources\views\client\header\header.blade.php --}}

{{-- TĂNG CHIỀU CAO: h-[90vh] để ảnh to hơn --}}
<div x-data="{ currentSlide: 1, totalSlides: 3 }" 
     x-init="setInterval(() => { currentSlide = (currentSlide % totalSlides) + 1 }, 5000)" 
     class="relative h-[90vh] min-h-[700px] overflow-hidden">

    {{-- Slider Images Container --}}
    <div class="absolute inset-0">
        @php
            // Đường dẫn ảnh trong public/img/hero/
            $slides = [
                'img/hero/hero-1.jpg', 
                'img/hero/hero-2.jpg',
                'img/hero/hero-3.jpg',
            ];
        @endphp

        @foreach($slides as $index => $image)
            {{-- X-show và X-transition quản lý việc hiển thị/ẩn mượt mà của slide --}}
            <div x-show="currentSlide === {{ $index + 1 }}" 
                 x-transition:enter="transition ease-out duration-1000"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-1000"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-cover bg-center h-full w-full" 
                 style="background-image: url('{{ asset($image) }}');">
            </div>
        @endforeach
    </div>

    {{-- Dark Overlay và Nội dung chính --}}
    <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center p-4 sm:px-8 lg:px-16">
        
        {{-- Wrapper cho cả chữ và form: Thiết lập layout chính --}}
        {{-- THAY ĐỔI: Dùng khoảng cách giữa (gap) thay vì justify-between để kiểm soát tốt hơn --}}
        <div class="w-full max-w-screen-xl mx-auto flex flex-col lg:flex-row items-center lg:items-center lg:justify-start lg:gap-8">
            
            {{-- Text Content (Left Side) --}}
            <div class="text-white z-10 text-center lg:text-left max-w-lg lg:w-5/12">
                <p class="text-sm uppercase tracking-widest mb-2 font-light">WELCOME TO SONA</p>
                <h1 class="text-5xl md:text-6xl font-serif font-bold mb-4 leading-tight">Sona A Luxury Hotel</h1>
                <p class="text-lg mb-8 mx-auto lg:mx-0 font-light">
                    Here are the best hotel booking sites, including recommendations for international travel and for finding low-priced hotel rooms.
                </p>
                <a href="#" class="inline-block px-8 py-3 border border-white text-white font-semibold uppercase tracking-wider transition duration-300 hover:bg-white hover:text-black">
                    DISCOVER NOW
                </a>
            </div>

            {{-- Compact Booking Form (Right Side) --}}
            {{-- THAY ĐỔI: Giữ max-w-sm (384px) trên mobile, và đặt lg:w-1/3 để nó chiếm đúng 1/3 không gian còn lại (33.33%) --}}
            <div class="w-full max-w-sm lg:w-1/3 p-6 md:p-8 bg-white shadow-2xl z-20 mt-8 lg:mt-0 rounded-lg lg:ml-auto">
                <h3 class="text-2xl font-serif font-bold text-gray-800 mb-6 text-center">Booking Your Hotel</h3>
                <form action="#" method="POST">
                    @csrf
                    
                    {{-- Check In & Check Out --}}
                    <div class="space-y-4 mb-6">
                        <div>
                            <label for="check_in" class="text-sm text-gray-600 block mb-1">Check In</label>
                            <div class="relative">
                                <input type="text" id="check_in" name="check_in" placeholder="Select Date"
                                    class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-red-500"
                                    onfocus="(this.type='date')" onblur="(this.type='text')" required>
                                <i class="fas fa-calendar-alt absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                            </div>
                        </div>

                        <div>
                            <label for="check_out" class="text-sm text-gray-600 block mb-1">Check Out</label>
                            <div class="relative">
                                <input type="text" id="check_out" name="check_out" placeholder="Select Date"
                                    class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-red-500"
                                    onfocus="(this.type='date')" onblur="(this.type='text')" required>
                                <i class="fas fa-calendar-alt absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Guests & Room --}}
                    <div class="space-y-4 mb-6">
                        <div>
                            <label for="guests" class="text-sm text-gray-600 block mb-1">Guests</label>
                            <div class="relative">
                                <select id="guests" name="guests"
                                        class="w-full p-3 border border-gray-300 rounded appearance-none bg-white pr-10 focus:outline-none focus:ring-2 focus:ring-red-500">
                                    <option value="2 Adults">2 Adults</option>
                                    <option value="1 Adult">1 Adult</option>
                                    <option value="3 Adults">3 Adults</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                            </div>
                        </div>

                        <div>
                            <label for="room" class="text-sm text-gray-600 block mb-1">Room</label>
                            <div class="relative">
                                <select id="room" name="room"
                                        class="w-full p-3 border border-gray-300 rounded appearance-none bg-white pr-10 focus:outline-none focus:ring-2 focus:ring-red-500">
                                    <option value="1 Room">1 Room</option>
                                    <option value="2 Rooms">2 Rooms</option>
                                    <option value="3 Rooms">3 Rooms</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Check Availability Button --}}
                    <button type="submit"
                            class="w-full py-3 bg-red-600 text-white font-semibold uppercase tracking-wider rounded transition duration-300 hover:bg-red-700">
                        CHECK AVAILABILITY
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    {{-- Slider Indicators (Chấm tròn điều hướng) --}}
    {{-- THAY ĐỔI LỚN: Căn giữa tuyệt đối cho tất cả kích thước màn hình --}}
    <div class="absolute bottom-10 left-1/2 transform -translate-x-1/2 flex space-x-2 z-20">
        <template x-for="i in totalSlides" :key="i">
            <button @click="currentSlide = i" 
                    :class="{'bg-white w-8': currentSlide === i, 'bg-gray-400 w-3': currentSlide !== i}"
                    class="h-3 rounded-full transition-all duration-300"></button>
        </template>
    </div>
</div>