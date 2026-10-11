<?php

namespace App\Helpers;

use App\Services\GoogleApiUsageService;
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
            
            $credentialsPath = storage_path('firebase/sarea-adce3-bde9c32ebddd.json');
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

    /**
     * Only incoming offers wake the device. Status updates stay at normal priority.
     */
    private static function isOfferNotification(array $data): bool
    {
        $msgType = strtolower(trim((string) ($data['msg_type'] ?? '')));

        return in_array($msgType, ['ride_request', 'delivery_request'], true);
    }

    /**
     * FCM HTTP v1 rejects a data payload unless every value is a string.
     */
    private static function stringifyData(array $data): array
    {
        $stringData = [];

        foreach ($data as $key => $value) {
            if ($value === null) {
                continue;
            }

            $stringData[$key] = is_array($value)
                ? (string) json_encode($value)
                : (string) $value;
        }

        return $stringData;
    }

    public static function sendPushNotification($fcmToken, $title, $body, $data = [])
    {
        $startTime = microtime(true);
        $data = $data ?? [];
        $highPriority = self::isOfferNotification($data);

        try {
            $accessToken = self::getAccessToken();

            if (!$accessToken) {
                Log::error('No access token available for FCM');
                return ['error' => 'Failed to get access token'];
            }

            $dataPayload = self::stringifyData(array_merge([
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'title' => $title,
                'body' => $body,
            ], $data));

            $message = [
                'token' => $fcmToken,
                // Data-only on Android so the Flutter background handler still
                // runs the loud channel, overlay, and offer sheet.
                'data' => $dataPayload,
            ];

            if ($highPriority) {
                $message['android'] = [
                    'priority' => 'high',
                    'direct_boot_ok' => true,
                    'ttl' => '120s',
                ];
                $message['apns'] = [
                    'headers' => [
                        'apns-priority' => '10',
                        'apns-push-type' => 'alert',
                    ],
                    'payload' => [
                        'aps' => [
                            'alert' => [
                                'title' => (string) $title,
                                'body' => (string) $body,
                            ],
                            'sound' => 'default',
                            'interruption-level' => 'time-sensitive',
                            'relevance-score' => 1.0,
                        ],
                    ],
                ];
            }

            $payload = [
                'message' => $message,
            ];

            $response = Http::timeout(10) // 10 second timeout
                ->withToken($accessToken)
                ->withoutVerifying() // Disable SSL verification (not secure for production)
                ->post("https://fcm.googleapis.com/v1/projects/sarea-adce3/messages:send", $payload);

            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            $result = $response->json();
            
            if ($response->successful()) {
                GoogleApiUsageService::record('fcm', 1, 1);
                Log::info("✅ FCM notification sent successfully (took {$duration}ms)", [
                    'fcm_token' => substr($fcmToken, 0, 20) . '...',
                    'msg_type' => $data['msg_type'] ?? 'unknown',
                    'ride_id' => $data['ride_id'] ?? null,
                    'high_priority' => $highPriority,
                ]);
            } else {
                Log::error("❌ FCM notification failed (took {$duration}ms)", [
                    'fcm_token' => substr($fcmToken, 0, 20) . '...',
                    'status' => $response->status(),
                    'error' => $result,
                    'msg_type' => $data['msg_type'] ?? 'unknown',
                    'high_priority' => $highPriority,
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            Log::error("❌ Exception sending FCM notification (took {$duration}ms): " . $e->getMessage(), [
                'fcm_token' => substr($fcmToken, 0, 20) . '...',
                'msg_type' => $data['msg_type'] ?? 'unknown',
                'high_priority' => $highPriority,
            ]);
            return ['error' => $e->getMessage()];
        }
    }
}
