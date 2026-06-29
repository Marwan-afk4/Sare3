<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LandingPageController extends Controller
{
    /**
     * Display the public landing page.
     * Redirects authenticated users to the dashboard.
     */
    public function index()
    {
        if (Auth::guard('sanctum')->check() || Auth::check()) {
            return redirect()->route('home');
        }

        return view('landing');
    }
}
