<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Google\Auth\Credentials\ServiceAccountCredentials;

class FcmHelper
{
    /**
     * Get cached access token or fetch a new one
     * Access tokens are valid for 1 hour, so we cache them for 55 minutes
     */
    public static function getAccessToken(): ?string
    {
        $cacheKey = 'fcm_access_token';
        
        // Try to get cached token
        $cachedToken = Cache::get($cacheKey);
        if ($cachedToken) {
            Log::debug('Using cached FCM access token');
            return $cachedToken;
        }

        // Fetch new token if not cached
        try {
            $startTime = microtime(true);
            
            $credentialsPath = storage_path('firebase/sarea-adce3-5ed7f6334162.json');
            $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

            $creds = new ServiceAccountCredentials($scopes, $credentialsPath);
            $token = $creds->fetchAuthToken();

            $accessToken = $token['access_token'] ?? null;
            
            if ($accessToken) {
                // Cache for 55 minutes (tokens are valid for 1 hour)
                Cache::put($cacheKey, $accessToken, now()->addMinutes(55));
                
                $duration = round((microtime(true) - $startTime) * 1000, 2);
                Log::info("Fetched new FCM access token (took {$duration}ms)");
            }

            return $accessToken;
        } catch (\Exception $e) {
            Log::error('Failed to get FCM access token: ' . $e->getMessage());
            return null;
        }
    }

    public static function sendPushNotification($fcmToken, $title, $body, $data = [])
    {
        $startTime = microtime(true);
        
        try {
            $accessToken = self::getAccessToken();

            if (!$accessToken) {
                Log::error('No access token available for FCM');
                return ['error' => 'Failed to get access token'];
            }

            $payload = [
                'message' => [
                    'token' => $fcmToken,
                    // 'notification' => [
                    //     'title' => $title,
                    //     'body' => $body,
                    // ],
                    'data' => array_merge([
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'title' => $title,
                        'body' => $body,
                    ], $data ?? [])
                ] 
            ];

            $response = Http::timeout(10) // 10 second timeout
                ->withToken($accessToken)
                ->withoutVerifying() // Disable SSL verification (not secure for production)
                ->post("https://fcm.googleapis.com/v1/projects/sarea-adce3/messages:send", $payload);

            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            $result = $response->json();
            
            if ($response->successful()) {
                Log::info("✅ FCM notification sent successfully (took {$duration}ms)", [
                    'fcm_token' => substr($fcmToken, 0, 20) . '...',
                    'msg_type' => $data['msg_type'] ?? 'unknown',
                    'ride_id' => $data['ride_id'] ?? null,
                ]);
            } else {
                Log::error("❌ FCM notification failed (took {$duration}ms)", [
                    'fcm_token' => substr($fcmToken, 0, 20) . '...',
                    'status' => $response->status(),
                    'error' => $result,
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            Log::error("❌ Exception sending FCM notification (took {$duration}ms): " . $e->getMessage(), [
                'fcm_token' => substr($fcmToken, 0, 20) . '...',
            ]);
            return ['error' => $e->getMessage()];
        }
    }
}
