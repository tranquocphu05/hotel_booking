<?php

namespace App\Http\Controllers\Admin;

use App\Models\DatPhong;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DatPhongController extends Controller
{
    public function index()
    {
        $bookings = DatPhong::where('nguoi_dung_id', Auth::id())
            ->with(['phong', 'phong.loaiPhong', 'voucher']) // Eager load relationships
            ->orderBy('ngay_dat', 'desc')
            ->paginate(10);
        // dd($bookings);
        return view('admin.dat_phong.index', compact('bookings'));
    }
}
