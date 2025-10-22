<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Models\WalletRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WalletRequestController extends Controller
{


    public function requestWallet(Request $request)
    {
        $driver = $request->user();

        $validation = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'type' => 'required|in:withdraw,deposit',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validation->errors()->first(),
            ], 422);
        }

        $walletRequest = WalletRequest::create([
            'driver_id' => $driver->id,
            'amount' => $request->amount,
            'type' => $request->type,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Go and pay in our places.',
            // 'data' => $walletRequest,
        ], 200);
    }

    public function getWalletRequests(Request $request)
    {
        $driver = $request->user();
        $walletRequests = WalletRequest::where('driver_id', $driver->id)
            ->with('driver:id,name,email') // Assuming you want to include driver details
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'History_Wallet_Requests' => $walletRequests,
        ], 200);
    }
}
