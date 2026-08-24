<?php
 
 namespace App\Http\Controllers;
 
 use Illuminate\Http\Request;
 use Illuminate\Support\Facades\Log;
 use Illuminate\Support\Str;
 
 abstract class Controller
 {
     /**
      * Helper to automatically prepend '+' to phone numbers in the request
      * to ensure consistent database saving and API validation matching.
      */
     protected function normalizePhoneRequest(Request $request)
     {
         if ($request->has('phone') && !empty($request->phone)) {
             $phone = $request->phone;
             // Prepend + if it's missing, ensuring we don't duplicate it
             if (!str_starts_with($phone, '+')) {
                 $phone = '+' . ltrim($phone, '+');
             }
             $request->merge(['phone' => $phone]);
         }
     }

     protected function startOtpTrace(Request $request, string $action): void
     {
         Log::withContext([
             'otp_trace_id' => (string) Str::uuid(),
             'otp_action' => $action,
             'otp_path' => $request->path(),
             'otp_ip' => $request->ip(),
         ]);

         Log::info('[otp-trace] incoming request', [
             'phone_input' => $request->input('phone'),
             'all_input' => $request->all(),
             'query' => $request->query(),
             'headers' => $request->headers->all(),
             'user_agent' => $request->userAgent(),
         ]);
     }
 }
