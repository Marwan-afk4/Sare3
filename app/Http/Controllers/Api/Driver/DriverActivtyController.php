<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

use function PHPSTORM_META\map;

class DriverActivtyController extends Controller
{


    public function getDriverActivity(Request $request)
    {
        $driver = $request->user();

        $data = [
            'name' => $driver->name,
            'email' => $driver->email,
            'phone' => $driver->phone,
            'activity' => $driver->activity,
        ];

        return response()->json(['driver' => $data]);
    }
}
