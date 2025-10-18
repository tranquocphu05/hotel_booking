<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GioiThieuController extends Controller
{
    public function index()
    {
        return view('client.content.gioithieu');
    }
}
