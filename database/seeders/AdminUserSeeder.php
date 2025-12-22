<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Determine which unique key exists on the users table
        $user = new User();
        $table = $user->getTable();

        // Try Schema first
        $columns = Schema::getColumnListing($table);

        // If Schema couldn't find columns (some environments), query information_schema as fallback
        if (empty($columns)) {
            try {
                $dbName = DB::getDatabaseName();
                $rows = DB::select(
                    'SELECT column_name FROM information_schema.columns WHERE table_schema = ? AND table_name = ?'
                    , [$dbName, $table]
                );
                $columns = array_map(fn($r) => $r->column_name, $rows);
            } catch (\Throwable $e) {
                $this->command->error("Cannot detect columns for table {$table}: " . $e->getMessage());
                return;
            }
        }

        $hasEmail = in_array('email', $columns, true);
        $hasUsername = in_array('username', $columns, true);

        if ($hasEmail) {
            $where = ['email' => 'admin@example.com'];
            $attributes = [
                'username' => $hasUsername ? 'admin' : null,
                'email' => 'admin@example.com',
                'password' => Hash::make('Admin@1234'),
                'ho_ten' => 'Administrator',
                'vai_tro' => 'admin',
                'trang_thai' => 'hoat_dong',
            ];
            // remove null values
            $attributes = array_filter($attributes, fn($v) => $v !== null);
        } elseif ($hasUsername) {
            $where = ['username' => 'admin'];
            $attributes = [
                'username' => 'admin',
                'password' => Hash::make('Admin@1234'),
                'ho_ten' => 'Administrator',
                'vai_tro' => 'admin',
                'trang_thai' => 'hoat_dong',
            ];
        } else {
            // if neither exists, bail out gracefully
            $this->command->error("Cannot seed admin: neither 'email' nor 'username' columns exist on table {$table}.");
            return;
        }

        User::updateOrCreate($where, $attributes);
        $this->command->info('Admin user seeded (admin@example.com / Admin@1234)');

        // 2. Sample Staff (nhan_vien)
        User::updateOrCreate(
            ['email' => 'staff@example.com'],
            [
                'username' => 'nhanvien',
                'password' => Hash::make('123456'),
                'ho_ten' => 'Nhân Viên Quản Lý',
                'vai_tro' => 'nhan_vien',
                'trang_thai' => 'hoat_dong',
                'sdt' => '0911222333',
            ]
        );
        $this->command->info('Staff user seeded (staff@example.com / 123456)');

        // 3. Sample Receptionist (le_tan)
        User::updateOrCreate(
            ['email' => 'receptionist@example.com'],
            [
                'username' => 'letan',
                'password' => Hash::make('123456'),
                'ho_ten' => 'Lễ Tân Khách Sạn',
                'vai_tro' => 'le_tan',
                'trang_thai' => 'hoat_dong',
                'sdt' => '0944555666',
            ]
        );
        $this->command->info('Receptionist user seeded (receptionist@example.com / 123456)');

        // 4. Sample Customers (khach_hang)
        $customers = [
            ['email' => 'customer1@example.com', 'username' => 'customer1', 'ho_ten' => 'Nguyễn Văn A', 'sdt' => '0901234567', 'cccd' => '123456789'],
            ['email' => 'customer2@example.com', 'username' => 'customer2', 'ho_ten' => 'Trần Thị B', 'sdt' => '0907654321', 'cccd' => '987654321'],
            ['email' => 'customer3@example.com', 'username' => 'customer3', 'ho_ten' => 'Lê Văn C', 'sdt' => '0912345678', 'cccd' => '456789123'],
            ['email' => 'customer4@example.com', 'username' => 'customer4', 'ho_ten' => 'Phạm Thị D', 'sdt' => '0923456789', 'cccd' => '789123456'],
            ['email' => 'customer5@example.com', 'username' => 'customer5', 'ho_ten' => 'Hoàng Văn E', 'sdt' => '0934567890', 'cccd' => '321654987'],
        ];

        foreach ($customers as $cust) {
            User::updateOrCreate(
                ['email' => $cust['email']],
                array_merge($cust, [
                    'password' => Hash::make('123456'),
                    'vai_tro' => 'khach_hang',
                    'trang_thai' => 'hoat_dong',
                ])
            );
        }
        $this->command->info('Sample customers seeded (123456 password)');
    }


}
