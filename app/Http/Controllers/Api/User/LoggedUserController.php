<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoggedUserController extends Controller
{


    public function getLoggedUser(Request $request)
    {
        $user = $request->user();

        $data = [
            'user_data' => $user,
        ];

        return response()->json($data);
    }
}
