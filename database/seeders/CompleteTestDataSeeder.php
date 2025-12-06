<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\LoaiPhong;
use App\Models\Phong;
use Illuminate\Support\Facades\Hash;

class CompleteTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder creates a complete test dataset including:
     * - Admin and customer users
     * - Room types with different prices
     * - Multiple rooms for each type
     * - Vouchers
     * - Bookings with various statuses
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting complete test data seeding...');

        // 1. Create users if not exist
        $this->seedUsers();

        // 2. Create room types if not exist
        $this->seedRoomTypes();

        // 3. Create rooms if not exist
        $this->seedRooms();

        // 4. Create vouchers
        $this->call(VoucherSeeder::class);

        // 5. Create bookings with pivot table data
        $this->call(BookingSeeder::class);

        $this->command->info('✅ Complete test data seeding finished!');
    }

    private function seedUsers(): void
    {
        // Create admin if not exists
        if (!User::where('email', 'admin@hotel.com')->exists()) {
            User::create([
                'username' => 'admin',
                'email' => 'admin@hotel.com',
                'password' => Hash::make('password'),
                'ho_ten' => 'Administrator',
                'sdt' => '0900000000',
                'dia_chi' => 'Hà Nội',
                'cccd' => '000000000000',
                'vai_tro' => 'admin',
                'trang_thai' => 'hoat_dong',
            ]);
            $this->command->info('✅ Created admin user');
        }

        // Create test customers
        $customers = [
            [
                'username' => 'customer1',
                'email' => 'customer1@example.com',
                'ho_ten' => 'Nguyễn Văn A',
                'sdt' => '0912345678',
                'cccd' => '001234567890',
            ],
            [
                'username' => 'customer2',
                'email' => 'customer2@example.com',
                'ho_ten' => 'Trần Thị B',
                'sdt' => '0923456789',
                'cccd' => '002345678901',
            ],
            [
                'username' => 'customer3',
                'email' => 'customer3@example.com',
                'ho_ten' => 'Lê Văn C',
                'sdt' => '0934567890',
                'cccd' => '003456789012',
            ],
        ];

        foreach ($customers as $customer) {
            if (!User::where('email', $customer['email'])->exists()) {
                User::create(array_merge($customer, [
                    'password' => Hash::make('password'),
                    'dia_chi' => 'Hà Nội',
                    'vai_tro' => 'khach_hang',
                    'trang_thai' => 'hoat_dong',
                ]));
            }
        }
        $this->command->info('✅ Created customer users');
    }

    private function seedRoomTypes(): void
    {
        $roomTypes = [
            [
                'ten_loai' => 'Standard',
                'mo_ta' => 'Phòng tiêu chuẩn với đầy đủ tiện nghi cơ bản',
                'gia_co_ban' => 800000,
                'gia_khuyen_mai' => 700000,
                'so_luong_phong' => 20,
                'so_luong_trong' => 20,
                'diem_danh_gia' => 4.2,
                'so_luong_danh_gia' => 45,
                'trang_thai' => 'hoat_dong',
            ],
            [
                'ten_loai' => 'Deluxe',
                'mo_ta' => 'Phòng cao cấp với view đẹp và tiện nghi hiện đại',
                'gia_co_ban' => 1500000,
                'gia_khuyen_mai' => 1300000,
                'so_luong_phong' => 15,
                'so_luong_trong' => 15,
                'diem_danh_gia' => 4.5,
                'so_luong_danh_gia' => 67,
                'trang_thai' => 'hoat_dong',
            ],
            [
                'ten_loai' => 'Suite',
                'mo_ta' => 'Phòng suite sang trọng với không gian rộng rãi',
                'gia_co_ban' => 2500000,
                'gia_khuyen_mai' => 2200000,
                'so_luong_phong' => 10,
                'so_luong_trong' => 10,
                'diem_danh_gia' => 4.8,
                'so_luong_danh_gia' => 89,
                'trang_thai' => 'hoat_dong',
            ],
            [
                'ten_loai' => 'Presidential',
                'mo_ta' => 'Phòng tổng thống với dịch vụ 5 sao',
                'gia_co_ban' => 5000000,
                'gia_khuyen_mai' => null,
                'so_luong_phong' => 5,
                'so_luong_trong' => 5,
                'diem_danh_gia' => 5.0,
                'so_luong_danh_gia' => 23,
                'trang_thai' => 'hoat_dong',
            ],
        ];

        foreach ($roomTypes as $roomType) {
            if (!LoaiPhong::where('ten_loai', $roomType['ten_loai'])->exists()) {
                LoaiPhong::create($roomType);
            }
        }
        $this->command->info('✅ Created room types');
    }

    private function seedRooms(): void
    {
        $loaiPhongs = LoaiPhong::all();

        foreach ($loaiPhongs as $loaiPhong) {
            $existingCount = Phong::where('loai_phong_id', $loaiPhong->id)->count();
            $needToCreate = $loaiPhong->so_luong_phong - $existingCount;

            if ($needToCreate > 0) {
                $startNumber = $existingCount + 1;
                
                for ($i = 0; $i < $needToCreate; $i++) {
                    $roomNumber = ($startNumber + $i);
                    $floor = ceil($roomNumber / 10);
                    
                    Phong::create([
                        'loai_phong_id' => $loaiPhong->id,
                        'so_phong' => str_pad($roomNumber, 3, '0', STR_PAD_LEFT),
                        'ten_phong' => $loaiPhong->ten_loai . ' ' . str_pad($roomNumber, 3, '0', STR_PAD_LEFT),
                        'tang' => $floor,
                        'huong_cua_so' => ['Đông', 'Tây', 'Nam', 'Bắc'][rand(0, 3)],
                        'trang_thai' => 'trong',
                        'co_ban_cong' => rand(0, 1) == 1,
                        'co_view_dep' => rand(0, 1) == 1,
                        'gia_rieng' => null,
                        'gia_bo_sung' => rand(0, 1) == 1 ? rand(50000, 200000) : null,
                    ]);
                }
                $this->command->info("✅ Created {$needToCreate} rooms for {$loaiPhong->ten_loai}");
            }
        }
    }
}
