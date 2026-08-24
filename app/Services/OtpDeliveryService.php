<?php

namespace App\Services;

use App\Enums\PhoneVerificationMethod;
use App\Jobs\SendWhatsappMessage;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Log;

class OtpDeliveryService
{
    public function __construct(protected KastanaSmsService $kastanaSms)
    {
    }

    public function send(string $phone, string $message): void
    {
        $channel = AppSetting::getStoredPhoneVerificationMethod();

        if ($channel === PhoneVerificationMethod::KastanaOtp->value) {
            $response = $this->kastanaSms->send($phone, $message);

            if (! $this->kastanaSms->succeeded($response)) {
                Log::error('Failed to send Kastana SMS', [
                    'phone_number' => $phone,
                    'status'       => $response->status(),
                    'body'         => $response->body(),
                ]);
            } else {
                Log::info('Kastana SMS sent successfully', [
                    'phone_number' => $phone,
                    'status'       => $response->status(),
                    'body'         => $response->body(),
                ]);
            }

            return;
        }

        SendWhatsappMessage::dispatchSync($phone, $message);
    }
}
