<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\EmailVerificationCode;
use App\Models\User;
use App\trait\twilio;
use Google_Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use twilio;

    public function postOtp(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'phone' => 'required|string'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => $validation->errors()->first()
            ], 200);
        }
        $this->sendOtp($request->phone);

        $exists = User::where('phone', $request->phone)->exists();

        return response()->json([
            'message' => $exists ? 'Otp sent for login' : 'Otp sent for signup'
        ]);
    }

    public function CheckOtp(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'phone' => 'required|string',
            'code' => 'required|string',
            'email' => 'nullable|email|exists:users,email'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => $validation->errors()->first()
            ], 422);
        }

        // Step 1: Verify OTP
        $verification = $this->verifyOtp($request->phone, $request->code);

        if ($verification->status !== 'approved') {
            return response()->json([
                'message' => 'OTP verification failed, try again'
            ], 422);
        }

        $user = null;

        // Step 2: Handle case when email is provided (user started with email first)
        if ($request->filled('email')) {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'message' => 'Email not found'
                ], 404);
            }

            // If phone is already used by another user (avoid duplicate phone numbers)
            $phoneUsedByAnother = User::where('phone', $request->phone)
                                    ->where('id', '!=', $user->id)
                                    ->exists();

            if ($phoneUsedByAnother) {
                return response()->json([
                    'message' => 'Phone number already used by another account'
                ], 409);
            }

            // Attach phone to existing email user
            $user->phone = $request->phone;
            $user->save();
        }

        // Step 3: If email is not provided, login/register using phone
        if (!$user) {
            $user = User::firstOrCreate(['phone' => $request->phone]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'OTP verified successfully',
            'token' => $token,
            'user' => $user
        ]);
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
            ], 401);
        }
        $user = User::where('phone', $request->phone)->first();
        $code = rand(100000, 999999);
        if ($user) {
            $user->email_code = $code;
            $user->email_verified = 'unverified';
            $user->role = 'user';
            $user->email = $request->email;
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
            'email' => 'required|email|exists:users,email',
            'code' => 'required|integer'
        ]);
        if ($validation->fails()) {
            return response()->json([
                'message' => $validation->errors()->first()
            ], 401);
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
        ], 401);
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
            ], 401);
        }

        $user = User::where('phone', $request->phone)->first();

        $token = $user->createToken('auth_token')->plainTextToken;

        if ($user) {
            $user->name = $request->name;
            $user->save();
            return response()->json([
                'token' => $token,
                'message' => 'You can login now',
            ]);
        }
        return response()->json([
            'message' => 'User not found'
        ], 401);
    }

    public function emailVerficationFirst(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => $validation->errors()->first()
            ], 401);
        }

        $existingUser = User::where('email', $request->email)->first();
        $code = rand(100000, 999999);

        if ($existingUser) {
            if ($existingUser->email_verified == 'unverified') {
                // Resend verification code
                $existingUser->update([
                    'email_code' => $code,
                    'email_verified' => 'unverified',
                ]);

                Mail::to($existingUser->email)->send(new EmailVerificationCode($code));

                return response()->json([
                    'message' => 'Verification code resent. Please check your email.',
                ]);
            } else {
                // Email already verified, proceed to login or notify
                Mail::to($existingUser->email)->send(new EmailVerificationCode($code));
                $existingUser->update([
                    'email_code' => $code,
                    'email_verified' => 'unverified',
                ]);
                return response()->json([
                    'message' => "Email already verified. You can login but we will send to verify it's you",
                ]);
            }
        }

        // Email doesn't exist, create new user and send verification
        $user = User::create([
            'email' => $request->email,
            'role' => 'user',
            'email_code' => $code,
            'email_verified' => 'unverified',
        ]);

        Mail::to($user->email)->send(new EmailVerificationCode($code));

        return response()->json([
            'message' => 'Verification code sent. Please check your email.',
        ]);
    }


    public function verifyEmailFirst(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'code' => 'required'
        ]);

        if ($validation->fails()) {
            return response()->json($validation->errors(), 401);
        }

        $user = User::where('email', $request->email)->first();

        if ($user->email_code !== $request->code) {
            return response()->json(['error' => 'Invalid verification code.'], 401);
        }

        $user->update([
            'email_verified' => 'verified',
            'email_code' => null,
            'activity' => 'active',
        ]);

        // Check if user has phone number to generate token (means login)
        if ($user->phone) {
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Email verified successfully you can login now',
                'token' => $token,
            ]);
        }

        // No phone number yet, so just return verification success without token
        return response()->json([
            'message' => 'Email verified successfully, please verify your phone number to complete login.',
            'user' => $user,
        ]);
    }


    public function googleAuth(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'id_token' => 'required|string'
        ]);
        if ($validation->fails()) {
            return response()->json([
                'message' => $validation->errors()->first()
            ], 401);
        }

        $client = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
        $payload = $client->verifyIdToken($request->id_token);

        if($payload){
            $email = $payload['email'];
            $name = $payload['name'];
            $id_token = $payload['sub'];

            $user =User::firstOrCreate([
                'email' => $email,
                'name' => $name,
                'id_token' => $id_token,
            ]);

            return response()->json([
                'message' => 'Google account registered successfully',
                'user' => $user
            ]);
        }
        else{
            return response()->json([
                'message' => 'Invalid Google ID token'
            ], 401);
        }
    }

    public function login(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'phone' => 'nullable|string|exists:users,phone',
            'email' => 'nullable|email|exists:users,email',
            'password' => 'required|string'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => $validation->errors()->first()
            ], 401);
        }

        $user = User::where('phone', $request->phone)->orWhere('email', $request->email)->first();

        if (!$user || !password_verify($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user
        ]);
    }
}
