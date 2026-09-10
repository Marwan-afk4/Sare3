<?php

namespace App\Services;

use App\Models\Ride;
use App\Models\RideOffer;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class RideOfferService
{
    /**
     * Record that a ride has just been offered to a driver. If there's
     * already a pending offer for the same (ride, driver) pair we leave it
     * alone to avoid duplicates caused by retries.
     */
    public function recordOffer(Ride $ride, int $driverId, ?int $attempt = null, ?string $note = null): ?RideOffer
    {
        try {
            $existingPending = RideOffer::where('ride_id', $ride->id)
                ->where('driver_id', $driverId)
                ->where('response', RideOffer::RESPONSE_PENDING)
                ->latest('id')
                ->first();

            $location = $this->snapshotDriverLocation($driverId);

            if ($existingPending) {
                if (! $existingPending->hasDriverLocation() && $location) {
                    $existingPending->update($location);
                }

                return $existingPending->fresh();
            }

            if ($attempt === null) {
                $attempt = (int) RideOffer::where('ride_id', $ride->id)
                    ->where('driver_id', $driverId)
                    ->count() + 1;
            }

            return RideOffer::create(array_merge([
                'ride_id' => $ride->id,
                'driver_id' => $driverId,
                'offered_at' => now(),
                'response' => RideOffer::RESPONSE_PENDING,
                'attempt' => $attempt,
                'note' => $note,
            ], $location));
        } catch (\Throwable $e) {
            Log::warning("RideOfferService::recordOffer failed for ride {$ride->id}, driver {$driverId}: " . $e->getMessage());
            return null;
        }
    }

    public function markAccepted(Ride $ride, int $driverId, ?string $note = null): void
    {
        $this->finalizeOffer($ride, $driverId, RideOffer::RESPONSE_ACCEPTED, $note);
    }

    public function markRejected(Ride $ride, int $driverId, ?string $note = null): void
    {
        $this->finalizeOffer($ride, $driverId, RideOffer::RESPONSE_REJECTED, $note);
    }

    public function markIgnored(Ride $ride, int $driverId, ?string $note = null): void
    {
        $this->finalizeOffer($ride, $driverId, RideOffer::RESPONSE_IGNORED, $note);
    }

    public function markCancelledAfterAccept(Ride $ride, int $driverId, ?string $note = null): void
    {
        $this->finalizeOffer($ride, $driverId, RideOffer::RESPONSE_CANCELLED_AFTER_ACCEPT, $note);
    }

    /**
     * Called when the passenger cancels while a driver still has the request
     * on screen. All currently-pending offers on the ride are flipped to
     * "cancelled_by_user" so the admin can see why the captains never got
     * to respond.
     */
    public function markAllPendingCancelledByUser(Ride $ride, ?string $note = null): void
    {
        try {
            RideOffer::where('ride_id', $ride->id)
                ->where('response', RideOffer::RESPONSE_PENDING)
                ->get()
                ->each(function (RideOffer $offer) use ($note) {
                    $respondedAt = now();
                    $responseSeconds = $offer->offered_at
                        ? max(0, $respondedAt->timestamp - $offer->offered_at->timestamp)
                        : null;

                    $offer->update([
                        'response' => RideOffer::RESPONSE_CANCELLED_BY_USER,
                        'responded_at' => $respondedAt,
                        'response_seconds' => $responseSeconds,
                        'note' => $note,
                    ]);
                });
        } catch (\Throwable $e) {
            Log::warning("RideOfferService::markAllPendingCancelledByUser failed for ride {$ride->id}: " . $e->getMessage());
        }
    }

    /**
     * Shared helper: find the most recent pending offer for this (ride,
     * driver) pair and close it with the given response. If no pending
     * offer exists, create one retroactively so the audit trail is
     * complete even for rides that pre-date this feature.
     */
    protected function finalizeOffer(Ride $ride, int $driverId, string $response, ?string $note): void
    {
        try {
            $offer = RideOffer::where('ride_id', $ride->id)
                ->where('driver_id', $driverId)
                ->where('response', RideOffer::RESPONSE_PENDING)
                ->latest('id')
                ->first();

            $respondedAt = now();

            $location = $this->snapshotDriverLocation($driverId, $response);

            if (!$offer) {
                RideOffer::create(array_merge([
                    'ride_id' => $ride->id,
                    'driver_id' => $driverId,
                    'offered_at' => $respondedAt,
                    'responded_at' => $respondedAt,
                    'response' => $response,
                    'response_seconds' => 0,
                    'attempt' => (int) RideOffer::where('ride_id', $ride->id)
                        ->where('driver_id', $driverId)
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
            Log::warning("RideOfferService::finalizeOffer failed for ride {$ride->id}, driver {$driverId}, response {$response}: " . $e->getMessage());
        }
    }

    /**
     * Captain GPS: Redis live ping first, then users.latitude.
     * When $response is set, only ignored/rejected offers get a snapshot.
     * When omitted (recordOffer), always try so the pin exists even if ignore
     * happens after the Redis TTL expires.
     */
    protected function snapshotDriverLocation(int $driverId, ?string $response = null): array
    {
        if ($response !== null && ! in_array($response, [
            RideOffer::RESPONSE_IGNORED,
            RideOffer::RESPONSE_REJECTED,
        ], true)) {
            return [];
        }

        $driver = User::find($driverId);
        $location = $driver?->latestKnownLocation();

        if ($location === null) {
            Log::info("RideOfferService: no GPS for driver {$driverId} while snapshotting".($response ? " ({$response})" : ' (offer)'));

            return [];
        }

        return [
            'driver_lat' => $location['lat'],
            'driver_lng' => $location['lng'],
        ];
    }
}
