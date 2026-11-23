<?php

namespace App\Helpers;

use App\Models\PushNotificationLog;
use App\Models\User;
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
     * Offers and a passenger cancel after accept must wake the device so the
     * driver app can play a ringtone while it is backgrounded.
     */
    private static function isWakeNotification(array $data): bool
    {
        return self::isOfferNotification($data) || self::isPassengerCancelNotification($data);
    }

    private static function isPassengerCancelNotification(array $data): bool
    {
        $msgType = strtolower(trim((string) ($data['msg_type'] ?? '')));

        return $msgType === 'ride_cancelled_by_user';
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

    /**
     * Record a push that never left the server, usually because the account has no token.
     */
    public static function recordSkipped($title, $body, $data = [], array $context = [], string $reason = 'Not sent'): void
    {
        self::writeLog($title, $body, $data ?? [], $context, [
            'status' => 'skipped',
            'has_token' => false,
            'error_message' => $reason,
            'high_priority' => self::isWakeNotification($data ?? []),
        ]);
    }

    public static function sendPushNotification($fcmToken, $title, $body, $data = [], array $context = [])
    {
        $startTime = microtime(true);
        $data = $data ?? [];
        $highPriority = self::isWakeNotification($data);

        if (!$fcmToken) {
            self::recordSkipped($title, $body, $data, $context, 'No FCM token saved for this account');
            return ['error' => 'No FCM token'];
        }

        try {
            $accessToken = self::getAccessToken();

            if (!$accessToken) {
                Log::error('No access token available for FCM');
                self::writeLog($title, $body, $data, $context, [
                    'status' => 'failed',
                    'has_token' => true,
                    'token_prefix' => substr((string) $fcmToken, 0, 20),
                    'error_message' => 'Failed to get access token',
                    'high_priority' => $highPriority,
                    'duration_ms' => (int) round((microtime(true) - $startTime) * 1000),
                ], $fcmToken);
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
                            'sound' => self::isPassengerCancelNotification($data)
                                ? 'cancel_ringtone.wav'
                                : 'default',
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
            
            [$errorCode, $errorMessage] = self::extractFcmError($result);
            $logAttributes = [
                'has_token' => true,
                'token_prefix' => substr((string) $fcmToken, 0, 20),
                'http_status' => $response->status(),
                'high_priority' => $highPriority,
                'duration_ms' => (int) round($duration),
            ];

            if ($response->successful()) {
                GoogleApiUsageService::record('fcm', 1, 1);
                $logAttributes['status'] = 'accepted';
                $logAttributes['fcm_message_id'] = is_array($result) ? ($result['name'] ?? null) : null;
                Log::info("✅ FCM notification sent successfully (took {$duration}ms)", [
                    'fcm_token' => substr($fcmToken, 0, 20) . '...',
                    'msg_type' => $data['msg_type'] ?? 'unknown',
                    'ride_id' => $data['ride_id'] ?? null,
                    'high_priority' => $highPriority,
                ]);
            } else {
                $logAttributes['status'] = 'failed';
                $logAttributes['error_code'] = $errorCode;
                $logAttributes['error_message'] = $errorMessage;
                Log::error("❌ FCM notification failed (took {$duration}ms)", [
                    'fcm_token' => substr($fcmToken, 0, 20) . '...',
                    'status' => $response->status(),
                    'error' => $result,
                    'msg_type' => $data['msg_type'] ?? 'unknown',
                    'high_priority' => $highPriority,
                ]);
            }

            self::writeLog($title, $body, $data, $context, $logAttributes, $fcmToken);

            return $result;
        } catch (\Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            Log::error("❌ Exception sending FCM notification (took {$duration}ms): " . $e->getMessage(), [
                'fcm_token' => substr($fcmToken, 0, 20) . '...',
                'msg_type' => $data['msg_type'] ?? 'unknown',
                'high_priority' => $highPriority,
            ]);
            self::writeLog($title, $body, $data, $context, [
                'status' => 'exception',
                'has_token' => true,
                'token_prefix' => substr((string) $fcmToken, 0, 20),
                'error_message' => $e->getMessage(),
                'high_priority' => $highPriority,
                'duration_ms' => (int) round($duration),
            ], $fcmToken);
            return ['error' => $e->getMessage()];
        }
    }

    private static function writeLog($title, $body, array $data, array $context, array $attributes, $fcmToken = null): void
    {
        try {
            $msgType = isset($data['msg_type']) ? (string) $data['msg_type'] : null;
            $isDelivery = $msgType === 'delivery_request';
            [$recipientId, $recipientRole] = self::resolveRecipient($fcmToken, $context);

            PushNotificationLog::create(array_merge([
                'recipient_id' => $recipientId,
                'recipient_role' => $recipientRole,
                'ride_id' => $isDelivery
                    ? self::nullableInt($context['ride_id'] ?? null)
                    : self::nullableInt($context['ride_id'] ?? ($data['ride_id'] ?? null)),
                'delivery_id' => $isDelivery
                    ? self::nullableInt($context['delivery_id'] ?? ($data['delivery_id'] ?? null))
                    : self::nullableInt($context['delivery_id'] ?? null),
                'msg_type' => $msgType ? mb_substr($msgType, 0, 64) : null,
                'title' => self::clip((string) $title, 255),
                'body' => self::clip((string) $body, 2000),
            ], $attributes, [
                'error_code' => isset($attributes['error_code']) ? self::clip((string) $attributes['error_code'], 255) : null,
                'error_message' => isset($attributes['error_message']) ? self::clip((string) $attributes['error_message'], 2000) : null,
                'fcm_message_id' => isset($attributes['fcm_message_id']) ? self::clip((string) $attributes['fcm_message_id'], 255) : null,
            ]));
        } catch (\Throwable $e) {
            Log::error('Failed to write push notification log: ' . $e->getMessage());
        }
    }

    private static function resolveRecipient($fcmToken, array $context): array
    {
        $recipientId = self::nullableInt($context['recipient_id'] ?? null);
        $recipientRole = self::roleString($context['recipient_role'] ?? null);

        if (!$recipientId && $fcmToken) {
            $user = User::query()->where('fcm_token', $fcmToken)->first(['id', 'role']);
            if ($user) {
                $recipientId = $user->id;
                $recipientRole = $recipientRole ?: self::roleString($user->role);
            }
        }

        return [$recipientId, $recipientRole ? mb_substr($recipientRole, 0, 20) : null];
    }

    private static function extractFcmError($result): array
    {
        if (!is_array($result)) {
            return [null, is_scalar($result) ? (string) $result : null];
        }

        if (isset($result['error']) && is_string($result['error'])) {
            return [null, $result['error']];
        }

        $error = $result['error'] ?? null;
        if (!is_array($error)) {
            return [null, null];
        }

        $code = null;
        foreach ($error['details'] ?? [] as $detail) {
            if (is_array($detail) && !empty($detail['errorCode'])) {
                $code = (string) $detail['errorCode'];
                break;
            }
        }

        return [
            $code ?: (isset($error['status']) ? (string) $error['status'] : null),
            isset($error['message']) ? (string) $error['message'] : null,
        ];
    }

    private static function nullableInt($value): ?int
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    private static function roleString($role): ?string
    {
        if ($role === null) {
            return null;
        }

        if (is_object($role) && isset($role->value)) {
            return (string) $role->value;
        }

        return is_scalar($role) ? (string) $role : null;
    }

    private static function clip(string $value, int $limit): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        return mb_substr($value, 0, $limit);
    }
}
