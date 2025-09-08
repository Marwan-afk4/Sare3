<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Models\CancellationReason;
use Illuminate\Http\Request;

class CancelationReasonController extends Controller
{


    public function getDriverCancelationReason()
    {
        $reasons = CancellationReason::where('type', 'driver')
            ->where('is_active', 1)
            ->get();

        return response()->json(['data' => $reasons]);
    }
}
