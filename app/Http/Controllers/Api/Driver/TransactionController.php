<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Controller;
use App\Models\Ride;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{


    public function transfareToUserWallet(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'ride_id' => 'required|exists:rides,id',
            'amount' => 'required|numeric|min:1',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => $validation->errors()->first(),
            ], 400);
        }

        // نجيب الرحلة الخاصة بالسواق الحالي
        $ride = Ride::where('id', $request->ride_id)
            ->where('driver_id', auth()->id())
            ->where('status', 'completed')
            ->first();

        if (!$ride) {
            return response()->json([
                'message' => 'Ride not found or not completed',
            ], 404);
        }

        DB::transaction(function () use ($ride, $request) {
            $ride->user->wallet += $request->amount;
            $ride->user->save();

            // إنشاء transaction
            $ride->user->userTransactions()->create([
                'user_id'     => $ride->user->id,
                'driver_id'   => $ride->driver->id,
                'amount'      => $request->amount,
                'description' => 'Driver paid remaining balance',
            ]);

            // Send notification to user about wallet update
            if ($ride->user->fcm_token) {
                try {
                    $notificationController = new NotificationController();
                    $notificationRequest = new Request([
                        'user_id' => $ride->user->id,
                        'data' => [
                            'title' => 'Wallet Updated',
                            'body' => "You received {$request->amount} in your wallet. New balance: {$ride->user->wallet}",
                            'type' => 'wallet_transfer',
                            'amount' => $request->amount,
                            'new_balance' => $ride->user->wallet,
                            'description' => 'Driver paid remaining balance',
                            'ride_id' => $ride->id,
                        ]
                    ]);
                    Log::info('Sending wallet notification');
                    $notificationController->broadcastNotification($notificationRequest);
                } catch (\Exception $e) {
                    // Log the error but don't fail the transaction
                    Log::error('Failed to send wallet notification: ' . $e->getMessage(), [
                        'user_id' => $ride->user->id,
                        'ride_id' => $ride->id,
                        'amount' => $request->amount
                    ]);
                }
            }
            else {
                Log::info('No FCM token for user, skipping wallet notification', [
                    'user_id' => $ride->user->id,
                    'ride_id' => $ride->id,
                ]);
            }
        });

        return response()->json([
            'message' => 'Amount transferred to user wallet successfully',
            'user_wallet' => $ride->user->wallet,
        ], 200);
    }

}
