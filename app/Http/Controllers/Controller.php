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
        if ($request->has('phone') && !empty($request->phone) && !str_starts_with($request->phone, '+')) {
            $request->merge(['phone' => '+' . ltrim($request->phone, '+')]);
        }
    }
}
