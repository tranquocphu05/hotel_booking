<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;

class PhongController extends Controller
{
    private $rooms = [
        [
            'id' => 1,
            'name' => 'Premium King Room',
            'img' => 'img/room/room-1.jpg',
            'price' => 1590000,
            'desc' => 'Phòng sang trọng với giường King, view đẹp và đầy đủ tiện nghi.',
            'size' => '30m²',
            'capacity' => '3 người',
            'bed' => '1 King Bed',
            'services' => 'WiFi, Tivi, Điều hòa, Phòng tắm riêng'
        ],
        [
            'id' => 2,
            'name' => 'Deluxe Room',
            'img' => 'img/room/room-2.jpg',
            'price' => 1200000,
            'desc' => 'Phòng thoải mái với tiện nghi hiện đại và view tuyệt đẹp.',
            'size' => '25m²',
            'capacity' => '2 người',
            'bed' => '1 Queen Bed',
            'services' => 'WiFi, Tivi, Điều hòa'
        ],
        [
            'id' => 3,
            'name' => 'Double Room',
            'img' => 'img/room/room-3.jpg',
            'price' => 1100000,
            'desc' => 'Phòng đôi rộng rãi, phù hợp cho 2 khách.',
            'size' => '28m²',
            'capacity' => '2 người',
            'bed' => '2 Single Beds',
            'services' => 'WiFi, Tivi'
        ],
        [
            'id' => 4,
            'name' => 'Luxury Room',
            'img' => 'img/room/room-4.jpg',
            'price' => 2000000,
            'desc' => 'Phòng sang trọng với thiết kế hiện đại, view hướng biển.',
            'size' => '35m²',
            'capacity' => '3 người',
            'bed' => '1 King Bed',
            'services' => 'WiFi, Tivi, Điều hòa, Minibar, Phòng tắm riêng'
        ],
        [
            'id' => 5,
            'name' => 'Room With View',
            'img' => 'img/room/room-5.jpg',
            'price' => 1800000,
            'desc' => 'Phòng đẹp với view hướng ra thành phố, thiết kế hiện đại.',
            'size' => '32m²',
            'capacity' => '2 người',
            'bed' => '1 Queen Bed',
            'services' => 'WiFi, Tivi, Điều hòa, Balcony'
        ],
        [
            'id' => 6,
            'name' => 'Small View',
            'img' => 'img/room/room-6.jpg',
            'price' => 900000,
            'desc' => 'Phòng nhỏ gọn, tiết kiệm nhưng vẫn đầy đủ tiện nghi.',
            'size' => '20m²',
            'capacity' => '1 người',
            'bed' => '1 Single Bed',
            'services' => 'WiFi, Tivi'
        ],
    ];

    // Danh sách phòng
    public function index()
    {
        $rooms = $this->rooms;
        return view('client.content.phong', compact('rooms'));
    }

    // Chi tiết phòng
    public function show($id)
    {
        $room = collect($this->rooms)->firstWhere('id', $id);

        if (!$room) {
            abort(404, 'Phòng không tồn tại');
        }

        return view('client.content.show', compact('room'));
    }
}
