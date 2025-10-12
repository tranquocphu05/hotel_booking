<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Booking;
use App\Models\Comment;
use App\Models\Room;
use Illuminate\Support\Carbon;
use stdClass;

class RoomReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Nếu chưa có user, booking, room thì tạo tạm dữ liệu giả
        $user = User::first() ?? User::factory()->create([
            'name' => 'Nguyễn Đức Thiện',
            'email' => 'thien@example.com',
            'password' => bcrypt('123456'),
        ]);

        // $booking = Booking::first() ?? Booking::factory()->create([
        //     'user_id' => $user->id,
        //     'code' => 'BOOK-' . rand(1000, 9999),
        //     'status' => 'completed',
        // ]);

        // $room = Room::first() ?? Room::factory()->create([
        //     'name' => 'Phòng Deluxe City View',
        //     'price' => 1200000,
        // ]);

        // 🏨 Nếu chưa có bảng booking thật, tạo 5 đối tượng giả
        $bookings = [];
        for ($i = 1; $i <= 5; $i++) {
            $booking = new stdClass();
            $booking->id = $i;
            $booking->booking_code = 'BOOK-' . rand(1000, 9999);
            $bookings[] = $booking;
        }

        // 🔗 Tạo danh sách room_booking giả
        $roomBookings = [];
        for ($i = 1; $i <= 5; $i++) {
            $room_booking = new stdClass();
            $room_booking->id = $i;
            $room_booking->booking_id = rand(1, 5);
            $room_booking->room_id = rand(1, 5);
            $roomBookings[] = $room_booking;
        }

        // 🏠 Tạo danh sách room giả (chỉ id)
        $rooms = [];
        for ($i = 1; $i <= 5; $i++) {
            $room = new stdClass();
            $room->id = $i;
            $rooms[] = $room;
        }

        for ($i = 1; $i <= 10; $i++) {
            Comment::create([
                'booking_id' => $booking->id,
                'room_booking_id' => $room_booking->id,
                'room_id' => $room->id,
                'user_id' => $user->id,
                'rating' => rand(3, 5),
                'content' => fake()->paragraph(3),
                'images' => json_encode([
                    'uploads/reviews/sample' . rand(1, 3) . '.jpg',
                ]),
                'reply' => $i % 3 === 0 ? 'Cảm ơn bạn đã đánh giá!' : null,
                'is_active' => $i % 4 !== 0, // Một vài đánh giá bị ẩn
                'hidden_reason' => $i % 4 === 0 ? 'Ngôn từ không phù hợp' : null,
                'is_updated' => (bool) rand(0, 1),
                'reply_at' => $i % 3 === 0 ? Carbon::now()->subDays(rand(1, 7)) : null,
                'created_at' => Carbon::now()->subDays(rand(1, 30)),
            ]);
        }
    }
}
