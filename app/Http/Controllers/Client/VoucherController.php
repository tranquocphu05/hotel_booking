<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Voucher;

class VoucherController extends Controller
{
    public function getVoucher()
    {
        $vouchers = Voucher::where('trang_thai', 1)->get(); // chỉ lấy voucher đang hoạt động
        return view('client.content.voucher', compact('vouchers'));
    }
}
