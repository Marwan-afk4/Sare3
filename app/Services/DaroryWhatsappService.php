<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DaroryWhatsappService
{
    protected string $baseUrl;

    protected string $apiToken;

    protected string $instanceId;

    public function __construct()
    {
        $this->baseUrl    = env('WA_DARORY');
        $this->apiToken   = env('WA_DARORY_API_TOKEN');
        $this->instanceId = env('WA_DARORY_INSTANCE_ID');
    }

    /**
     * Normalize number: strip +, spaces, dashes — leave digits only.
     * Expects the caller to pass a number already in international format
     * (e.g. 9627XXXXXXXX, 201XXXXXXXXX, 9641XXXXXXXXX).
     */
    protected function normalizeNumber(string $number): string
    {
        return preg_replace('/\D/', '', $number);
    }

    /**
     * Send WhatsApp message via Darory API
     *
     * @return \Illuminate\Http\Client\Response
     */
    public function sendMessage(string $number, string $message)
    {
        $formatted = $this->normalizeNumber($number) . '@c.us';

        Log::info('Sending WhatsApp message', [
            'raw_number'       => $number,
            'formatted_number' => $formatted,
            'url'              => rtrim($this->baseUrl, '/') . '/send-message',
        ]);

        $response = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $this->apiToken,
            'X-INSTANCE-ID' => $this->instanceId,
        ])->post(rtrim($this->baseUrl, '/') . '/send-message', [
            'number'  => $formatted,
            'message' => $message,
        ]);

        return $response;
    }
}
