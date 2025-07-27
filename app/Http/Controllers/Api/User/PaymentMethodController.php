<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\PaymenentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{


    public function getPaymentMethods(Request $request)
    {
        $paymentMethods = PaymenentMethod::where('status', 'active')->get();

        return response()->json([
            'data' => $paymentMethods
        ]);
    }
}
