<?php

namespace App\Http\Controllers\Api;

use App\Helpers\FcmHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{


    public function broadcastNotification(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'title' => 'required|string',
            'body' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'data' => 'nullable|array'
        ]);

        if ($validation->fails()) {
            return response()->json($validation->errors(), 400);
        }

        $tokens = User::where('id', $request->user_id)
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->pluck('fcm_token')
            ->toArray();

        if (empty($tokens)) {
            return response()->json(['error' => 'No FCM tokens found'], 404);
        }

        $responses = [];

        foreach ($tokens as $token) {
            $responses[] = [
                'token' => $token,
                'response' => FcmHelper::sendPushNotification(
                    $token, // ✅ Single token
                    $request->title,
                    $request->body,
                    $request->data
                )
            ];
        }

        return response()->json([
            'message' => 'Broadcast sent to ' . count($tokens) . ' users',
            'responses' => $responses,
        ]);
    }

}
