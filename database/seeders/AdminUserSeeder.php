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
    }
}
