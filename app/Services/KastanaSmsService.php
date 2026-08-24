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
        $password = $this->rawPassword(config('services.kastana.password'));

        Log::info('Sending Kastana SMS', [
            'raw_number' => $number,
            'recipients' => $recipients,
            'url'        => config('services.kastana.url'),
        ]);

        return Http::accept('application/xml')
            ->get(config('services.kastana.url'), [
                'UserName'    => config('services.kastana.username'),
                'Password'    => $password,
                'SenderID'    => config('services.kastana.sender_id'),
                'Body'        => $message,
                'Recipients'  => $recipients,
                'SendingTime' => now()->format('Y-m-d H:i:s'),
                'Language'    => config('services.kastana.language', 'English'),
            ]);
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
}
