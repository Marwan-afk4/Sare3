<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\EmailVerificationCode;
use App\Services\OtpDeliveryService;
use App\Models\OtpLimit;
use App\Models\User;
use App\Services\PhoneVerificationService;
use App\Services\SignupGiftService;
use Google_Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // ===================== WP OTP (Phone Verification) =====================

    /**
     * Send OTP via WhatsApp for user registration / first-time phone verification.
     */
    public function sendOtp(Request $request)
    {
        $this->startOtpTrace($request, 'user.sendOtp');
        $phoneBeforeNormalize = $request->input('phone');
        $this->normalizePhoneRequest($request);
        Log::info('[otp-trace] phone after normalize', [
            'before' => $phoneBeforeNormalize,
            'after' => $request->input('phone'),
        ]);

        $validation = Validator::make($request->all(), [
            // Must be international format with or without +
            'phone' => ['required', 'string', 'regex:/^\+?[1-9][0-9]{6,14}$/'],
        ], [
            'phone.regex' => 'Phone must be in international format (e.g. +9627XXXXXXXX or 9627XXXXXXXX)',
        ]);

        if ($validation->fails()) {
            Log::warning('[otp-trace] validation failed', [
                'errors' => $validation->errors()->toArray(),
            ]);
            return response()->json([
                'message' => $validation->errors()->first(),
            ], 422);
        }

        $otpCode = rand(100000, 999999);
        $defaultOtpLimit = OtpLimit::where('type', 'user')->value('otp_limit');
        $phone = $request->phone;
        $rawPhone = ltrim($phone, '+');

        Log::info('[otp-trace] looking up user', [
            'phone' => $phone,
            'raw_phone' => $rawPhone,
            'default_otp_limit' => $defaultOtpLimit,
        ]);

        // Robust lookup to handle transition to '+' prefix and avoid duplicate accounts
        $user = User::where('phone', $phone)
                    ->orWhere('phone', $rawPhone)
                    ->first();

        if ($user) {
            Log::info('[otp-trace] user found', [
                'user_id' => $user->id,
                'stored_phone' => $user->phone,
                'otp_used' => $user->otp_used,
                'otp_limit' => $user->otp_limit,
                'role' => $user->role,
                'phone_verified' => $user->phone_verified,
            ]);
            // Ensure user has the standardized phone format with '+'
            if ($user->phone !== $phone) {
                $user->update(['phone' => $phone]);
                Log::info('[otp-trace] stored phone updated to normalized value', ['phone' => $phone]);
            }
        } else {
            // Create new user account
            $user = User::create([
                'phone'          => $phone,
                'role'           => 'user',
                'otp_limit'      => $defaultOtpLimit ?? 5,
                'status'         => 'pending',
                'phone_verified' => false,
            ]);
            Log::info('[otp-trace] user created', [
                'user_id' => $user->id,
                'phone' => $user->phone,
                'otp_limit' => $user->otp_limit,
            ]);
        }

        $userLimit = $user->otp_limit > 0 ? $user->otp_limit : ($defaultOtpLimit ?? 5);

        // Check OTP limit
        if ($user->otp_used >= $userLimit) {
            Log::warning('[otp-trace] otp limit reached', [
                'user_id' => $user->id,
                'otp_used' => $user->otp_used,
                'user_limit' => $userLimit,
            ]);
            return response()->json([
                'message' => 'You have reached your OTP limit. Please contact support.',
            ], 429);
        }

        $user->update([
            'otp_code'       => $otpCode,
            'otp_expires_at' => now()->addMinutes(10),
            'otp_used'       => $user->otp_used + 1,
            'otp_limit'      => $userLimit, // Ensure the limit is explicitly set if it was 0/null
        ]);

        $message = $this->getRandomOtpMessage($otpCode);
        Log::info('[otp-trace] otp generated, calling delivery', [
            'user_id' => $user->id,
            'delivery_phone' => $user->phone,
            'otp_code' => $otpCode,
            'otp_message' => $message,
            'otp_used_after' => $user->otp_used,
            'otp_expires_at' => optional($user->otp_expires_at)?->toDateTimeString(),
        ]);

        try {
            app(OtpDeliveryService::class)->send($user->phone, $message);
            Log::info('[otp-trace] delivery service returned');
        } catch (\Throwable $e) {
            Log::error('[otp-trace] delivery threw exception', [
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }

        $exists = $user->wasRecentlyCreated ? false : true;

        Log::info('[otp-trace] sendOtp json response', [
            'isLogin' => $exists,
            'message' => $exists ? 'OTP sent for login' : 'OTP sent for signup',
        ]);

        return response()->json([
            'message' => $exists ? 'OTP sent for login' : 'OTP sent for signup',
            'isLogin' => $exists,
        ]);
    }

    /**
     * Verify OTP and log the user in (or create account).
     */
    public function verifyOtp(Request $request)
    {
        $this->normalizePhoneRequest($request);

        $validation = Validator::make($request->all(), [
            'phone'    => 'required|string|exists:users,phone',
            'otp_code' => 'required|string',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => $validation->errors()->first(),
            ], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->otp_code !== $request->otp_code) {
            return response()->json(['message' => 'Invalid OTP code'], 422);
        }

        if (now()->isAfter($user->otp_expires_at)) {
            return response()->json(['message' => 'OTP has expired. Please request a new one.'], 422);
        }

        return app(PhoneVerificationService::class)->finalizePhoneVerification($user);
    }

    /**
     * Resend OTP via WhatsApp.
     */
    public function resendOtp(Request $request)
    {
        $this->startOtpTrace($request, 'user.resendOtp');
        $phoneBeforeNormalize = $request->input('phone');
        $this->normalizePhoneRequest($request);
        Log::info('[otp-trace] phone after normalize', [
            'before' => $phoneBeforeNormalize,
            'after' => $request->input('phone'),
        ]);

        $validation = Validator::make($request->all(), [
            'phone' => 'required|string|exists:users,phone',
        ]);

        if ($validation->fails()) {
            Log::warning('[otp-trace] validation failed', [
                'errors' => $validation->errors()->toArray(),
            ]);
            return response()->json([
                'message' => $validation->errors()->first(),
            ], 422);
        }

        $phone = $request->phone;
        $rawPhone = ltrim($phone, '+');
        $user = User::where('phone', $phone)
                    ->orWhere('phone', $rawPhone)
                    ->first();

        Log::info('[otp-trace] looking up user for resend', [
            'phone' => $phone,
            'raw_phone' => $rawPhone,
            'found' => (bool) $user,
            'user_id' => $user?->id,
            'stored_phone' => $user?->phone,
        ]);

        if ($user && $user->phone !== $phone) {
            $user->update(['phone' => $phone]);
            Log::info('[otp-trace] stored phone updated to normalized value', ['phone' => $phone]);
        }

        if (!$user) {
            Log::warning('[otp-trace] no account for resend');
            return response()->json([
                'message' => 'No account found with this phone number.',
            ], 422);
        }

        $defaultOtpLimit = OtpLimit::where('type', 'user')->value('otp_limit') ?? 5;
        $userLimit = $user->otp_limit > 0 ? $user->otp_limit : $defaultOtpLimit;

        // Check OTP limit
        if ($user->otp_used >= $userLimit) {
            Log::warning('[otp-trace] otp limit reached', [
                'user_id' => $user->id,
                'otp_used' => $user->otp_used,
                'user_limit' => $userLimit,
            ]);
            return response()->json([
                'message' => 'You have reached your OTP limit. Please contact support.',
            ], 429);
        }

        $otpCode = rand(100000, 999999);
        $user->update([
            'otp_code'       => $otpCode,
            'otp_expires_at' => now()->addMinutes(10),
            'otp_used'       => $user->otp_used + 1,
            'otp_limit'      => $userLimit, // Ensure the limit is explicitly set if it was 0/null
        ]);

        $message = $this->getRandomOtpMessage($otpCode);
        Log::info('[otp-trace] otp generated, calling delivery', [
            'user_id' => $user->id,
            'delivery_phone' => $user->phone,
            'otp_code' => $otpCode,
            'otp_message' => $message,
            'otp_used_after' => $user->otp_used,
        ]);

        try {
            app(OtpDeliveryService::class)->send($user->phone, $message);
            Log::info('[otp-trace] delivery service returned');
        } catch (\Throwable $e) {
            Log::error('[otp-trace] delivery threw exception', [
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }

        return response()->json([
            'message' => 'A new OTP has been sent to your WhatsApp.',
        ]);
    }

    // ===================== Email Verification =====================

    public function sendEmailVerificationCode(Request $request)
    {
        $this->normalizePhoneRequest($request);

        $validation = Validator::make($request->all(), [
            'phone' => 'nullable|string|exists:users,phone',
            'email' => 'nullable|email|unique:users,email',
        ]);
        if ($validation->fails()) {
            return response()->json([
                'message' => $validation->errors()->first(),
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
                'message' => 'Email verification code sent successfully',
            ]);
        }
    }

    public function verifyEmailCode(Request $request)
    {
        $this->normalizePhoneRequest($request);

        $validation = Validator::make($request->all(), [
            'phone' => 'nullable|string|exists:users,phone',
            'email' => 'required|email|exists:users,email',
            'code'  => 'required|integer',
        ]);
        if ($validation->fails()) {
            return response()->json([
                'message' => $validation->errors()->first(),
            ], 401);
        }
        $user = User::where('phone', $request->phone)->first();
        if ($user->email_code == $request->code) {
            $user->email_verified = 'verified';
            $user->email = $request->email;
            $user->email_code = null;
            $user->save();

            return response()->json([
                'message' => 'Email verified successfully',
            ]);
        }

        return response()->json([
            'message' => 'Email verification code is incorrect',
        ], 401);
    }

    public function postName(Request $request)
    {
        $this->normalizePhoneRequest($request);

        $validation = Validator::make($request->all(), [
            'phone'     => 'required|string|exists:users,phone',
            'name'      => 'required|string|max:255',
            'gender'    => 'nullable|string|in:male,female',
            'fcm_token' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => $validation->errors()->first(),
            ], 401);
        }

        $phone = $request->phone;
        $rawPhone = ltrim($phone, '+');
        $user = User::where('phone', $phone)
            ->orWhere('phone', $rawPhone)
            ->first();

        if (! $user) {
            return response()->json([
                'message' => 'User not found',
            ], 401);
        }

        $isFirstProfileCompletion = blank($user->name);
        $wasDriver = $user->isDriver();
        $token = $user->createToken('auth_token')->plainTextToken;

        $user->name      = $request->name;
        if (! $wasDriver) {
            $user->role      = 'user';
            $user->activity  = 'active';
        }
        $user->gender    = $request->gender ?? null;
        $user->fcm_token = $request->input('fcm_token', $user->fcm_token);
        if ($user->wallet === null) {
            $user->wallet = 0;
        }
        $user->save();

        $signupGift = $wasDriver
            ? null
            : app(SignupGiftService::class)->grantOnRegistrationComplete(
                $user->fresh(),
                $isFirstProfileCompletion
            );
        $user->refresh();

        return response()->json([
            'token'   => $token,
            'message' => 'You can login now',
            'wallet'  => (float) ($user->wallet ?? 0),
            'signup_gift' => $signupGift ? [
                'granted' => true,
                'amount'  => $signupGift['amount'],
            ] : [
                'granted' => false,
                'amount'  => 0,
            ],
        ]);
    }

    public function emailVerficationFirst(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => $validation->errors()->first(),
            ], 401);
        }

        $existingUser = User::where('email', $request->email)->first();
        $code = rand(100000, 999999);

        if ($existingUser) {
            if ($existingUser->email_verified == 'unverified') {
                $existingUser->update([
                    'email_code'     => $code,
                    'email_verified' => 'unverified',
                ]);
                Mail::to($existingUser->email)->send(new EmailVerificationCode($code));

                return response()->json([
                    'message' => 'Verification code resent. Please check your email.',
                ]);
            } else {
                Mail::to($existingUser->email)->send(new EmailVerificationCode($code));
                $existingUser->update([
                    'email_code'     => $code,
                    'email_verified' => 'unverified',
                ]);

                return response()->json([
                    'message' => "Email already verified. You can login but we will send to verify it's you",
                ]);
            }
        }

        $user = User::create([
            'email'          => $request->email,
            'role'           => 'user',
            'email_code'     => $code,
            'email_verified' => 'unverified',
            'status'         => 'approved',
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
            'code'  => 'required',
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
            'email_code'     => null,
            'activity'       => 'active',
        ]);

        if ($user->phone) {
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Email verified successfully you can login now',
                'token'   => $token,
            ]);
        }

        return response()->json([
            'message' => 'Email verified successfully, please verify your phone number to complete login.',
            'user'    => $user,
        ]);
    }

    // ===================== Google Auth =====================

    public function googleAuth(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'id_token' => 'required|string',
        ]);
        if ($validation->fails()) {
            return response()->json([
                'message' => $validation->errors()->first(),
            ], 401);
        }

        $client = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
        $payload = $client->verifyIdToken($request->id_token);

        if (!$payload) {
            return response()->json([
                'message' => 'Invalid Google ID token',
            ], 401);
        }

        $email    = $payload['email'];
        $name     = $payload['name'];
        $googleId = $payload['sub'];

        $user = User::where('email', $email)->first();

        if ($user) {
            $token = $user->createToken('google_token')->plainTextToken;

            return response()->json([
                'message' => 'This account already exists, you can log in now.',
                'token'   => $token,
                'user'    => $user,
            ]);
        }

        $user = User::create([
            'email'          => $email,
            'name'           => $name,
            'id_token'       => $googleId,
            'email_verified' => 'verified',
            'role'           => 'user',
            'status'         => 'approved',
            'wallet'         => 0,
        ]);

        $signupGift = app(SignupGiftService::class)->grantIfEligible($user);
        $user->refresh();

        $token = $user->createToken('google_token')->plainTextToken;

        return response()->json([
            'message' => 'Google account registered successfully.',
            'token'   => $token,
            'user'    => $user,
            'signup_gift' => $signupGift ? [
                'granted' => true,
                'amount'  => $signupGift['amount'],
            ] : [
                'granted' => false,
                'amount'  => 0,
            ],
        ]);
    }

    // ===================== Login (Password + WP OTP) =====================

    /**
     * Step 1: Validate credentials, send OTP via WhatsApp.
     */
    public function login(Request $request)
    {
        $this->normalizePhoneRequest($request);

        $validation = Validator::make($request->all(), [
            'phone'    => 'nullable|string|exists:users,phone',
            'email'    => 'nullable|email|exists:users,email',
            'password' => 'required|string',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => $validation->errors()->first(),
            ], 401);
        }

        $user = User::where(function ($q) use ($request) {
            if ($request->filled('phone')) {
                $q->where('phone', $request->phone);
            }
            if ($request->filled('email')) {
                $q->orWhere('email', $request->email);
            }
        })->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => $user,
        ]);
    }

    // ===================== Logout =====================

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }

    /**
     * Generate a random OTP message in Arabic to prevent spam/ban.
     *
     * @param string|int $otpCode
     * @return string
     */
    private function getRandomOtpMessage($otpCode)
    {
        $templates = [
            "سريع: {$otpCode}",
            "Sarea: {$otpCode}",
            "{$otpCode} - سريع",
            "{$otpCode} - Sarea",
            "تطبيق سريع: {$otpCode}",
            "Sarea App: {$otpCode}",
        ];

        $template = $templates[array_rand($templates)];

        return $template;
    }
}
