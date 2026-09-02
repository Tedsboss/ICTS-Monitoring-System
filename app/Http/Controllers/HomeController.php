<?php

namespace App\Http\Controllers;

use App\Models\Parameter;

class HomeController extends Controller
{
    public function index()
    {
        // Home announcement configuration
        $homeannouncement = Parameter::find(2);

        return view('home', compact('homeannouncement'));
    }
}
