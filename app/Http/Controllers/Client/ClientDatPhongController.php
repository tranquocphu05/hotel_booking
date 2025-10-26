<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClientDatPhongController extends Controller
{
    public function index(Request $request)
    {
        return view('client.dat_phong.index');
    }

    public function daDatPhong(Request $request)
    {
        return view('client.dat_phong.da_dat');
    }
}
