<?php

namespace App\Http\Controllers\Api;

use App\Helpers\FcmHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{


    public function broadcastNotification(Request $request)
    {
        $startTime = microtime(true);
        
        $validation = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'data' => 'required|array',
            'data.title' => 'required|string',
            'data.body' => 'required|string',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->errors(), 400);
        }

        $driverId = $request->user_id;
        $msgType = $request->data['msg_type'] ?? 'unknown';
        $rideId = $request->data['ride_id'] ?? null;
        
        \Illuminate\Support\Facades\Log::info("📲 Notification API called", [
            'driver_id' => $driverId,
            'msg_type' => $msgType,
            'ride_id' => $rideId,
            'caller_user_id' => $request->user()->id ?? null,
        ]);

        $tokens = User::where('id', $driverId)
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->pluck('fcm_token')
            ->toArray();

        if (empty($tokens)) {
            \Illuminate\Support\Facades\Log::warning("No FCM token found for driver {$driverId}");
            return response()->json(['error' => 'No FCM tokens found'], 404);
        }

        $responses = [];

        foreach ($tokens as $token) {
            $responses[] = [
                'token' => substr($token, 0, 20) . '...', // Don't expose full token
                'response' => FcmHelper::sendPushNotification(
                    $token,
                    $request->data['title'], // ✅ العنوان من جوه data
                    $request->data['body'],  // ✅ الرسالة من جوه data
                    $request->data           // ✅ باقي البيانات
                )
            ];
        }

        $duration = round((microtime(true) - $startTime) * 1000, 2);
        
        \Illuminate\Support\Facades\Log::info("✅ Notification API completed in {$duration}ms", [
            'driver_id' => $driverId,
            'tokens_count' => count($tokens),
        ]);

        return response()->json([
            'message' => 'Broadcast sent to ' . count($tokens) . ' users',
            'responses' => $responses,
            'processing_time_ms' => $duration,
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

    /**
     * Send notifications to all users/drivers in a specific zone
     */
    public function broadcastNotificationToZone(Request $request)
    {
        $startTime = microtime(true);
        
        $validation = Validator::make($request->all(), [
            'zone_id' => 'required|exists:zones,id',
            'type' => 'nullable|in:user,driver,both',
            'data' => 'required|array',
            'data.title' => 'required|string',
            'data.body' => 'required|string',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->errors(), 400);
        }

        $zoneId = $request->zone_id;
        $type = $request->type ?? 'both';
        $msgType = $request->data['msg_type'] ?? 'zone_notification';
        
        // Verify zone exists
        $zone = Zone::find($zoneId);
        if (!$zone) {
            return response()->json(['error' => 'Zone not found'], 404);
        }

        \Illuminate\Support\Facades\Log::info("📲 Zone Notification API called", [
            'zone_id' => $zoneId,
            'zone_name' => $zone->name,
            'type' => $type,
            'msg_type' => $msgType,
            'caller_user_id' => $request->user()->id ?? null,
        ]);

        // Build query based on zone and type
        $query = User::where('zone_id', $zoneId)
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '');

        // Filter by user type
        if ($type === 'user') {
            $query->where(function($q) {
                $q->where('role', 'user')
                  ->orWhereNull('role');
            });
        } elseif ($type === 'driver') {
            $query->where('role', 'driver');
        }
        // If type is 'both', no additional role filter needed

        $tokens = $query->pluck('fcm_token')->unique()->values()->toArray();

        if (empty($tokens)) {
            \Illuminate\Support\Facades\Log::warning("No FCM tokens found for zone {$zoneId} with type {$type}");
            return response()->json([
                'error' => 'No FCM tokens found for the specified zone and type',
                'zone_id' => $zoneId,
                'zone_name' => $zone->name,
                'type' => $type,
                'users_count' => 0
            ], 404);
        }

        $responses = [];
        $successCount = 0;
        $failureCount = 0;

        foreach ($tokens as $token) {
            try {
                $response = FcmHelper::sendPushNotification(
                    $token,
                    $request->data['title'],
                    $request->data['body'],
                    $request->data
                );

                $responses[] = [
                    'token' => substr($token, 0, 20) . '...',
                    'response' => $response
                ];

                // Check if notification was successful
                if (isset($response['name']) && !isset($response['error'])) {
                    $successCount++;
                } else {
                    $failureCount++;
                }
            } catch (\Exception $e) {
                $failureCount++;
                $responses[] = [
                    'token' => substr($token, 0, 20) . '...',
                    'error' => $e->getMessage()
                ];
            }
        }

        $duration = round((microtime(true) - $startTime) * 1000, 2);
        
        \Illuminate\Support\Facades\Log::info("✅ Zone Notification API completed in {$duration}ms", [
            'zone_id' => $zoneId,
            'zone_name' => $zone->name,
            'type' => $type,
            'tokens_count' => count($tokens),
            'success_count' => $successCount,
            'failure_count' => $failureCount,
        ]);

        return response()->json([
            'message' => "Notification sent to {$successCount} users in zone '{$zone->name}'",
            'zone_id' => $zoneId,
            'zone_name' => $zone->name,
            'type' => $type,
            'total_recipients' => count($tokens),
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'responses' => $responses,
            'processing_time_ms' => $duration,
        ]);
    }

}
