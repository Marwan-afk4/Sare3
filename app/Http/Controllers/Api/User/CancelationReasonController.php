<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\CancellationReason;
use Illuminate\Http\Request;

class CancelationReasonController extends Controller
{


    public function getUserCancelationReason()
    {
        $reasons = CancellationReason::where('type', 'user')
            ->where('is_active', 1)
            ->get();

        return response()->json(['data' => $reasons]);
    }
}
