{{-- resources/views/client/content/content.blade.php --}}
{{-- Gộp toàn bộ nội dung 5 file, mỗi phần bao riêng để tránh xung đột --}}

<section id="about-us">
{{-- About Us Section: Intercontinental LA Westlake Hotel --}}
<section class="container mx-auto px-4 py-16">
    <div class="flex flex-col lg:flex-row items-center justify-between">
        {{-- Text Content (Left Side) --}}
        <div class="lg:w-3/5 text-center lg:text-left mb-8 lg:mb-0">
            <p class="text-sm uppercase tracking-widest text-red-600 mb-2">About Us</p>
            <h2 class="text-3xl font-bold text-gray-800 mb-4">Intercontinental LA Westlake Hotel</h2>
            <p class="text-gray-600 mb-6 max-w-2xl mx-auto lg:mx-0">
                Sona.com is a leading online accommodation site. We're passionate about travel. Every day, we inspire and reach millions of travelers across the globe through our own sites.
            </p>
            <a href="#" class="inline-block text-red-600 font-semibold border-b border-red-600 pb-1 hover:text-red-700 transition duration-300">
                Discover Now <i class="fas fa-arrow-right ml-1 text-sm"></i>
            </a>
        </div>
        
        {{-- Images (Right Side) --}}
        <div class="lg:w-2/5 flex justify-center lg:justify-end space-x-4">
            {{-- Giả sử bạn có ảnh trong public/img/about/ --}}
            <img src="{{ asset('img/about/about-1.jpg') }}" alt="Hotel View 1" class="w-32 h-32 md:w-40 md:h-40 object-cover rounded shadow-xl transform hover:scale-105 transition duration-500">
            <img src="{{ asset('img/about/about-2.jpg') }}" alt="Hotel View 2" class="w-32 h-32 md:w-40 md:h-40 object-cover rounded shadow-xl transform hover:scale-105 transition duration-500">
        </div>
    </div>
</section>
</section>
<section id="services">
{{-- File: resources/views/client/content/services.blade.php --}}

{{-- Services Section: Discover Our Services --}}
<section class="text-center container mx-auto px-4 py-16 bg-white">
    <p class="text-sm uppercase tracking-widest text-red-600 mb-2">Our Services</p>
    <h2 class="text-3xl font-bold text-gray-800 mb-12">Discover Our Services</h2>
    
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-4 gap-8">
        @php
            $services = [
                ['icon' => 'fas fa-utensils', 'title' => 'Travel Plan', 'desc' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'],
                ['icon' => 'fas fa-mug-hot', 'title' => 'Catering Service', 'desc' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'],
                ['icon' => 'fas fa-wifi', 'title' => 'Booking Guides', 'desc' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'],
                ['icon' => 'fas fa-car', 'title' => 'Car Parking', 'desc' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'],
                ['icon' => 'fas fa-door-open', 'title' => 'Room Service', 'desc' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'],
                ['icon' => 'fas fa-spa', 'title' => 'Fitness & Spa', 'desc' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'],
                ['icon' => 'fas fa-wine-glass-alt', 'title' => 'Bar & Dining', 'desc' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'],
                ['icon' => 'fas fa-swimmer', 'title' => 'Swimming Pool', 'desc' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'],
            ];
        @endphp
        
        @foreach($services as $service)
            <div class="p-4 hover:shadow-lg transition duration-300 rounded-lg">
                <i class="{{ $service['icon'] }} text-4xl text-red-600 mb-4 transition duration-300 hover:text-red-700"></i>
                <h4 class="text-xl font-semibold mb-2 text-gray-800">{{ $service['title'] }}</h4>
                <p class="text-gray-500 text-sm">{{ $service['desc'] }}</p>
            </div>
        @endforeach
    </div>
</section>
</section>

<section id="rooms-gallery">
{{-- FILE: resources/views/client/content/rooms_gallery.blade.php --}}

<section class="flex flex-wrap">
    @php
        // Dữ liệu phòng (Tất cả đều được cấu hình để có hiệu ứng hover_detail)
        $rooms = [
            [
                'image' => 'img/room/room-1.jpg', 
                'name' => 'Double Room',             
                'price' => '199$',
                'details' => [
                    'Size' => '25 ft',
                    'Capacity' => 'Max persion 2',
                    'Bed' => 'Double Bed',
                    'Services' => 'Wifi, Television, Bathroom,..'
                ],
            ],
            [
                'image' => 'img/room/room-2.jpg', 
                'name' => 'Premium King Room',       
                'price' => '159$',
                'details' => [
                    'Size' => '30 ft',
                    'Capacity' => 'Max persion 5',
                    'Bed' => 'King Beds',
                    'Services' => 'Wifi, Television, Bathroom,..'
                ],
            ],
            [
                'image' => 'img/room/room-3.jpg', 
                'name' => 'Deluxe Room',            
                'price' => '198$',
                'details' => [
                    'Size' => '35 ft',
                    'Capacity' => 'Max persion 4',
                    'Bed' => 'Queen Beds',
                    'Services' => 'Wifi, Television, Bathroom,..'
                ],
            ],
            [
                'image' => 'img/room/room-4.jpg', 
                'name' => 'Family Room',            
                'price' => '299$',
                'details' => [
                    'Size' => '45 ft',
                    'Capacity' => 'Max persion 6',
                    'Bed' => 'Two Double Beds',
                    'Services' => 'Wifi, Television, Bathroom,..'
                ],
            ],
        ];
    @endphp

    @foreach($rooms as $room)
        {{-- Container cho mỗi phòng. w-1/4 để chia đều 4 cột. h-[600px] để tạo chiều cao lớn --}}
        <div class="w-full sm:w-1/2 md:w-1/4 h-[600px] relative group overflow-hidden">
            
            {{-- Ảnh nền (Group Hover Zoom) --}}
            <img src="{{ asset($room['image']) }}" alt="{{ $room['name'] }}" 
                 class="w-full h-full object-cover transition duration-500 group-hover:scale-105">
            
            {{-- Lớp phủ tối (Overlay): Tối hơn khi hover để thông tin nổi bật --}}
            <div class="absolute inset-0 bg-black bg-opacity-40 transition duration-300 group-hover:bg-opacity-80"></div>
            
            
            {{-- Vùng Nội dung chính (Luôn hiển thị Tên & Giá, ẨN đi khi HOVER) --}}
            <div class="absolute inset-0 p-6 text-white flex items-end transition duration-300 group-hover:opacity-0 group-hover:invisible">
                <div>
                    <h4 class="text-3xl font-serif font-bold mb-1">{{ $room['name'] }}</h4>
                    <span class="text-xl text-red-400 font-semibold">{{ $room['price'] }} <span class="text-white text-sm font-light">/ Night</span></span>
                </div>
            </div>

            
            {{-- Vùng Nội dung chi tiết (CHỈ hiện ra khi HOVER, căn giữa) --}}
            <div class="absolute inset-0 flex items-center justify-center p-6 transition duration-300 opacity-0 invisible group-hover:opacity-100 group-hover:visible">
                <div class="text-left text-white w-full max-w-xs"> 
                    <h4 class="text-3xl font-serif font-bold mb-1">{{ $room['name'] }}</h4>
                    <span class="text-xl text-red-400 font-semibold">{{ $room['price'] }} <span class="text-white text-sm font-light">/ Night</span></span>
                    
                    <div class="mt-4 grid grid-cols-2 gap-x-2 gap-y-1 text-sm">
                        @foreach($room['details'] as $key => $value)
                            {{-- Sử dụng 2 div để căn đều các nhãn và giá trị --}}
                            <div class="text-left">
                                <span class="font-bold text-red-300">{{ $key }}:</span>
                            </div>
                            <div class="text-left">
                                <span class="font-light">{{ $value }}</span>
                            </div>
                        @endforeach
                    </div>
                    
                    {{-- Nút MORE DETAILS --}}
                    <a href="#" class="mt-6 inline-block text-sm font-semibold uppercase tracking-wider border-b border-white pb-1 transition duration-300 hover:text-red-400 hover:border-red-400">
                        MORE DETAILS
                    </a>
                </div>
            </div>
        </div>
    @endforeach
</section>
</section>



<section id="testimonials">
{{-- Testimonials Section --}}
<section class="text-center container mx-auto px-4 py-16 bg-gray-50 my-16 rounded-lg">
    <p class="text-sm uppercase tracking-widest text-red-600 mb-2">Testimonials</p>
    <h2 class="text-3xl font-bold text-gray-800 mb-8">What Customers Say?</h2>
    
    {{-- Testimonial Content --}}
    <div class="max-w-3xl mx-auto px-4">
        <p class="text-gray-600 italic mb-6">
            "Allows a construction project manager to ledger the requested funds to the jobsite and monitor payment due to the wrong people. As a Designer/Builder, we love all the clean lines and crisp typography, and we are certainly satisfied with the invaluable tools this service has provided."
        </p>
        <div class="flex justify-center mb-4">
            <i class="fas fa-star text-yellow-500 mx-1"></i>
            <i class="fas fa-star text-yellow-500 mx-1"></i>
            <i class="fas fa-star text-yellow-500 mx-1"></i>
            <i class="fas fa-star text-yellow-500 mx-1"></i>
            <i class="fas fa-star text-yellow-500 mx-1"></i>
        </div>
        <p class="font-semibold text-gray-800">- Ronald F. Weger</p>
        {{-- Logo testimonial nếu có (img/testimonial-logo.png) --}}
        <img src="{{ asset('img/testimonial-logo.png') }}" alt="Client Logo" class="mt-4 mx-auto h-8 opacity-75">
    </div>
</section>
</section>

<section id="blog-events">
{{-- Blog & Event Section --}}
<section class="text-center container mx-auto px-4 py-16">
    <p class="text-sm uppercase tracking-widest text-red-600 mb-2">News & Events</p>
    <h2 class="text-3xl font-bold text-gray-800 mb-12">Our Blog & Event</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @php
            // Đường dẫn ảnh giả định từ thư mục img/blog/
            $posts = [
                ['image' => 'img/blog/blog-1.jpg', 'title' => 'Travel Guide: Tranquillum in Canada', 'date' => '2025-10-16', 'tag' => 'Travel Tips'],
                ['image' => 'img/blog/blog-2.jpg', 'title' => 'Choosing a Luxury Cruise Vacation', 'date' => '2025-10-16', 'tag' => 'Cruises'],
                ['image' => 'img/blog/blog-3.jpg', 'title' => 'Copper Canyon Adventure Trip', 'date' => '2025-10-16', 'tag' => 'Adventure'],
            ];
        @endphp
        
        @foreach($posts as $post)
            <div class="text-left rounded-lg overflow-hidden shadow-lg hover:shadow-xl transition duration-300">
                <img src="{{ asset($post['image']) }}" alt="{{ $post['title'] }}" class="w-full h-56 object-cover">
                <div class="p-6">
                    <span class="text-xs uppercase tracking-widest text-red-600 font-semibold block mb-2">{{ $post['tag'] }}</span>
                    <h4 class="text-xl font-bold text-gray-800 mb-3 hover:text-red-600 transition duration-300">
                        <a href="#">{{ $post['title'] }}</a>
                    </h4>
                    <p class="text-gray-500 text-sm"><i class="far fa-calendar-alt mr-1"></i> {{ $post['date'] }}</p>
                </div>
            </div>
        @endforeach
    </div>
</section>
</section>