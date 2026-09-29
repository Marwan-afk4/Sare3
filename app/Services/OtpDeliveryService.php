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

        $usingKastana = $channel === PhoneVerificationMethod::KastanaOtp->value;

        $this->logDelivery($usingKastana, 'info', '[otp-trace] delivery started', [
            'phone' => $phone,
            'message' => $message,
            'channel' => $channel,
            'kastana_enum' => PhoneVerificationMethod::KastanaOtp->value,
            'will_use_kastana' => $usingKastana,
        ]);

        if ($usingKastana) {
            try {
                $response = $this->kastanaSms->send($phone, $message);
            } catch (\Throwable $e) {
                $this->logDelivery(true, 'error', '[otp-trace] Kastana HTTP client exception', [
                    'phone' => $phone,
                    'exception' => $e::class,
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }

            $succeeded = $this->kastanaSms->succeeded($response);

            $this->logDelivery(true, $succeeded ? 'info' : 'error', '[otp-trace] Kastana delivery result', [
                'phone' => $phone,
                'succeeded' => $succeeded,
                'http_failed' => $response->failed(),
                'status' => $response->status(),
                'reason' => $response->reason(),
                'headers' => $response->headers(),
                'body' => $response->body(),
                'effective_uri' => (string) $response->effectiveUri(),
                'handler_stats' => $response->handlerStats(),
            ]);

            return;
        }

        Log::info('[otp-trace] dispatching WhatsApp OTP', [
            'phone' => $phone,
            'message' => $message,
        ]);

        SendWhatsappMessage::dispatchSync($phone, $message);

        Log::info('[otp-trace] WhatsApp dispatch finished', [
            'phone' => $phone,
        ]);
    }

    private function logDelivery(bool $kastana, string $level, string $message, array $context = []): void
    {
        $logger = $kastana ? Log::channel('kastana') : Log::channel(config('logging.default'));

        $logger->log($level, $message, $context);
    }
}
