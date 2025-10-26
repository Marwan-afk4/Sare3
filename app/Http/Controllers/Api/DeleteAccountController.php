<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DeleteAccountController extends Controller
{


    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        // Perform account deletion logic here
        $user->delete();

        return response()->json([
            'message' => 'Account deleted successfully.',
        ], 200);
    }
}
