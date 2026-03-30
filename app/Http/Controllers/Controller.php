<?php
 
 namespace App\Http\Controllers;
 
 use Illuminate\Http\Request;
 
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
 }
