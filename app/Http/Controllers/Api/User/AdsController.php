<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\trait\ImageUpload;
use Illuminate\Http\Request;

class AdsController extends Controller
{
    public function getAds()
    {
        $ads = Ad::all();
        return response()->json(['ads'=>$ads]);
    }
}
