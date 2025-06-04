<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Mail\EmailVerificationCode;
use App\Models\DocumentType;
use App\Models\DriverDocument;
use App\Models\User;
use App\trait\ImageUpload;
use App\trait\twilio;
use Illuminate\Http\Request;
use Google_Client;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    use twilio ,
    ImageUpload;

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
                    $user->role = 'driver';
                    $user->activity = 'in_progress';
                    $user->save();
                } else {
                    return response()->json([
                        'message' => 'Email not found'
                    ], 404);
                }
            }

            if (!$user) {
                $user = User::firstOrCreate(['phone' => $request->phone,'role'=>'driver']);
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
            $user->role = 'driver';
            $user->activity = 'in_progress';
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
        $user = User::where('email', $request->email)->first();
        if ($user->email_code == $request->code) {
            $user->email_verified = 'verified';
            $user->email = $request->email;
            $user->email_code = null; // Clear the code after verification
            $user->role = 'driver';
            $user->activity = 'in_progress';
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
        if ($user) {
            $user->name = $request->name;
            $user->role = 'driver';
            $user->save();
            return response()->json([
                'message' => 'Name updated successfully'
            ]);
        }
        return response()->json([
            'message' => 'User not found'
        ], 401);
    }

    public function emailVerficationFirst(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'email' => 'nullable|email|unique:users,email'
        ]);
        if ($validation->fails()) {
            return response()->json([
                'message' => $validation->errors()->first()
            ], 401);
        }
        $excistUser = User::where('email', $request->email)->first();
        $code = rand(100000, 999999);

        if ($excistUser) {
        if ($excistUser->email_verified == 'unverified') {
            $excistUser->update([
                'email' => $request->email,
                'role' => 'driver',
                'email_code' => $code,
                'email_verified' => 'unverified',
                'activity' => 'in_progress'
            ]);

            Mail::to($excistUser->email)->send(new EmailVerificationCode($code));

            return response()->json([
                'message' => 'Go and check your email to verify your account , the code will expire after 5 min',
            ]);
        } elseif($excistUser->email_verified == 'verified') {
            return response()->json([
                'message' => 'This email is already registered',
            ], 401);
        }
    }

        $user = User::create([
            'email' => $request->email,
            'role' => 'driver',
            'email_code' => $code,
            'email_verified' => 'unverified',
            'activity' => 'in_progress'
        ]);

        Mail::to($user->email)->send(new EmailVerificationCode($code));

        return response()->json([
            'message' => 'Go and check your email to verify your account , the code will expire after 5 min',
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
            'activity' => 'in_progress',
        ]);

        return response()->json(['message' => 'Email verified successfully']);
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
                'role' => 'driver',
                'activity' => 'in_progress'
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

    public function requiredDocs(Request $request)
    {
        $requiredDocs = DocumentType::where('is_required', 1)->get();

        return response()->json([
            'requiredDocs' => $requiredDocs
        ]);
    }

    public function storeDriverDocs(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'phone' => 'required|string|exists:users,phone',
            'documents' => 'required|array',
            'documents.*' => 'required',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => $validation->errors()->first()
            ], 401);
        }

        $driver = User::where('phone', $request->phone)->first();

        if (!$driver) {
            return response()->json([
                'message' => 'User not found'
            ], 401);
        }

        $requiredDocs = DocumentType::where('is_required', 1)->get();

        $documents = $request->input('documents');

        foreach ($requiredDocs as $doc) {
            if (!isset($documents[$doc->id])) {
                return response()->json([
                    'message' => 'missing document ' . $doc->name
                ], 401);
            }

            $base64Image = $documents[$doc->id];
            $path = $this->storeBase64Image($base64Image, 'driver/documents');

            DriverDocument::create([
                'driver_id' => $driver->id,
                'document_type_id' => $doc->id,
                'image_path' => $path,
            ]);
        }

        return response()->json([
            'message' => 'Documents uploaded successfully'
        ]);

    }
}
