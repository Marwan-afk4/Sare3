<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class DriverNotificationController extends Controller
{


    public function getDriverNotificaions(Request $request)
    {
        $driverId = $request->user()->id;
        $notifications = Notification::where('type', 'driver')
            ->where(function ($query) use ($driverId) {
                $query->whereNull('driver_id') // للإشعارات العامة
                      ->orWhere('driver_id', $driverId); // للإشعارات الموجهة للسائق المحدد
            })
            ->get();

        return response()->json(['notifications'=>$notifications]);
    }
}
