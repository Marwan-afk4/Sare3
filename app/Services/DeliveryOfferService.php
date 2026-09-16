<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\DeliveryOffer;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class DeliveryOfferService
{
    public function recordOffer(Delivery $delivery, int $riderId, ?int $attempt = null, ?string $note = null): ?DeliveryOffer
    {
        try {
            $existingPending = DeliveryOffer::where('delivery_id', $delivery->id)
                ->where('rider_id', $riderId)
                ->where('response', DeliveryOffer::RESPONSE_PENDING)
                ->latest('id')
                ->first();

            if ($existingPending) {
                return $existingPending;
            }

            if ($attempt === null) {
                $attempt = (int) DeliveryOffer::where('delivery_id', $delivery->id)
                    ->where('rider_id', $riderId)
                    ->count() + 1;
            }

            return DeliveryOffer::create([
                'delivery_id' => $delivery->id,
                'rider_id' => $riderId,
                'offered_at' => now(),
                'response' => DeliveryOffer::RESPONSE_PENDING,
                'attempt' => $attempt,
                'note' => $note,
            ]);
        } catch (\Throwable $e) {
            Log::warning("DeliveryOfferService::recordOffer failed for delivery {$delivery->id}, rider {$riderId}: " . $e->getMessage());
            return null;
        }
    }

    public function markAccepted(Delivery $delivery, int $riderId, ?string $note = null): void
    {
        $this->finalizeOffer($delivery, $riderId, DeliveryOffer::RESPONSE_ACCEPTED, $note);
    }

    public function markRejected(Delivery $delivery, int $riderId, ?string $note = null): void
    {
        $this->finalizeOffer($delivery, $riderId, DeliveryOffer::RESPONSE_REJECTED, $note);
    }

    public function markIgnored(Delivery $delivery, int $riderId, ?string $note = null): void
    {
        $this->finalizeOffer($delivery, $riderId, DeliveryOffer::RESPONSE_IGNORED, $note);
    }

    public function markCancelledAfterAccept(Delivery $delivery, int $riderId, ?string $note = null): void
    {
        $this->finalizeOffer($delivery, $riderId, DeliveryOffer::RESPONSE_CANCELLED_AFTER_ACCEPT, $note);
    }

    public function markAllPendingCancelledByUser(Delivery $delivery, ?string $note = null): void
    {
        $this->closeAllPending($delivery, DeliveryOffer::RESPONSE_CANCELLED_BY_USER, $note);
    }

    public function markAllPendingIgnored(Delivery $delivery, ?string $note = null): void
    {
        $this->closeAllPending($delivery, DeliveryOffer::RESPONSE_IGNORED, $note);
    }

    protected function closeAllPending(Delivery $delivery, string $response, ?string $note): void
    {
        try {
            DeliveryOffer::where('delivery_id', $delivery->id)
                ->where('response', DeliveryOffer::RESPONSE_PENDING)
                ->get()
                ->each(function (DeliveryOffer $offer) use ($delivery, $response, $note) {
                    $this->finalizeOffer($delivery, (int) $offer->rider_id, $response, $note);
                });
        } catch (\Throwable $e) {
            Log::warning("DeliveryOfferService::closeAllPending failed for delivery {$delivery->id}: " . $e->getMessage());
        }
    }

    protected function finalizeOffer(Delivery $delivery, int $riderId, string $response, ?string $note): void
    {
        try {
            $offer = DeliveryOffer::where('delivery_id', $delivery->id)
                ->where('rider_id', $riderId)
                ->where('response', DeliveryOffer::RESPONSE_PENDING)
                ->latest('id')
                ->first();

            $respondedAt = now();
            $location = $this->snapshotRiderLocation($riderId, $response);

            if (!$offer) {
                DeliveryOffer::create(array_merge([
                    'delivery_id' => $delivery->id,
                    'rider_id' => $riderId,
                    'offered_at' => $respondedAt,
                    'responded_at' => $respondedAt,
                    'response' => $response,
                    'response_seconds' => 0,
                    'attempt' => (int) DeliveryOffer::where('delivery_id', $delivery->id)
                        ->where('rider_id', $riderId)
                        ->count() + 1,
                    'note' => $note,
                ], $location));
                return;
            }

            $responseSeconds = $offer->offered_at
                ? max(0, $respondedAt->timestamp - $offer->offered_at->timestamp)
                : null;

            $offer->update(array_merge([
                'response' => $response,
                'responded_at' => $respondedAt,
                'response_seconds' => $responseSeconds,
                'note' => $note,
            ], $location));
        } catch (\Throwable $e) {
            Log::warning("DeliveryOfferService::finalizeOffer failed for delivery {$delivery->id}, rider {$riderId}, response {$response}: " . $e->getMessage());
        }
    }

    protected function snapshotRiderLocation(int $riderId, string $response): array
    {
        if ($response !== DeliveryOffer::RESPONSE_IGNORED) {
            return [];
        }

        $rider = User::find($riderId);
        if (!$rider || $rider->latitude === null || $rider->longitude === null) {
            return [];
        }

        return [
            'rider_lat' => (float) $rider->latitude,
            'rider_lng' => (float) $rider->longitude,
        ];
    }
}
