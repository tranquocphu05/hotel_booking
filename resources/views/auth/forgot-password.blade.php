<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="max-w-md w-full">
            <!-- Card -->
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                <!-- Header Section -->
                <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-8 py-10 text-center">
                    <div class="w-20 h-20 bg-white rounded-full mx-auto mb-4 flex items-center justify-center shadow-lg">
                        <i class="fas fa-key text-purple-600 text-3xl"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-white mb-2">Quên Mật Khẩu?</h2>
                    <p class="text-purple-100 text-sm">Đừng lo lắng, chúng tôi sẽ giúp bạn lấy lại</p>
                </div>

                <!-- Content Section -->
                <div class="px-8 py-8">
                    <!-- Info Message -->
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
                            <p class="text-sm text-blue-700">
                                Nhập địa chỉ email của bạn và chúng tôi sẽ gửi link đặt lại mật khẩu cho bạn.
                            </p>
                        </div>
                    </div>

                    <!-- Session Status -->
                    @if (session('status'))
                        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded animate-fade-in">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-500 mr-3 text-xl"></i>
                                <p class="text-sm text-green-700 font-medium">{{ session('status') }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Errors -->
                    @if ($errors->any())
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                            <div class="flex items-start">
                                <i class="fas fa-exclamation-circle text-red-500 mt-0.5 mr-3"></i>
                                <div class="text-sm text-red-700">
                                    @foreach ($errors->all() as $error)
                                        <p>{{ $error }}</p>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Form -->
                    <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                        @csrf

                        <!-- Email Input -->
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-envelope mr-2 text-purple-600"></i>
                                Địa chỉ Email
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-at text-gray-400"></i>
                                </div>
                                <input 
                                    id="email" 
                                    type="email" 
                                    name="email" 
                                    value="{{ old('email') }}"
                                    required 
                                    autofocus
                                    placeholder="example@email.com"
                                    class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition-all duration-200 @error('email') border-red-300 @enderror">
                            </div>
                            @error('email')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <button 
                            type="submit"
                            class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold py-4 rounded-xl shadow-lg transform transition-all duration-200 hover:scale-[1.02] hover:shadow-xl flex items-center justify-center">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Gửi Link Đặt Lại Mật Khẩu
                        </button>
                    </form>

                    <!-- Divider -->
                    <div class="relative my-8">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-200"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-4 bg-white text-gray-500">hoặc</span>
                        </div>
                    </div>

                    <!-- Back to Login -->
                    <div class="text-center space-y-3">
                        <a href="{{ route('login') }}" class="inline-flex items-center text-sm text-purple-600 hover:text-purple-700 font-medium transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Quay lại đăng nhập
                        </a>
                        
                        <p class="text-xs text-gray-500">
                            Chưa có tài khoản? 
                            <a href="{{ route('register') }}" class="text-purple-600 hover:text-purple-700 font-semibold">
                                Đăng ký ngay
                            </a>
                        </p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 px-8 py-4 text-center border-t border-gray-100">
                    <p class="text-xs text-gray-500">
                        <i class="fas fa-shield-alt mr-1"></i>
                        Thông tin của bạn được bảo mật tuyệt đối
                    </p>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="mt-6 text-center">
                <p class="text-sm text-white">
                    <i class="fas fa-question-circle mr-1"></i>
                    Cần hỗ trợ? 
                    <a href="mailto:support@hotel.com" class="font-semibold hover:underline">
                        Liên hệ chúng tôi
                    </a>
                </p>
            </div>
        </div>
    </div>

    <style>
    @keyframes fade-in {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in {
        animation: fade-in 0.5s ease-out;
    }
    </style>
</body>
</html>