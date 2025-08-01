<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Models\RideRequestTimeLimit;
use Illuminate\Http\Request;

class RideRequestLimitController extends Controller
{


    public function getRideRequestLimit()
    {
        $requestLimit = RideRequestTimeLimit::first();
        return response()->json(['data'=>$requestLimit]);
    }
}
