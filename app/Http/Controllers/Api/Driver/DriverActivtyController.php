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
        $walletStatus = $driver->getWalletStatus();

        $data = [
            'id' => $driver->id,
            'name' => $driver->name,
            'email' => $driver->email,
            'phone' => $driver->phone,
            'activity' => $driver->activity,
            'wallet_status' => $walletStatus
        ];

        return response()->json(['driver' => $data]);
    }


    public function getDriverStatus(Request $request)
    {
        $driver = $request->user();

        $data =[
            'id'=> $driver->id,
            'name'=> $driver->name,
            'email'=> $driver->email,
            'phone'=> $driver->phone,
            'status'=> $driver->status,
        ];

        return response()->json(['driver'=> $data]);
    }


}
