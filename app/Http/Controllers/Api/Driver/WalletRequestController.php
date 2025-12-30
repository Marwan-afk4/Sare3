<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Models\WalletRequest;
use App\Models\WalletRequestsMessage;
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
            'message' => 'Wallet request created successfully.',
            'data' => $walletRequest,
        ], 200);
    }

    public function getWalletRequests(Request $request)
    {
        $driver = $request->user();
        $walletRequests = WalletRequest::where('driver_id', $driver->id)
            ->with(['driver.zone']) // Load driver with zone for timezone
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'History_Wallet_Requests' => $walletRequests,
        ], 200);
    }

    public function getWalletRequestMessages(Request $request, $id)
    {
        $driver = $request->user();

        $walletRequestsMessages = WalletRequestsMessage::where('wallet_request_id', $id)
            ->where('driver_id', $driver->id)
            ->with([
                'admin:id,name,email',
                'walletRequest:id,amount,type,status',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'Wallet_Requests_Messages' => $walletRequestsMessages,
        ], 200);
    }

    public function sendWalletRequestMessage(Request $request, $id)
    {
        $driver = $request->user();

        $validation = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validation->errors()->first(),
            ], 422);
        }

        $walletRequestMessage = WalletRequestsMessage::create([
            'wallet_request_id' => $id,
            'driver_id' => $driver->id,
            'driver_message' => $request->message,

        ]);

        return response()->json([
            'message' => 'Message sent successfully.',
            'data' => $walletRequestMessage,
        ], 200);
    }


}
