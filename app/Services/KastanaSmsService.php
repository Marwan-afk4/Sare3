<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KastanaSmsService
{
    public function send(string $number, string $message)
    {
        $recipients = preg_replace('/\D/', '', $number);

        Log::info('Sending Kastana SMS', [
            'raw_number' => $number,
            'recipients' => $recipients,
            'url'        => config('services.kastana.url'),
        ]);

        return Http::get(config('services.kastana.url'), [
            'UserName'    => config('services.kastana.username'),
            'Password'    => config('services.kastana.password'),
            'SenderID'    => config('services.kastana.sender_id'),
            'Body'        => $message,
            'Recipients'  => $recipients,
            'SendingTime' => now()->format('Y-m-d H:i:s'),
            'Language'    => config('services.kastana.language', 'English'),
        ]);
    }
}
