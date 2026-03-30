<?php

namespace App\Jobs;

use App\Services\DaroryWhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsappMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $phoneNumber;
    protected $message;

    /**
     * Create a new job instance.
     */
    public function __construct(string $phoneNumber, string $message)
    {
        $this->phoneNumber = $phoneNumber;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(DaroryWhatsappService $whatsappService)
    {
        $response = $whatsappService->sendMessage($this->phoneNumber, $this->message);

        if ($response->failed()) {
            Log::error('Failed to send WhatsApp message', [
                'phone_number' => $this->phoneNumber,
                'status'       => $response->status(),
                'body'         => $response->body(),
            ]);
        } else {
            Log::info('WhatsApp message sent successfully', [
                'phone_number' => $this->phoneNumber,
                'status'       => $response->status(),
                'body'         => $response->body(),
            ]);
        }
    }
}
