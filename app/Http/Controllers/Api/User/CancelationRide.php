<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\User\RideEstimateController;
use App\Jobs\AutoRejectRideJob;
use App\Models\CancelationRide as ModelsCancelationRide;
use App\Models\CancellationPolicy;
use App\Models\Ride;
use App\Models\Transaction;
use App\Services\RideOfferService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Factory;

class CancelationRide extends Controller
{


    public function cancel(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'ride_id' => 'required|exists:rides,id',
            'reason' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => $validation->errors()->first()], 400);
        }

        $ride = Ride::find($request->input('ride_id'));
        $user = $request->user(); // المستخدم الحالي
        $now = now();

        // ✅ Determine who is canceling based on the ride relationship (not user's role column)
        $isPassenger = $ride->user_id === $user->id;
        $isDriver = $ride->driver_id === $user->id;

        // التأكد من أن الرحلة لم تُلغَ مسبقًا (only check for user cancellation, not driver)
        if ($isPassenger && $ride->status->value === 'cancelled') {
            return response()->json(['message' => 'Ride already canceled'], 400);
        }
        
        // Prevent canceling completed rides
        if (in_array($ride->status->value, ['completed', 'finshed'])) {
            Log::warning("Attempt to cancel completed ride", [
                'ride_id' => $ride->id,
                'current_status' => $ride->status->value,
                'user_id' => $user->id,
                'is_passenger' => $isPassenger,
                'is_driver' => $isDriver
            ]);
            return response()->json(['message' => 'Cannot cancel a completed ride'], 422);
        }

        // Handle driver cancellation differently - set to pending and search for alternative driver
        if ($isDriver) {
            return $this->handleDriverCancellation($ride, $user, $request);
        }

        // حساب الوقت منذ بداية الحجز بالتقريب لأعلى دقيقة
        $rideCreatedAt = Carbon::parse($ride->created_at);
        $minutesSinceBooking = ceil($rideCreatedAt->floatDiffInMinutes($now));

        // At this point, only passenger cancellation is handled (driver cancellation returned early)
        if (!$isPassenger) {
            return response()->json(['message' => 'Unable to determine user type for cancellation'], 400);
        }

        // Get zone-based cancellation policy for rider
        $selectedPolicy = null;
        if ($ride->zone_id) {
            $selectedPolicy = CancellationPolicy::where('status', 'active')
                ->where('user_type', 'rider')
                ->where('zone_id', $ride->zone_id)
                ->first();
        }

        // Check if penalty should be applied based on time limit
        $shouldApplyPenalty = false;
        if ($selectedPolicy && $selectedPolicy->time_limit_minutes !== null) {
            // If minutes since booking exceeds time limit, apply penalty
            $shouldApplyPenalty = $minutesSinceBooking > $selectedPolicy->time_limit_minutes;
        }

        // Calculate penalty if applicable
        $penaltyAmount = 0;
        if ($shouldApplyPenalty && $selectedPolicy) {
            $estimatedFare = $ride->calculated_initial_price ?? 0;

            if ($selectedPolicy->penalty_amount !== null) {
                $penaltyAmount = $selectedPolicy->penalty_amount;
            } elseif ($selectedPolicy->penalty_percent !== null) {
                $penaltyAmount = $estimatedFare * ($selectedPolicy->penalty_percent / 100);
            }

            // Deduct from user wallet
            if ($penaltyAmount > 0) {
                $user->wallet = $user->wallet - $penaltyAmount;
                $user->save();

                // Create transaction record for wallet history
                Transaction::create([
                    'user_id' => $user->id,
                    'driver_id' => $ride->driver_id ?? null,
                    'amount' => -$penaltyAmount, // Negative amount to indicate deduction
                    'description' => "Cancellation penalty - Ride #{$ride->id} ({$selectedPolicy->name})",
                ]);
            }
        }

        // Flag the ride if the passenger gave up while the request was still
        // looking for a captain (no one had accepted yet). This is what the
        // admin dashboard uses for the "cancelled before any accept" filter.
        $wasNeverAccepted = is_null($ride->accepted_at)
            && in_array($ride->status->value, ['pending', 'rejected']);

        // تحديث حالة الرحلة
        $ride->update([
            'status' => 'cancelled',
            'cancelled_before_accept' => $wasNeverAccepted,
        ]);

        // Close every still-pending offer as "cancelled_by_user" so the
        // audit trail on the admin dashboard is accurate.
        if ($wasNeverAccepted) {
            app(RideOfferService::class)->markAllPendingCancelledByUser($ride, 'passenger_cancelled');
        }

        // Save cancellation record
        ModelsCancelationRide::create([
            'ride_id' => $ride->id,
            'user_id' => $user->id,
            'driver_id' => null,
            'cancelation_policy_id' => $selectedPolicy ? $selectedPolicy->id : null,
            'canceled_by' => 'user',
            'canceled_at' => $now,
            'penalty_applied' => $penaltyAmount > 0 ? 'yes' : 'no',
            'penalty_amount' => round($penaltyAmount, 2),
            'reason' => $request->input('reason'),
        ]);

        try {
            $firebase = (new Factory)
                ->withServiceAccount(storage_path('firebase/sarea-adce3-firebase-adminsdk-fbsvc-892a07f354.json'))
                ->withDatabaseUri('https://sarea-adce3-default-rtdb.firebaseio.com')
                ->createDatabase();

            $firebaseRef = $firebase->getReference("rides/{$ride->firebase_ride_id}");
            // Passenger canceled - remove from Firebase
            $firebaseRef->remove();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ride canceled, but failed to update Firebase',
                'firebase_error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Ride canceled successfully',
            'minutes_since_booking' => $minutesSinceBooking,
            'cancellation_policy_used' => $selectedPolicy ? $selectedPolicy->name : null,
            'time_limit_minutes' => $selectedPolicy ? $selectedPolicy->time_limit_minutes : null,
            'penalty_applied' => $penaltyAmount > 0,
            'penalty_amount' => round($penaltyAmount, 2),
        ]);
    }

    /**
     * Handle driver cancellation - set status to pending and search for alternative driver
     */
    private function handleDriverCancellation(Ride $ride, $driver, Request $request)
    {
        $currentDriverId = $driver->id;

        // Record this captain's response in the offer audit trail. If the
        // ride had already been accepted this is a cancellation-after-accept,
        // otherwise it's a plain rejection.
        $wasAccepted = !is_null($ride->accepted_at) || in_array($ride->status->value, ['accepted', 'waiting_user', 'in_progress']);
        $offerService = app(RideOfferService::class);
        if ($wasAccepted) {
            $offerService->markCancelledAfterAccept($ride, (int) $currentDriverId);
        } else {
            $offerService->markRejected($ride, (int) $currentDriverId);
        }

        // Add current driver to rejected drivers list
        $rejectedDrivers = $ride->rejected_drivers ?? [];
        if ($currentDriverId && !in_array($currentDriverId, $rejectedDrivers)) {
            $rejectedDrivers[] = $currentDriverId;
        }

        DB::beginTransaction();

        try {
            // Update ride: reset driver_id, add to rejected drivers, set status to pending
            $ride->update([
                'driver_id' => null,
                'rejected_drivers' => $rejectedDrivers,
                'canceled_at' => now()->toIso8601String(),
                'status' => 'pending',
            ]);

            // Update Firebase
            try {
                $firebase = (new Factory)
                    ->withServiceAccount(storage_path('firebase/sarea-adce3-firebase-adminsdk-fbsvc-892a07f354.json'))
                    ->withDatabaseUri('https://sarea-adce3-default-rtdb.firebaseio.com')
                    ->createDatabase();

                $firebaseRef = $firebase->getReference("rides/{$ride->firebase_ride_id}");
                $firebaseRef->update([
                    'driver_id' => null,
                    'rejected_drivers' => $rejectedDrivers,
                    'status' => 'pending',
                    'canceled_at' => now()->toIso8601String(),
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to update Firebase in driver cancellation: " . $e->getMessage());
            }

            // Search for alternative driver
            $rideEstimateController = new RideEstimateController();
            $alternativeDriver = $rideEstimateController->searchAlternativeDriver($ride);

            DB::commit();

            if ($alternativeDriver) {
                $driverId = $alternativeDriver['driver_id'] ?? $alternativeDriver['id'] ?? null;

                if (!$driverId) {
                    Log::error("Invalid alternative driver structure in handleDriverCancellation", ['alternative_driver' => $alternativeDriver]);
                    return response()->json([
                        'message' => 'Ride rejected. Failed to find alternative driver.',
                        'status' => 'pending'
                    ]);
                }

                $ride->update([
                    'driver_id' => $driverId,
                    'status' => 'pending',
                    'reassigned_at' => now(),
                ]);

                try {
                    $firebase = (new Factory)
                        ->withServiceAccount(storage_path('firebase/sarea-adce3-firebase-adminsdk-fbsvc-892a07f354.json'))
                        ->withDatabaseUri('https://sarea-adce3-default-rtdb.firebaseio.com')
                        ->createDatabase();

                    $firebaseRef = $firebase->getReference("rides/{$ride->firebase_ride_id}");
                    $firebaseRef->update([
                        'driver_id' => $driverId,
                        'status' => 'pending',
                        'reassigned_at' => now()->toIso8601String(),
                        'previous_rejections' => count($rejectedDrivers),
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to update Firebase with alternative driver: " . $e->getMessage());
                }

                // Schedule auto-reject job for the new driver
                $timeoutSeconds = config('ride.auto_reject_timeout_seconds', 15);
                AutoRejectRideJob::dispatch(
                    $ride->id,
                    $driverId,
                    $ride->updated_at->format('Y-m-d H:i:s')
                )->delay(now()->addSeconds($timeoutSeconds));

                return response()->json([
                    'message' => 'Ride rejected. Alternative driver assigned.',
                    'status' => 'pending'
                ]);
            } else {
                return response()->json([
                    'message' => 'Ride rejected. No alternative drivers available.',
                    'status' => 'pending'
                ]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error in handleDriverCancellation: ' . $e->getMessage(), [
                'ride_id' => $ride->id,
                'driver_id' => $currentDriverId,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Failed to cancel ride'], 500);
        }
    }
}
