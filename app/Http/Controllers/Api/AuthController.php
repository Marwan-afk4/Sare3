<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\EmailVerificationCode;
use App\Models\User;
use App\trait\twilio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use twilio;


    public function postOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|unique:users,phone'
        ]);
        $this->sendOtp($request->phone);
        return response()->json([
            'message' => 'OTP sent successfully'
        ]);
    }

    public function CheckOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|unique:users,phone',
            'code' => 'required|string'
        ]);
        $verification = $this->verifyOtp($request->phone, $request->code);
        if ($verification->status === 'approved') {
            $userCreation = User::firstOrCreate([
                'phone' => $request->phone
            ]);

            return response()->json([
                'message' => 'OTP verified successfully'
            ]);
        }
        return response()->json([
            'message' => 'OTP verification failed try again'
        ], 200);
    }

    public function sendEmailVerificationCode(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'phone' => 'nullable|string|exists:users,phone',
            'email' => 'nullable|email|unique:users,email'
        ]);
        if ($validation->fails()) {
            return response()->json([
                'message' => $validation->errors()->first()
            ], 200);
        }
        $user = User::where('phone', $request->phone)->first();
        $code = rand(100000, 999999);
        if ($user) {
            $user->email_code = $code;
            $user->email_verified = 'unverified';
            $user->role = 'user';
            $user->save();
            Mail::to($request->email)->send(new EmailVerificationCode($code));
            return response()->json([
                'message' => 'Email verification code sent successfully'
            ]);
        }
    }

    public function verifyEmailCode(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'phone' => 'nullable|string|exists:users,phone',
            'email' => 'required|email|unique:users,email',
            'code' => 'required|integer'
        ]);
        if ($validation->fails()) {
            return response()->json([
                'message' => $validation->errors()->first()
            ], 200);
        }
        $user = User::where('phone', $request->phone)->first();
        if ($user->email_code == $request->code) {
            $user->email_verified = 'verified';
            $user->email = $request->email;
            $user->email_code = null; // Clear the code after verification
            $user->save();
            return response()->json([
                'message' => 'Email verified successfully'
            ]);
        }
        return response()->json([
            'message' => 'Email verification code is incorrect'
        ], 200);
    }



    public function Postname(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'phone' => 'required|string|exists:users,phone',
            'name' => 'required|string|max:255'
        ]);
        if ($validation->fails()) {
            return response()->json([
                'message' => $validation->errors()->first()
            ], 200);
        }
        $user = User::where('phone', $request->phone)->first();
        if ($user) {
            $user->name = $request->name;
            $user->save();
            return response()->json([
                'message' => 'Name updated successfully'
            ]);
        }
        return response()->json([
            'message' => 'User not found'
        ], 200);
    }




}
