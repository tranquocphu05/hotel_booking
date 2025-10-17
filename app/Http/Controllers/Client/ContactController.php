<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        return view('client.content.contact'); // trỏ đến file resources/views/client/content/contact.blade.php
    }
}
