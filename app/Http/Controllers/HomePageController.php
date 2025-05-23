<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class HomePageController extends Controller
{


    public function index()
    {
        $userCount = User::where('role', 'user')->count();
        $driverCount = User::where('role', 'driver')->count();

        return view('home.welcome', compact('userCount', 'driverCount'));
    }
}
