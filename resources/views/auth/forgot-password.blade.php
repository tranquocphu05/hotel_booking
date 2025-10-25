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
  <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8" 
       style="background: linear-gradient(135deg, #f8f6f1 0%, #f2e7c9 50%, #e8d9a7 100%);">
    <div class="max-w-md w-full">
      <!-- Card -->
      <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-2xl overflow-hidden border border-[#e0d5b3]">
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-[#D4AF37] to-[#b8860b] px-8 py-10 text-center">
          <div class="w-20 h-20 bg-white rounded-full mx-auto mb-4 flex items-center justify-center shadow-lg">
            <i class="fas fa-key text-[#D4AF37] text-3xl"></i>
          </div>
          <h2 class="text-3xl font-bold text-white mb-2">Quên Mật Khẩu?</h2>
          <p class="text-yellow-100 text-sm">Đừng lo, chúng tôi sẽ giúp bạn lấy lại tài khoản.</p>
        </div>

        <!-- Content Section -->
        <div class="px-8 py-8">
          <div class="bg-yellow-50 border-l-4 border-[#D4AF37] p-4 mb-6 rounded">
            <div class="flex items-start">
              <i class="fas fa-info-circle text-[#D4AF37] mt-0.5 mr-3"></i>
              <p class="text-sm text-gray-700">
                Nhập địa chỉ email của bạn và chúng tôi sẽ gửi link đặt lại mật khẩu cho bạn.
              </p>
            </div>
          </div>

          <!-- Form -->
          <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
            @csrf

            <!-- Email -->
            <div>
              <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-envelope mr-2 text-[#D4AF37]"></i>Địa chỉ Email
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                  <i class="fas fa-at text-gray-400"></i>
                </div>
                <input 
                  id="email" type="email" name="email" 
                  placeholder="example@email.com"
                  required autofocus
                  class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#D4AF37] focus:ring-4 focus:ring-yellow-100 transition-all duration-200">
              </div>
            </div>

            <!-- Submit -->
            <button 
              type="submit"
              class="btn-gold-hover w-full text-white font-semibold py-3 rounded-xl shadow-md transform transition-all duration-300 hover:scale-[1.03] hover:shadow-lg flex items-center justify-center text-sm tracking-wide">
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

          <!-- Back to login -->
          <div class="text-center space-y-3">
            <a href="{{ route('login') }}" class="inline-flex items-center text-sm text-[#D4AF37] hover:text-yellow-600 font-medium transition-colors">
              <i class="fas fa-arrow-left mr-2"></i>
              Quay lại đăng nhập
            </a>
            <p class="text-xs text-gray-500">
              Chưa có tài khoản? 
              <a href="{{ route('register') }}" class="text-[#D4AF37] hover:text-yellow-600 font-semibold">Đăng ký ngay</a>
            </p>
          </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-8 py-4 text-center border-t border-gray-100">
          <p class="text-xs text-gray-500">
            <i class="fas fa-shield-alt mr-1"></i>Thông tin của bạn được bảo mật tuyệt đối
          </p>
        </div>
      </div>

      <div class="mt-6 text-center">
        <p class="text-sm text-gray-600">
          <i class="fas fa-question-circle mr-1"></i>
          Cần hỗ trợ? 
          <a href="mailto:support@hotel.com" class="font-semibold hover:underline text-[#b8860b]">
            Liên hệ chúng tôi
          </a>
        </p>
      </div>
    </div>
  </div>

  <style>
    /* Hiệu ứng vàng kim loại */
    .btn-gold-hover {
      background: linear-gradient(135deg, #c5a029, #f9e48f, #d4af37);
      background-size: 250% 250%;
      color: #fff;
      border: none;
      transition: all 0.4s ease;
      box-shadow: 0 4px 10px rgba(212, 175, 55, 0.3);
    }

    .btn-gold-hover:hover {
      background-position: right center;
      filter: brightness(1.1);
      box-shadow: 0 6px 18px rgba(212, 175, 55, 0.5);
    }

    .btn-gold-hover:active {
      transform: scale(0.97);
      box-shadow: 0 2px 6px rgba(212, 175, 55, 0.4);
    }
  </style>
</body>
</html>
