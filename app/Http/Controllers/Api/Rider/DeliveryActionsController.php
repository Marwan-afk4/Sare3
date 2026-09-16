<?php

namespace App\Http\Controllers\Api\Rider;

use App\Http\Controllers\Api\User\DeliveryController as UserDeliveryController;
use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Delivery;
use App\Models\DeliveryZonePrice;
use App\Models\WalletRequest;
use App\Models\Zone;
use App\Services\DeliveryOfferService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DeliveryActionsController extends Controller
{
    protected function validateDelivery(Request $request, $status = null, bool $requireRiderMatch = true): ?Delivery
    {
        $validator = Validator::make($request->all(), [
            'delivery_id' => 'required|exists:deliveries,id',
        ]);

        if ($validator->fails()) {
            abort(response()->json(['message' => $validator->errors()->first()], 422));
        }

        $query = Delivery::where('id', $request->delivery_id);

        if ($requireRiderMatch) {
            $query->where('rider_id', $request->user()->id);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $delivery = $query->first();

        if (!$delivery) {
            abort(response()->json(['message' => 'Delivery not found or invalid status.'], 404));
        }

        return $delivery;
    }

    /**
     * Rider accepts the delivery offer.
     */
    public function accept(Request $request)
    {
        $rider = $request->user();

        if (!$rider->canGoOnline()) {
            return response()->json([
                'message' => 'Insufficient wallet balance to take deliveries.',
                'wallet_status' => $rider->getWalletStatus(),
            ], 403);
        }

        $delivery = Delivery::where('id', $request->delivery_id)
            ->whereIn('status', ['pending', 'rejected'])
            ->first();

        if (!$delivery) {
            return response()->json(['message' => 'Delivery not found or not available for acceptance.'], 404);
        }

        $acceptLat = $request->input('lat') ?? $rider->latitude;
        $acceptLng = $request->input('lng') ?? $rider->longitude;

        $updateData = [
            'rider_id' => $rider->id,
            'status' => 'accepted',
            'accepted_at' => now(),
            'started_at' => now(),
        ];

        if ($acceptLat !== null && $acceptLng !== null) {
            $updateData['rider_accept_lat'] = (float) $acceptLat;
            $updateData['rider_accept_lng'] = (float) $acceptLng;
            $updateData['to_pickup_route_points'] = [[
                'lat'       => (float) $acceptLat,
                'lng'       => (float) $acceptLng,
                'bearing'   => (float) ($rider->bearing ?? 0),
                'timestamp' => now()->timestamp,
                'seq'       => 1,
                'phase'     => 'to_pickup',
            ]];
        }

        $delivery->update($updateData);

        app(DeliveryOfferService::class)->markAccepted($delivery, (int) $rider->id);

        return response()->json(['message' => 'Delivery accepted.']);
    }

    /**
     * Rider rejects (or cancels before accepting) -> reassign to next nearest.
     */
    public function reject(Request $request)
    {
        $rider = $request->user();

        $delivery = Delivery::where('id', $request->delivery_id)->first();

        if (!$delivery) {
            return response()->json(['message' => 'Delivery not found.'], 404);
        }

        if (in_array($delivery->status->value, ['completed', 'finshed', 'cancelled'])) {
            return response()->json(['message' => 'Cannot reject a finished delivery.'], 422);
        }

        $wasAccepted = $delivery->status->value === 'accepted' || $delivery->status->value === 'arrived';

        // Record cancel GPS + add to rejected list + cooldown.
        $delivery->recordRiderCancelLocation($rider, $request->input('lat'), $request->input('lng'));

        $rejected = $delivery->rejected_riders ?? [];
        $rejected = array_map(fn ($id) => is_numeric($id) ? (int) $id : $id, $rejected);
        if (!in_array((int) $rider->id, $rejected, true)) {
            $rejected[] = (int) $rider->id;
        }

        Cache::put("delivery_cooldown:{$delivery->id}:{$rider->id}", 'manual', now()->addMinute());

        $delivery->update([
            'rider_id' => null,
            'rejected_riders' => $rejected,
            'status' => 'pending',
        ]);

        if ($wasAccepted) {
            app(DeliveryOfferService::class)->markCancelledAfterAccept($delivery, (int) $rider->id);
        } else {
            app(DeliveryOfferService::class)->markRejected($delivery, (int) $rider->id);
        }

        // Try to reassign to the next-nearest rider.
        $controller = new UserDeliveryController();
        $newRiderId = $controller->searchAlternativeRider($delivery);

        if (!$newRiderId) {
            $delivery->update(['status' => 'rejected']);
        }

        return response()->json(['message' => 'Delivery rejected.']);
    }

    /**
     * Rider marks arrival at the PICKUP location.
     */
    public function arrived(Request $request)
    {
        $delivery = $this->validateDelivery($request);
        $rider = $request->user();

        $arrivedLat = $request->input('lat') ?? $rider->latitude;
        $arrivedLng = $request->input('lng') ?? $rider->longitude;

        $updateData = [
            'status' => 'arrived',
            'arrived_at' => now(),
        ];

        if ($arrivedLat !== null && $arrivedLng !== null) {
            $updateData['rider_arrived_lat'] = (float) $arrivedLat;
            $updateData['rider_arrived_lng'] = (float) $arrivedLng;

            $toPickup = $delivery->to_pickup_route_points ?? [];
            $toPickup[] = [
                'lat'       => (float) $arrivedLat,
                'lng'       => (float) $arrivedLng,
                'bearing'   => (float) ($rider->bearing ?? 0),
                'timestamp' => now()->timestamp,
                'seq'       => count($toPickup) + 1,
                'phase'     => 'to_pickup',
            ];
            $updateData['to_pickup_route_points'] = $toPickup;
        }

        $delivery->update($updateData);

        return response()->json(['message' => 'Marked as arrived at pickup.']);
    }

    /**
     * Rider finishes the delivery at the user's destination. Settles the fare
     * and deducts the admin commission from the rider's wallet.
     */
    public function finish(Request $request)
    {
        $delivery = $this->validateDelivery($request);
        $rider = $request->user();

        if (in_array($delivery->status->value, ['finshed', 'cancelled'])) {
            return response()->json(['message' => 'Delivery already closed.'], 422);
        }

        // 1) Distance: prefer actual route points, fall back to estimate.
        $points = is_array($delivery->route_points) ? $delivery->route_points : [];
        $distanceKm = count($points) >= 2
            ? $this->routeDistanceKm($points)
            : (float) ($delivery->estimated_km ?? 0);

        // 2) Duration: from accepted/started time.
        $startTime = $delivery->started_at ?? $delivery->accepted_at ?? $delivery->created_at;
        $durationMinutes = $startTime ? ceil(Carbon::parse($startTime)->floatDiffInMinutes(now())) : (int) ($delivery->estimated_time ?? 0);

        // 3) Fare from zone + vehicle type pricing.
        $vehicleType = $delivery->vehicle_type?->value;
        $priceRow = DeliveryZonePrice::where('zone_id', $delivery->zone_id)
            ->where('vehicle_type', $vehicleType)
            ->first();

        $fare = $priceRow
            ? round($priceRow->calculatePrice($distanceKm, $durationMinutes), 2)
            : (float) ($delivery->calculated_initial_price ?? 0);

        // 4) Admin commission from rider wallet (zone override or global).
        $zone = $delivery->zone_id ? Zone::find($delivery->zone_id) : null;
        $adminProfitPercentage = $zone->admin_profit_percentage ?? AppSetting::getAdminProfitPercentage();
        $adminProfitAmount = round($fare * ($adminProfitPercentage / 100), 2);

        $delivery->update([
            'status' => 'finshed',
            'calculated_final_price' => $fare,
            'original_price' => $fare,
            'total_distance_in_km' => round($distanceKm, 2),
            'time_taken' => $durationMinutes,
            'completed_at' => now(),
            'ended_at' => now(),
        ]);

        if ($rider && $adminProfitAmount > 0) {
            try {
                $rider->decrement('wallet', $adminProfitAmount);
                WalletRequest::create([
                    'driver_id' => $rider->id,
                    'amount' => $adminProfitAmount,
                    'type' => 'deduction',
                    'status' => 'approved',
                    'note' => "Admin profit commission for delivery #{$delivery->id}",
                ]);
            } catch (\Throwable $e) {
                Log::error("Failed to settle delivery commission for delivery {$delivery->id}: " . $e->getMessage());
            }
        }

        return response()->json([
            'message' => 'Delivery finished.',
            'pricing' => [
                'final_fare' => $fare,
                'distance_km' => round($distanceKm, 2),
                'duration_minutes' => $durationMinutes,
            ],
            'admin_profit' => [
                'percentage' => $adminProfitPercentage,
                'amount' => $adminProfitAmount,
                'rider_amount' => round($fare - $adminProfitAmount, 2),
            ],
        ]);
    }

    private function routeDistanceKm(array $points): float
    {
        $total = 0.0;
        for ($i = 1; $i < count($points); $i++) {
            $a = $points[$i - 1];
            $b = $points[$i];
            if (!isset($a['lat'], $a['lng'], $b['lat'], $b['lng'])) {
                continue;
            }
            $total += $this->haversineKm((float) $a['lat'], (float) $a['lng'], (float) $b['lat'], (float) $b['lng']);
        }
        return $total;
    }

    private function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
