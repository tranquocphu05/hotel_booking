<?php

namespace Database\Seeders;

use App\Models\News;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy admin đầu tiên
        $admin = Admin::where('vai_tro', 'admin')->first();
        
        if (!$admin) {
            // Tạo admin mặc định nếu chưa có
            $admin = Admin::create([
                'username' => 'admin',
                'email' => 'admin@hotel.com',
                'password' => bcrypt('password'),
                'ho_ten' => 'Quản trị viên',
                'vai_tro' => 'admin'
            ]);
        }

        $newsData = [
            [
                'tieu_de' => 'Khách sạn mở cửa dịch vụ spa mới',
                'tom_tat' => 'Khách sạn của chúng tôi vừa mở cửa dịch vụ spa cao cấp với nhiều liệu pháp thư giãn độc đáo.',
                'noi_dung' => 'Chúng tôi rất vui mừng thông báo về việc mở cửa dịch vụ spa mới tại khách sạn. Dịch vụ spa này được thiết kế với tiêu chuẩn quốc tế, mang đến cho khách hàng những trải nghiệm thư giãn tuyệt vời nhất.

Các dịch vụ chính bao gồm:
- Massage truyền thống và hiện đại
- Liệu pháp thảo dược
- Chăm sóc da mặt chuyên nghiệp
- Tắm hơi và xông hơi

Đội ngũ chuyên gia spa của chúng tôi đã được đào tạo bài bản và có nhiều năm kinh nghiệm trong lĩnh vực chăm sóc sức khỏe và thư giãn.',
                'trang_thai' => 'published',
                'nguoi_dung_id' => $admin->id
            ],
            [
                'tieu_de' => 'Chương trình ưu đãi đặc biệt mùa hè 2024',
                'tom_tat' => 'Khách sạn triển khai chương trình ưu đãi đặc biệt với nhiều gói dịch vụ hấp dẫn cho mùa hè này.',
                'noi_dung' => 'Mùa hè 2024, khách sạn của chúng tôi mang đến những ưu đãi đặc biệt dành cho tất cả khách hàng:

Gói ưu đãi "Summer Special":
- Giảm 30% cho đặt phòng từ 3 đêm trở lên
- Miễn phí bữa sáng buffet cho tất cả khách
- Tặng voucher spa trị giá 500.000 VNĐ
- Ưu đãi đặc biệt cho gia đình có trẻ em

Ngoài ra, chúng tôi còn có:
- Gói "Honeymoon Package" với giá đặc biệt cho cặp đôi
- Tour du lịch trong ngày miễn phí
- Dịch vụ đưa đón sân bay

Chương trình áp dụng từ ngày 1/6/2024 đến 31/8/2024. Đặt phòng ngay để không bỏ lỡ cơ hội tuyệt vời này!',
                'trang_thai' => 'published',
                'nguoi_dung_id' => $admin->id
            ],
            [
                'tieu_de' => 'Khách sạn đạt chứng nhận 5 sao quốc tế',
                'tom_tat' => 'Khách sạn của chúng tôi vinh dự nhận được chứng nhận 5 sao quốc tế từ Hiệp hội Du lịch Thế giới.',
                'noi_dung' => 'Chúng tôi rất tự hào thông báo rằng khách sạn đã chính thức được công nhận đạt tiêu chuẩn 5 sao quốc tế. Đây là thành quả của sự nỗ lực không ngừng trong việc nâng cao chất lượng dịch vụ.

Những tiêu chí đạt được:
- Chất lượng phòng ốc và tiện nghi cao cấp
- Dịch vụ khách hàng chuyên nghiệp 24/7
- Nhà hàng với menu đa dạng và chất lượng cao
- Hệ thống an ninh và an toàn tuyệt đối
- Môi trường xanh, sạch, thân thiện

Chứng nhận này khẳng định cam kết của chúng tôi trong việc mang đến trải nghiệm tuyệt vời nhất cho mọi khách hàng.',
                'trang_thai' => 'published',
                'nguoi_dung_id' => $admin->id
            ],
            [
                'tieu_de' => 'Hướng dẫn du lịch thành phố cho khách du lịch',
                'tom_tat' => 'Khám phá những địa điểm du lịch nổi tiếng và ẩm thực đặc sắc của thành phố.',
                'noi_dung' => 'Thành phố của chúng tôi có rất nhiều điểm đến hấp dẫn mà bạn không nên bỏ qua:

Địa điểm tham quan nổi tiếng:
- Chợ đêm với ẩm thực đường phố đa dạng
- Bảo tàng lịch sử và văn hóa
- Công viên và khu vui chơi giải trí
- Khu phố cổ với kiến trúc độc đáo

Ẩm thực đặc sắc:
- Phở bò truyền thống
- Bánh mì pate đặc biệt
- Chè và các món tráng miệng
- Cà phê phin đậm đà

Gợi ý lịch trình 3 ngày 2 đêm:
Ngày 1: Tham quan khu phố cổ và thưởng thức ẩm thực
Ngày 2: Khám phá bảo tàng và mua sắm
Ngày 3: Thư giãn tại công viên và chuẩn bị về

Chúng tôi sẵn sàng hỗ trợ bạn lập kế hoạch du lịch hoàn hảo!',
                'trang_thai' => 'published',
                'nguoi_dung_id' => $admin->id
            ],
            [
                'tieu_de' => 'Công nghệ mới trong dịch vụ khách sạn',
                'tom_tat' => 'Khách sạn áp dụng các công nghệ hiện đại để nâng cao trải nghiệm khách hàng.',
                'noi_dung' => 'Chúng tôi đã đầu tư mạnh mẽ vào công nghệ để mang đến trải nghiệm tốt nhất cho khách hàng:

Hệ thống check-in/check-out tự động:
- Khách có thể check-in online trước khi đến
- Sử dụng thẻ từ thông minh để mở cửa phòng
- Thanh toán không tiếp xúc an toàn

Ứng dụng di động khách sạn:
- Đặt dịch vụ phòng 24/7
- Đặt bàn nhà hàng và spa
- Thông tin về các sự kiện và hoạt động
- Hỗ trợ khách hàng trực tiếp

Hệ thống quản lý năng lượng thông minh:
- Điều hòa tự động điều chỉnh theo thói quen khách
- Hệ thống chiếu sáng thông minh
- Tiết kiệm năng lượng và thân thiện môi trường

Những công nghệ này giúp chúng tôi cung cấp dịch vụ nhanh chóng, tiện lợi và an toàn hơn.',
                'trang_thai' => 'published',
                'nguoi_dung_id' => $admin->id
            ],
            [
                'tieu_de' => 'Nhà hàng mới với menu đặc sản địa phương',
                'tom_tat' => 'Khách sạn khai trương nhà hàng mới với thực đơn đặc sản địa phương và món ăn quốc tế.',
                'noi_dung' => 'Nhà hàng mới của chúng tôi mang đến những món ăn đặc sản địa phương và món ăn quốc tế.',
                'trang_thai' => 'published',
                'nguoi_dung_id' => $admin->id
            ],
            [
                'tieu_de' => 'Hệ thống phòng hội nghị hiện đại',
                'tom_tat' => 'Khách sạn đầu tư hệ thống phòng hội nghị với công nghệ hiện đại nhất.',
                'noi_dung' => 'Hệ thống phòng hội nghị mới được trang bị công nghệ hiện đại nhất.',
                'trang_thai' => 'published',
                'nguoi_dung_id' => $admin->id
            ],
            [
                'tieu_de' => 'Dịch vụ đưa đón sân bay miễn phí',
                'tom_tat' => 'Khách sạn cung cấp dịch vụ đưa đón sân bay miễn phí cho tất cả khách hàng.',
                'noi_dung' => 'Dịch vụ đưa đón sân bay miễn phí giúp khách hàng tiện lợi hơn.',
                'trang_thai' => 'published',
                'nguoi_dung_id' => $admin->id
            ],
            [
                'tieu_de' => 'Chương trình loyalty cho khách hàng thân thiết',
                'tom_tat' => 'Khách sạn ra mắt chương trình loyalty với nhiều ưu đãi hấp dẫn.',
                'noi_dung' => 'Chương trình loyalty mới mang đến nhiều ưu đãi cho khách hàng thân thiết.',
                'trang_thai' => 'published',
                'nguoi_dung_id' => $admin->id
            ],
            [
                'tieu_de' => 'Hồ bơi ngoài trời với view biển tuyệt đẹp',
                'tom_tat' => 'Khách sạn có hồ bơi ngoài trời với view biển tuyệt đẹp cho khách nghỉ dưỡng.',
                'noi_dung' => 'Hồ bơi ngoài trời với view biển tuyệt đẹp là điểm nhấn của khách sạn.',
                'trang_thai' => 'published',
                'nguoi_dung_id' => $admin->id
            ],
            [
                'tieu_de' => 'Dịch vụ concierge 24/7',
                'tom_tat' => 'Khách sạn cung cấp dịch vụ concierge 24/7 để hỗ trợ khách hàng.',
                'noi_dung' => 'Dịch vụ concierge 24/7 giúp khách hàng có trải nghiệm tốt nhất.',
                'trang_thai' => 'published',
                'nguoi_dung_id' => $admin->id
            ],
            [
                'tieu_de' => 'Phòng gym hiện đại với thiết bị mới nhất',
                'tom_tat' => 'Khách sạn có phòng gym hiện đại với thiết bị tập luyện mới nhất.',
                'noi_dung' => 'Phòng gym hiện đại với thiết bị mới nhất giúp khách duy trì sức khỏe.',
                'trang_thai' => 'published',
                'nguoi_dung_id' => $admin->id
            ],
            [
                'tieu_de' => 'Sự kiện đặc biệt cuối tuần',
                'tom_tat' => 'Khách sạn tổ chức các sự kiện đặc biệt vào cuối tuần cho khách hàng.',
                'noi_dung' => 'Các sự kiện đặc biệt cuối tuần mang đến trải nghiệm thú vị cho khách hàng.',
                'trang_thai' => 'published',
                'nguoi_dung_id' => $admin->id
            ],
            [
                'tieu_de' => 'Dịch vụ phòng 24/7',
                'tom_tat' => 'Khách sạn cung cấp dịch vụ phòng 24/7 với thực đơn đa dạng.',
                'noi_dung' => 'Dịch vụ phòng 24/7 với thực đơn đa dạng phục vụ mọi nhu cầu của khách.',
                'trang_thai' => 'published',
                'nguoi_dung_id' => $admin->id
            ],
            [
                'tieu_de' => 'Chương trình team building cho doanh nghiệp',
                'tom_tat' => 'Khách sạn có các gói team building chuyên nghiệp cho doanh nghiệp.',
                'noi_dung' => 'Các gói team building chuyên nghiệp giúp doanh nghiệp xây dựng tinh thần đoàn kết.',
                'trang_thai' => 'published',
                'nguoi_dung_id' => $admin->id
            ],
            [
                'tieu_de' => 'Dịch vụ wedding planner chuyên nghiệp',
                'tom_tat' => 'Khách sạn cung cấp dịch vụ wedding planner chuyên nghiệp cho các cặp đôi.',
                'noi_dung' => 'Dịch vụ wedding planner chuyên nghiệp giúp tổ chức đám cưới hoàn hảo.',
                'trang_thai' => 'published',
                'nguoi_dung_id' => $admin->id
            ],
            [
                'tieu_de' => 'Hệ thống an ninh 24/7',
                'tom_tat' => 'Khách sạn có hệ thống an ninh 24/7 đảm bảo an toàn cho khách hàng.',
                'noi_dung' => 'Hệ thống an ninh 24/7 đảm bảo an toàn tuyệt đối cho khách hàng.',
                'trang_thai' => 'published',
                'nguoi_dung_id' => $admin->id
            ]
        ];

        foreach ($newsData as $data) {
            News::create($data);
        }
    }
}
