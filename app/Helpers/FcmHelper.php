<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Google\Auth\Credentials\ServiceAccountCredentials;

class FcmHelper
{
    public static function getAccessToken(): ?string
    {
        $credentialsPath = storage_path('firebase/sarea-adce3-5ed7f6334162.json');

        $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

        $creds = new ServiceAccountCredentials($scopes, $credentialsPath);
        $token = $creds->fetchAuthToken();

        return $token['access_token'] ?? null;
    }

    public static function sendPushNotification($fcmToken, $title, $body, $data = [])
    {
        $accessToken = self::getAccessToken();

        $payload = [
            'message' => [
                'token' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => array_merge([
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                ], $data ?? [])
            ] 
        ];

        $response = Http::withToken($accessToken)
        ->withoutVerifying() // Disable SSL verification (not secure for production)
        ->post("https://fcm.googleapis.com/v1/projects/sarea-adce3/messages:send", $payload);

        return $response->json();
    }
}
