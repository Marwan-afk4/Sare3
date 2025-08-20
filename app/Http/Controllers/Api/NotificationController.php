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
            'user_id' => 'required|exists:users,id',
            'data' => 'required|array',
            'data.title' => 'required|string',
            'data.body' => 'required|string',
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
                    $token,
                    $request->data['title'], // ✅ العنوان من جوه data
                    $request->data['body'],  // ✅ الرسالة من جوه data
                    $request->data           // ✅ باقي البيانات
                )
            ];
        }

        return response()->json([
            'message' => 'Broadcast sent to ' . count($tokens) . ' users',
            'responses' => $responses,
        ]);
    }

    public function fcmTOken(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'fcm_token' => 'required|string',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->errors(), 400);
        }

        $user = $request->user();
        $user->fcm_token = $request->fcm_token;
        $user->save();

        return response()->json(['message' => 'FCM token updated successfully']);
    }

}
