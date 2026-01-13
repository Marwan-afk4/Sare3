<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class UserNotificatonController extends Controller
{


    public function getNotificaions()
    {
        $notifications = Notification::where('type', 'user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['notifications'=>$notifications]);
    }
}
