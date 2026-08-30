<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KastanaSmsService
{
    public function send(string $number, string $message): Response
    {
        $digits = preg_replace('/\D/', '', $number);
        $recipients = $digits === '' ? $number : '+'.$digits;
        $passwordFromConfig = (string) config('services.kastana.password');
        $password = $this->rawPassword($passwordFromConfig);
        // Kastana receives a timezone-less dateTime. UTC prevents the provider
        // from interpreting the application's local time as a future schedule.
        $sendingTime = now('UTC')->format('Y-m-d H:i:s');
        $query = [
            'UserName'    => config('services.kastana.username'),
            'Password'    => $password,
            'SenderID'    => config('services.kastana.sender_id'),
            'Body'        => $message,
            'Recipients'  => $recipients,
            'SendingTime' => $sendingTime,
            'Language'    => config('services.kastana.language', 'English'),
        ];
        $url = config('services.kastana.url');
        $safeQuery = $this->redactQuery($query);

        Log::info('[otp-trace] Kastana request prepared', [
            'raw_number' => $number,
            'digits' => $digits,
            'recipients' => $recipients,
            'url' => $url,
            'query' => $safeQuery,
            'built_url' => $this->redactUrl($url.'?'.http_build_query($query)),
            'rfc3986_url' => $this->redactUrl($url.'?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986)),
            'sending_time' => $sendingTime,
            'sending_time_timezone' => 'UTC',
            'application_timezone' => config('app.timezone'),
            'config' => [
                'username' => config('services.kastana.username'),
                'sender_id' => config('services.kastana.sender_id'),
                'language' => config('services.kastana.language'),
                'password_from_config' => $this->maskSecret($passwordFromConfig),
                'password_after_rawPassword' => $this->maskSecret($password),
                'password_length' => strlen($password),
                'password_has_plus' => str_contains($password, '+'),
                'password_has_percent' => str_contains($passwordFromConfig, '%'),
                'config_cached' => app()->configurationIsCached(),
                'env_username' => env('KASTANA_USERNAME'),
                'env_password' => $this->maskSecret(env('KASTANA_PASSWORD')),
                'env_sender_id' => env('KASTANA_SENDER_ID'),
                'env_url' => env('KASTANA_SMS_URL'),
            ],
        ]);

        $response = Http::accept('application/xml')
            ->beforeSending(function ($request) {
                Log::info('[otp-trace] Kastana HTTP outgoing', [
                    'method' => $request->method(),
                    'url' => $this->redactUrl((string) $request->url()),
                    'headers' => $request->headers(),
                    'body' => $request->body(),
                ]);
            })
            ->get($url, $query);

        Log::info('[otp-trace] Kastana HTTP returned', [
            'status' => $response->status(),
            'reason' => $response->reason(),
            'ok' => $response->ok(),
            'failed' => $response->failed(),
            'successful' => $response->successful(),
            'headers' => $response->headers(),
            'body' => $response->body(),
            'effective_uri' => $this->redactUrl((string) $response->effectiveUri()),
            'handler_stats' => $response->handlerStats(),
        ]);

        return $response;
    }

    public function succeeded(Response $response): bool
    {
        if ($response->failed()) {
            return false;
        }

        if (preg_match('/>(-?\d+)</', $response->body(), $matches)) {
            return (int) $matches[1] > 0;
        }

        return false;
    }

    /**
     * Kastana expects the raw password. If .env was copied from a Postman URL
     * (e.g. %2B instead of +), decode once so Laravel does not double-encode it.
     */
    private function rawPassword(mixed $password): string
    {
        $password = (string) $password;

        if (str_contains($password, '%')) {
            return rawurldecode($password);
        }

        return $password;
    }

    private function redactQuery(array $query): array
    {
        if (array_key_exists('Password', $query)) {
            $query['Password'] = $this->maskSecret($query['Password']);
        }

        return $query;
    }

    private function redactUrl(string $url): string
    {
        return (string) preg_replace(
            '/([?&]Password=)[^&]*/i',
            '$1[redacted]',
            $url
        );
    }

    private function maskSecret(mixed $secret): string
    {
        $secret = (string) $secret;

        if ($secret === '') {
            return '[empty]';
        }

        return str_repeat('*', max(4, strlen($secret)));
    }
}
