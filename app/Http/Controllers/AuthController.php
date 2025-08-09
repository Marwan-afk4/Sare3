<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function showLoginForm() {
        //echo Hash::make('admin');
        if (Auth::check()) {
            return redirect()->route('users.index')->with('success', __('You are already logged in'));
        }
        return view('auth.login');
    }

    public function login(Request $request) {
        $request->validate([
            'phone' => [
                'required',
                'string',
            ],
            'password' => 'required|string'
        ]);

        $user = User::where('phone', $request->phone)->first();
        // echo Hash::make($request->password);
        // echo bcrypt($request->password);
        // die;
        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors(['error' => __('Mobile number or password is incorrect')])->withInput();
        }

        if($user->role !== 'admin') {
            return back()->withErrors(['error' => __('You are not authorized to access this area')])->withInput();
        }

        Auth::login($user,true);
        $request->session()->regenerate();

        return redirect()->intended(route('home'))->with('success', __('Logged in successfully'));
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $user->forceFill(['remember_token' => null])->save();
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerate();

        return redirect()->route('login')->with('message', __('Logged out successfully'));
    }
}
