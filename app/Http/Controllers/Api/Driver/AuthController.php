<?php

namespace App\Http\Controllers\Api\Driver;

use App\Enums\ActiveStatuses;
use App\Enums\VehicleType;
use App\Http\Controllers\Controller;
use App\Mail\EmailVerificationCode;
use App\Services\OtpDeliveryService;
use App\Services\SignupGiftService;
use App\Models\CarCategory;
use App\Models\RiderVehicle;
use App\Models\CarModel;
use App\Models\CarType;
use App\Models\City;
use App\Models\DocumentType;
use App\Models\DriverCar;
use App\Models\DriverDocument;
use App\Models\OtpLimit;
use App\Models\User;
use App\trait\ImageUpload;
use Google_Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use ImageUpload;

    // ===================== WP OTP (Phone Verification) =====================

    /**
     * Send OTP via WhatsApp for driver registration / first-time phone verification.
     */
    public function sendOtp(Request $request)
    {
        $this->startOtpTrace($request, 'driver.sendOtp');
        $phoneBeforeNormalize = $request->input('phone');
        $this->normalizePhoneRequest($request);
        Log::info('[otp-trace] phone after normalize', [
            'before' => $phoneBeforeNormalize,
            'after' => $request->input('phone'),
        ]);

        $validation = Validator::make($request->all(), [
            // Must be international format with or without +
            'phone' => ['required', 'string', 'regex:/^\+?[1-9][0-9]{6,14}$/'],
            // Captain type chosen on the first signup screen. Defaults to driver.
            'role' => ['nullable', 'in:driver,delivery'],
            // Required only when signing up as a delivery captain.
            'vehicle_type' => ['nullable', 'required_if:role,delivery', 'in:bike,motorcycle'],
        ], [
            'phone.regex' => 'Phone must be in international format (e.g. +9627XXXXXXXX or 9627XXXXXXXX)',
            'vehicle_type.required_if' => 'Please choose a vehicle type (bike or motorcycle) for delivery.',
        ]);

        if ($validation->fails()) {
            Log::warning('[otp-trace] validation failed', [
                'errors' => $validation->errors()->toArray(),
            ]);
            return response()->json([
                'message' => $validation->errors()->first(),
            ], 422);
        }

        $requestedRole = $request->input('role', 'driver');

        $otpCode = rand(100000, 999999);
        $defaultOtpLimit = OtpLimit::where('type', 'driver')->value('otp_limit');
        $phone = $request->phone;
        $rawPhone = ltrim($phone, '+');

        Log::info('[otp-trace] looking up driver', [
            'phone' => $phone,
            'raw_phone' => $rawPhone,
            'default_otp_limit' => $defaultOtpLimit,
        ]);

        // Robust lookup to handle transition to '+' prefix and avoid duplicate accounts
        $user = User::where('phone', $phone)
                    ->orWhere('phone', $rawPhone)
                    ->first();

        if ($user) {
            Log::info('[otp-trace] driver found', [
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

            // Prevent switching an existing captain between driver and delivery.
            if (
                $request->filled('role')
                && in_array($user->role, ['driver', 'delivery'], true)
                && $user->role !== $requestedRole
            ) {
                return response()->json([
                    'message' => 'This phone is already registered as a ' . $user->role . ' account.',
                ], 422);
            }
        } else {
            // Create new captain account (driver or delivery).
            $user = User::create([
                'phone'          => $phone,
                'role'           => $requestedRole,
                'otp_limit'      => $defaultOtpLimit ?? 5,
                'activity'       => 'in_progress',
                'phone_verified' => false,
            ]);
            Log::info('[otp-trace] captain created', [
                'user_id' => $user->id,
                'phone' => $user->phone,
                'role' => $user->role,
                'otp_limit' => $user->otp_limit,
            ]);

            // Persist the chosen vehicle type right away so later steps do not
            // depend on the client remembering it. Images are filled later.
            if ($requestedRole === 'delivery' && $request->filled('vehicle_type')) {
                RiderVehicle::firstOrCreate(
                    ['rider_id' => $user->id],
                    ['type' => $request->input('vehicle_type')]
                );
            }
        }

        if ($user->isDelivery() && $request->filled('vehicle_type') && !$user->riderVehicle) {
            RiderVehicle::firstOrCreate(
                ['rider_id' => $user->id],
                ['type' => $request->input('vehicle_type')]
            );
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
            'otp_limit'      => $userLimit,
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

        $exists = $user->wasRecentlyCreated ? false : true;

        Log::info('[otp-trace] sendOtp json response', [
            'isLogin' => $exists,
            'message' => $exists ? 'OTP sent for login' : 'OTP sent for signup',
        ]);

        $user->loadMissing('riderVehicle');

        return response()->json([
            'message' => $exists ? 'OTP sent for login' : 'OTP sent for signup',
            'isLogin' => $exists,
            'role' => $user->role,
            'vehicle_type' => $user->riderVehicle?->type?->value,
        ]);
    }

    /**
     * Verify OTP and confirm driver phone.
     */
    public function verifyOtp(Request $request)
    {
        $this->normalizePhoneRequest($request);

        $validation = Validator::make($request->all(), [
            'phone'    => 'required|string',
            'otp_code' => 'required|string',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => $validation->errors()->first(),
            ], 422);
        }

        $phone = $request->phone;
        $rawPhone = ltrim($phone, '+');
        $user = User::where('phone', $phone)
                    ->orWhere('phone', $rawPhone)
                    ->first();

        if (!$user) {
            return response()->json(['message' => 'Driver not found'], 404);
        }

        // Standardize format
        if ($user->phone !== $phone) {
            $user->update(['phone' => $phone]);
        }

        if ($user->otp_code !== $request->otp_code) {
            return response()->json(['message' => 'Invalid OTP code'], 422);
        }

        if (now()->isAfter($user->otp_expires_at)) {
            return response()->json(['message' => 'OTP has expired. Please request a new one.'], 422);
        }

        $user->update([
            'phone_verified' => true,
            'otp_code'       => null,
            'otp_expires_at' => null,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;
        $user->loadMissing('riderVehicle');

        return response()->json([
            'message' => 'Phone number verified successfully',
            'token'   => $token,
            'role'    => $user->role,
            'vehicle_type' => $user->riderVehicle?->type?->value,
        ]);
    }

    /**
     * Resend OTP via WhatsApp.
     */
    public function resendOtp(Request $request)
    {
        $this->startOtpTrace($request, 'driver.resendOtp');
        $phoneBeforeNormalize = $request->input('phone');
        $this->normalizePhoneRequest($request);
        Log::info('[otp-trace] phone after normalize', [
            'before' => $phoneBeforeNormalize,
            'after' => $request->input('phone'),
        ]);

        $validation = Validator::make($request->all(), [
            'phone' => 'required|string',
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

        Log::info('[otp-trace] looking up driver for resend', [
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

        $defaultOtpLimit = OtpLimit::where('type', 'driver')->value('otp_limit') ?? 5;
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
            'otp_limit'      => $userLimit,
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
            'phone' => 'nullable|string',
            'email' => 'nullable|email|unique:users,email',
        ]);
        $phone = $request->phone;
        $rawPhone = ltrim($phone, '+');
        $user = User::where('phone', $phone)
                    ->orWhere('phone', $rawPhone)
                    ->first();

        // Update format if needed
        if ($user && $user->phone !== $phone) {
            $user->update(['phone' => $phone]);
        }

        $code = rand(100000, 999999);
        if ($user) {
            $user->email_code    = $code;
            $user->email_verified = 'unverified';
            $user->role          = 'driver';
            $user->activity      = 'in_progress';
            $user->email         = $request->email;
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
            'phone' => 'nullable|string',
            'email' => 'required|email|exists:users,email',
            'code'  => 'required|integer',
        ]);
        if ($validation->fails()) {
            return response()->json([
                'message' => $validation->errors()->first(),
            ], 401);
        }
        $user = User::where('email', $request->email)->first();
        if ($user->email_code == $request->code) {
            $user->email_verified = 'verified';
            $user->email          = $request->email;
            $user->email_code     = null;
            $user->role           = 'driver';
            $user->activity       = 'in_progress';
            $user->save();

            return response()->json([
                'message' => 'Email verified successfully',
            ]);
        }

        return response()->json([
            'message' => 'Email verification code is incorrect',
        ], 401);
    }

    public function getActiveCities()
    {
        $cities = City::where('status', 'active')->get();
        return response()->json([
            'message' => 'Active cities retrieved successfully',
            'data' => $cities,
        ]);
    }

    public function Postname(Request $request)
    {
        $this->normalizePhoneRequest($request);

        $validation = Validator::make($request->all(), [
            'phone'     => 'required|string',
            'name'      => 'required|string|max:255',
            'gender'    => 'nullable|string|in:male,female',
            'fcm_token' => 'required|string',
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

        if ($user && $user->phone !== $phone) {
            $user->update(['phone' => $phone]);
        }

        if ($user) {
            app(SignupGiftService::class)->revokeWhenBecomingDriver($user);
            $user->refresh();

            $user->name      = $request->name;
            $user->role      = 'driver';
            $user->activity  = 'in_progress';
            $user->gender    = $request->gender ?? null;
            $user->fcm_token = $request->fcm_token;
            $user->wallet    = 0;
            $user->save();

            return response()->json([
                'message' => 'Name updated successfully',
            ]);
        }

        return response()->json([
            'message' => 'User not found',
        ], 401);
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
                app(SignupGiftService::class)->revokeWhenBecomingDriver($existingUser);
                $existingUser->refresh();
                $existingUser->update([
                    'email_code'     => $code,
                    'email_verified' => 'unverified',
                    'activity'       => 'in_progress',
                    'role'           => 'driver',
                ]);
                Mail::to($existingUser->email)->send(new EmailVerificationCode($code));

                return response()->json([
                    'message' => 'Verification code resent. Please check your email.',
                ]);
            } else {
                Mail::to($existingUser->email)->send(new EmailVerificationCode($code));
                app(SignupGiftService::class)->revokeWhenBecomingDriver($existingUser);
                $existingUser->refresh();
                $existingUser->update([
                    'email_code'     => $code,
                    'email_verified' => 'unverified',
                    'activity'       => 'in_progress',
                    'role'           => 'driver',
                ]);

                return response()->json([
                    'message' => "Email already verified. You can login but we will send to verify it's you",
                ]);
            }
        }

        $user = User::create([
            'email'          => $request->email,
            'role'           => 'driver',
            'email_code'     => $code,
            'email_verified' => 'unverified',
            'activity'       => 'in_progress',
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

        app(SignupGiftService::class)->revokeWhenBecomingDriver($user);
        $user->refresh();
        $user->update([
            'email_verified' => 'verified',
            'email_code'     => null,
            'activity'       => 'in_progress',
            'role'           => 'driver',
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

        $client  = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
        $payload = $client->verifyIdToken($request->id_token);

        if ($payload) {
            $email    = $payload['email'];
            $name     = $payload['name'];
            $id_token = $payload['sub'];

            $user = User::firstOrCreate([
                'email'    => $email,
                'name'     => $name,
                'id_token' => $id_token,
                'role'     => 'driver',
                'activity' => 'in_progress',
            ]);

            return response()->json([
                'message' => 'Google account registered successfully',
                'user'    => $user,
            ]);
        } else {
            return response()->json([
                'message' => 'Invalid Google ID token',
            ], 401);
        }
    }

    // ===================== Docs & Car =====================

    public function requiredDocs(Request $request)
    {
        $requiredDocs = DocumentType::where('is_required', 1)->get();

        return response()->json([
            'requiredDocs' => $requiredDocs,
        ]);
    }

    public function storeDriverDocs(Request $request)
    {
        $this->normalizePhoneRequest($request);

        $validation = Validator::make($request->all(), [
            'phone'        => 'required|string',
            'selfie_image' => 'required|string',
            'documents'    => 'required|array',
            'documents.*'  => 'required',
            'city_id'      => 'required|exists:cities,id',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => $validation->errors()->first(),
            ], 401);
        }

        $phone = $request->phone;
        $rawPhone = ltrim($phone, '+');
        $driver = User::where('phone', $phone)
                    ->orWhere('phone', $rawPhone)
                    ->first();

        if (!$driver) {
            return response()->json([
                'message' => 'The selected phone is invalid.',
            ], 401);
        }

        // Ensure consistency
        if ($driver->phone !== $phone) {
            $driver->update(['phone' => $phone]);
        }

        $documents    = $request->input('documents');
        $allDocs      = DocumentType::all();
        $requiredDocs = $allDocs->where('is_required', ActiveStatuses::Active);

        // 1. Verify all required documents are present
        foreach ($requiredDocs as $doc) {
            if (!isset($documents[$doc->id])) {
                return response()->json([
                    'message' => 'missing document ' . $doc->name,
                ], 401);
            }
        }

        // 2. Save all documents provided in the request
        foreach ($documents as $docTypeId => $base64Image) {
            $docType = $allDocs->find($docTypeId);
            if (!$docType) {
                continue; // Skip invalid document types
            }

            $path = $this->storeBase64Image($base64Image, 'driver/documents');
            if ($path === null) {
                return response()->json(['errors' => "Invalid base64 image string for document: {$docType->name}"], 400);
            }

            DriverDocument::create([
                'driver_id'        => $driver->id,
                'document_type_id' => $docType->id,
                'image_path'       => $path,
            ]);
        }

        $selfiePath = $this->storeBase64Image($request->selfie_image, 'driver/selfies');
        if ($selfiePath === null) {
            return response()->json(['errors' => 'Invalid base64 image string'], 400);
        }
        $driver->update([
            'image' => $selfiePath,
            'city_id' => $request->city_id,
        ]);

        return response()->json([
            'message' => 'Documents uploaded successfully',
        ]);
    }

    public function storeDriverCar(Request $request)
    {
        $this->normalizePhoneRequest($request);

        $validation = Validator::make($request->all(), [
            'phone'            => 'required|string',
            'car_type_id'      => 'required|exists:car_types,id',
            'car_category_ids' => 'required|array|min:1',
            'car_category_ids.*' => 'integer|exists:car_categories,id',
            'car_image'        => 'required|string',
            'car_color'        => 'required|string',
            'car_license'      => 'nullable|string',
            'car_number'       => 'required|string',
            'car_model_id'     => 'required|exists:car_models,id',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => $validation->errors()->first(),
            ], 400);
        }

        $phone = $request->phone;
        $rawPhone = ltrim($phone, '+');
        $driver = User::where('phone', $phone)
                    ->orWhere('phone', $rawPhone)
                    ->first();

        if (!$driver) {
            return response()->json([
                'message' => 'Driver not found',
            ], 404);
        }

        // Standardize format
        if ($driver->phone !== $phone) {
            $driver->update(['phone' => $phone]);
        }

        $carType              = CarType::with('carCategories')->find($request->car_type_id);
        $allowedCategoryIds   = $carType->carCategories->pluck('id')->toArray();
        $providedCategoryIds  = $request->input('car_category_ids');

        foreach ($providedCategoryIds as $categoryId) {
            if (!in_array($categoryId, $allowedCategoryIds, true)) {
                return response()->json([
                    'message' => 'Selected car type is not available for one or more provided categories.',
                ], 422);
            }
        }

        $carImagePath  = $this->storeBase64Image($request->car_image, 'driver/cars');
        if ($carImagePath === null) {
            return response()->json(['errors' => 'Invalid base64 image string'], 400);
        }
        $car_licensePath = $request->car_license ? $this->storeBase64Image($request->car_license, 'driver/car_licenses') : null;
        if ($request->car_license && $car_licensePath === null) {
            return response()->json(['errors' => 'Invalid base64 image string for car license'], 400);
        }

        $driverCar = DriverCar::create([
            'driver_id'   => $driver->id,
            'car_type_id' => $request->car_type_id,
            'car_image'   => $carImagePath,
            'car_color'   => $request->car_color,
            'car_license' => $car_licensePath ?? null,
            'car_number'  => $request->car_number,
            'car_model_id' => $request->car_model_id,
        ]);

        $driverCar->carCategories()->sync($providedCategoryIds);

        app(SignupGiftService::class)->revokeWhenBecomingDriver($driver);
        $driver->refresh();
        $driver->update([
            'activity' => 'active',
            'role'     => 'driver',
        ]);

        return response()->json([
            'message' => 'Waiting for admin approval, your car details have been submitted successfully',
        ]);
    }

    public function getModelTypeIds()
    {
        $carModels = CarModel::all();

        $carTypes = CarType::with('carCategories')->get()->map(function ($carType) {
            $years = [];
            if ($carType->year_from && $carType->year_to) {
                for ($year = $carType->year_from; $year <= $carType->year_to; $year++) {
                    $years[] = $year;
                }
            } elseif ($carType->year_from && !$carType->year_to) {
                $endYear = date('Y') + 10;
                for ($year = $carType->year_from; $year <= $endYear; $year++) {
                    $years[] = $year;
                }
            } elseif (!$carType->year_from && $carType->year_to) {
                $startYear = max(1980, $carType->year_to - 50);
                for ($year = $startYear; $year <= $carType->year_to; $year++) {
                    $years[] = $year;
                }
            }

            return [
                'id'             => $carType->id,
                'car_model_id'   => $carType->car_model_id,
                'type_name'      => $carType->type_name,
                'year_from'      => $carType->year_from,
                'year_to'        => $carType->year_to,
                'year_range'     => $carType->year_range,
                'years'          => $years,
                'description'    => $carType->description,
                'car_categories' => $carType->carCategories,
                'created_at'     => $carType->created_at,
                'updated_at'     => $carType->updated_at,
            ];
        });

        $carCategories = CarCategory::all();

        return response()->json([
            'carModels'     => $carModels,
            'carTypes'      => $carTypes,
            'carCategories' => $carCategories,
        ]);
    }

    // ===================== Delivery (Rider) Docs & Vehicle =====================

    /**
     * Return the hardcoded required document images for a delivery captain.
     * The vehicle type comes from the query (`vehicle_type`) or, when a
     * `phone` is provided, from the stored rider_vehicles row.
     */
    public function riderRequiredDocs(Request $request)
    {
        $type = $request->input('vehicle_type');

        if (!$type && $request->filled('phone')) {
            $this->normalizePhoneRequest($request);
            $phone = $request->phone;
            $rawPhone = ltrim($phone, '+');
            $rider = User::where('phone', $phone)->orWhere('phone', $rawPhone)->first();
            $type = $rider?->riderVehicle?->type?->value;
        }

        if (!in_array($type, VehicleType::values(), true)) {
            return response()->json([
                'message' => 'A valid vehicle_type (bike or motorcycle) is required.',
            ], 422);
        }

        return response()->json([
            'vehicle_type' => $type,
            'requiredDocs' => VehicleType::requiredDocs($type),
        ]);
    }

    /**
     * Store a delivery captain's vehicle images. The vehicle type was chosen
     * at signup (stored on rider_vehicles), so it is not sent again here.
     */
    public function storeRiderVehicle(Request $request)
    {
        $this->normalizePhoneRequest($request);

        $validation = Validator::make($request->all(), [
            'phone'       => 'required|string',
            'documents'   => 'required|array',
            'documents.*' => 'required|string',
            'city_id'     => 'nullable|exists:cities,id',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => $validation->errors()->first()], 422);
        }

        $phone = $request->phone;
        $rawPhone = ltrim($phone, '+');
        $rider = User::where('phone', $phone)->orWhere('phone', $rawPhone)->first();

        if (!$rider || !$rider->isDelivery()) {
            return response()->json(['message' => 'Delivery account not found.'], 404);
        }

        if ($rider->phone !== $phone) {
            $rider->update(['phone' => $phone]);
        }

        $vehicle = $rider->riderVehicle;
        if (!$vehicle) {
            return response()->json([
                'message' => 'No vehicle type on file. Please restart signup and choose bike or motorcycle.',
            ], 422);
        }

        $type = $vehicle->type->value;
        $required = VehicleType::requiredDocs($type);
        $documents = $request->input('documents');

        // 1) Verify all required images are present.
        foreach ($required as $doc) {
            if (empty($documents[$doc['key']])) {
                return response()->json(['message' => 'missing document ' . $doc['name']], 422);
            }
        }

        // 2) Store each required image.
        $update = [];
        foreach ($required as $doc) {
            $path = $this->storeBase64Image($documents[$doc['key']], 'rider/documents');
            if ($path === null) {
                return response()->json(['errors' => "Invalid base64 image string for: {$doc['name']}"], 400);
            }
            $update[$doc['key']] = $path;
        }

        $vehicle->update($update);

        // Selfie stored on the user profile mirrors the driver flow.
        if (!empty($update['rider_image'])) {
            $rider->image = $update['rider_image'];
        }
        if ($request->filled('city_id')) {
            $rider->city_id = $request->city_id;
        }

        app(SignupGiftService::class)->revokeWhenBecomingDriver($rider);
        $rider->activity = 'active';
        $rider->role = 'delivery';
        $rider->save();

        return response()->json([
            'message' => 'Waiting for admin approval, your vehicle details have been submitted successfully',
        ]);
    }

    public function login(Request $request)
    {
        $this->normalizePhoneRequest($request);

        $validation = Validator::make($request->all(), [
            'phone'    => 'nullable|string',
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
                $phone = $request->phone;
                $rawPhone = ltrim($phone, '+');
                $q->where('phone', $phone)
                  ->orWhere('phone', $rawPhone);
            }
            if ($request->filled('email')) {
                $q->orWhere('email', $request->email);
            }
        })->first();

        if ($user && $request->filled('phone') && $user->phone !== $request->phone) {
            $user->update(['phone' => $request->phone]);
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        $user->loadMissing('riderVehicle');

        $vehicleType = $user->isDelivery() ? $user->riderVehicle?->type?->value : null;

        return response()->json([
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => $user,
            'role'    => $user->role,
            'vehicle_type' => $vehicleType,
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
