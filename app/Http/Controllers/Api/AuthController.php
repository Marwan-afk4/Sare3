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

    /**
     * send otp to phone
     * @bodyparam phone string required The user's phone number. Example: +201012345678
     * @response 200 {
     *      "message": "OTP sent successfully"
     *    }
     * @response 200 scenario="Validation error" {
     *      "message": "The phone field is required."
     *    }
     */
    public function postOtp(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'phone' => 'required|string|unique:users,phone'
        ]);
        if ($validation->fails()) {
            return response()->json([
                'message' => $validation->errors()->first()
            ], 200);
        }
        $this->sendOtp($request->phone);
        return response()->json([
            'message' => 'OTP sent successfully'
        ]);
    }

    /**
     * Verify phone OTP and optionally bind to email
     *
     * This endpoint verifies the OTP sent to the user's phone. If an email is included and exists,
     * it will bind the phone number to the user with that email. Otherwise, it creates a new user with the phone number.
     *
     * @bodyParam phone string required The user's phone number. Example: +201012345678
     * @bodyParam code string required The OTP code received by the user. Example: 123456
     * @bodyParam email string nullable The user's email (must already exist if provided). Example: test@example.com
     *
     * @response 200 {
     *   "message": "OTP verified successfully",
     *   "token": "1|a1b2c3d4e5f6g7h8"
     * }
     *
     * @response 200 scenario="OTP failure" {
     *   "message": "OTP verification failed, try again"
     * }
     *
     * @response 404 scenario="Email not found" {
     *   "message": "Email not found"
     * }
     */
    public function CheckOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string',
            'email' => 'nullable|email|exists:users,email'
        ]);

        $verification = $this->verifyOtp($request->phone, $request->code);

        if ($verification->status === 'approved') {

            $user = null;

            if ($request->filled('email')) {
                $user = User::where('email', $request->email)->first();

                if ($user) {
                    $user->phone = $request->phone;
                    $user->save();
                } else {
                    return response()->json([
                        'message' => 'Email not found'
                    ], 404);
                }
            }

            if (!$user) {
                $user = User::firstOrCreate(['phone' => $request->phone]);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'OTP verified successfully',
                'token' => $token
            ]);
        }

        return response()->json([
            'message' => 'OTP verification failed, try again'
        ], 200);
    }


    /**
     * Send email verification code
     *
     * This endpoint sends a 6-digit verification code to the user's email.
     * It links the email to an existing user by phone if provided.
     *
     * @bodyParam phone string nullable The user's phone number (must exist in the users table). Example: +201012345678
     * @bodyParam email string nullable The user's email address (must be unique). Example: user@example.com
     *
     * @response 200 {
     *   "message": "Email verification code sent successfully"
     * }
     *
     * @response 200 scenario="Validation error" {
     *   "message": "The email field must be a valid email address."
     * }
     */
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

    /**
     * Verify email with code
     *
     * This endpoint verifies the user's email using the provided 6-digit code.
     * The email must be unique and the phone must exist in the users table (if provided).
     *
     * @bodyParam phone string nullable The user's phone number (must exist in the users table). Example: +201012345678
     * @bodyParam email string required The user's email address (must be unique). Example: user@example.com
     * @bodyParam code integer required The 6-digit verification code sent to the user's email. Example: 123456
     *
     * @response 200 {
     *   "message": "Email verified successfully"
     * }
     *
     * @response 200 scenario="Invalid code" {
     *   "message": "Email verification code is incorrect"
     * }
     *
     * @response 200 scenario="Validation error" {
     *   "message": "The email field is required."
     * }
     */
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


    /**
     * Update user's name
     *
     * This endpoint updates the user's name based on their phone number.
     *
     * @bodyParam phone string required The user's phone number. Must exist in the database. Example: +201012345678
     * @bodyParam name string required The name to be set for the user. Example: John Doe
     *
     * @response 200 {
     *   "message": "Name updated successfully"
     * }
     *
     * @response 200 scenario="Validation error" {
     *   "message": "The name field is required."
     * }
     *
     * @response 200 scenario="User not found" {
     *   "message": "User not found"
     * }
     */
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


    /**
     * Send email verification code (first step)
     *
     * This endpoint starts the email verification process. It sends a 6-digit verification code to the provided email.
     *
     * @bodyParam email string nullable The user's email. Must be unique if not already registered. Example: user@example.com
     *
     * @response 200 scenario="New user or unverified email" {
     *   "message": "Go and check your email to verify your account , the code will expire after 5 min"
     * }
     *
     * @response 409 scenario="Email already verified" {
     *   "message": "This email is already registered"
     * }
     *
     * @response 200 scenario="Validation error" {
     *   "message": "The email has already been taken."
     * }
     */
    public function emailVerficationFirst(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'email' => 'nullable|email|unique:users,email'
        ]);
        if ($validation->fails()) {
            return response()->json([
                'message' => $validation->errors()->first()
            ], 200);
        }
        $excistUser = User::where('email', $request->email)->first();
        $code = rand(100000, 999999);

        if ($excistUser) {
        if ($excistUser->email_verified == 'unverified') {
            $excistUser->update([
                'email' => $request->email,
                'role' => 'user',
                'email_code' => $code,
                'email_verified' => 'unverified',
            ]);

            Mail::to($excistUser->email)->send(new EmailVerificationCode($code));

            return response()->json([
                'message' => 'Go and check your email to verify your account , the code will expire after 5 min',
            ]);
        } elseif($excistUser->email_verified == 'verified') {
            return response()->json([
                'message' => 'This email is already registered',
            ], 409);
        }
    }

        $user = User::create([
            'email' => $request->email,
            'role' => 'user',
            'email_code' => $code,
            'email_verified' => 'unverified',
        ]);

        Mail::to($user->email)->send(new EmailVerificationCode($code));

        return response()->json([
            'message' => 'Go and check your email to verify your account , the code will expire after 5 min',
        ]);

    }

    /**
     * Verify email using code (first step)
     *
     * This endpoint verifies the user's email using the code sent previously.
     *
     * @bodyParam email string required The email address to verify. Must already exist in the users table. Example: user@example.com
     * @bodyParam code string required The 6-digit verification code sent to the email. Example: 123456
     *
     * @response 200 {
     *   "message": "Email verified successfully"
     * }
     *
     * @response 400 scenario="Invalid code" {
     *   "error": "Invalid verification code."
     * }
     *
     * @response 422 scenario="Validation error" {
     *   "email": [
     *     "The selected email is invalid."
     *   ],
     *   "code": [
     *     "The code field is required."
     *   ]
     * }
     */
    public function verifyEmailFirst(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'code' => 'required'
        ]);

        if ($validation->fails()) {
            return response()->json($validation->errors(), 422);
        }

        $user = User::where('email', $request->email)->first();

        if ($user->email_code !== $request->code) {
            return response()->json(['error' => 'Invalid verification code.'], 400);
        }

        $user->update([
            'email_verified' => 'verified',
            'email_code' => null,
            'activity' => 'active',
        ]);

        return response()->json(['message' => 'Email verified successfully']);
    }

    /**
     * Authenticate using Google
     *
     * This endpoint allows users to authenticate or register using their Google account. You must send a valid `id_token` obtained from Google Sign-In.
     *
     * @bodyParam id_token string required The ID token received from Google. Example: ya29.a0AfH6SMC...xyz
     *
     * @response 200 {
     *   "message": "Google account registered successfully"
     * }
     *
     * @response 200 scenario="Invalid token" {
     *   "message": "Invalid Google ID token"
     * }
     *
     * @response 200 scenario="Validation error" {
     *   "message": "The id token field is required."
     * }
     */
    public function googleAuth(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'id_token' => 'required|string'
        ]);
        if ($validation->fails()) {
            return response()->json([
                'message' => $validation->errors()->first()
            ], 200);
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
            ], 200);
        }
    }
}
