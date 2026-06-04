<?php

namespace App\Http\Controllers\Api\Driver;

use App\Helpers\RideHelper;
use App\Http\Controllers\Api\User\RideEstimateController;
use App\Events\RideStatusUpdated;
use App\Http\Controllers\Controller;
use App\Jobs\AutoRejectRideJob;
use App\Models\AppSetting;
use App\Models\CancellationPolicy;
use App\Models\CancelationRide;
use App\Models\CarCategory;
use App\Models\Ride;
use App\Models\RideProfit;
use App\Models\Transaction;
use App\Models\WalletRequest;
use App\Models\Zone;
use App\Services\BonusService;
use App\Services\ReferralDiscountService;
use App\Services\RideOfferService;
use App\Services\RideVerificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class RideActionsController extends Controller
{
    public function __construct()
    {
    }

    protected function validateRide(Request $request, $status = null, $requireDriverMatch = true): ?Ride
    {
        $validator = Validator::make($request->all(), [
            'ride_id' => 'required|exists:rides,id',
        ]);

        if ($validator->fails()) {
            abort(response()->json(['message' => $validator->errors()->first()], 422));
        }

        $query = Ride::where('id', $request->ride_id);

        if ($requireDriverMatch) {
            $query->where('driver_id', $request->user()->id);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $ride = $query->first();

        if (!$ride) {
            abort(response()->json(['message' => 'Ride not found or invalid status.'], 404));
        }

        return $ride;
    }



    //accept ride
    public function acceptRide(Request $request)
    {
        $driver = $request->user();
        $startTime = Carbon::now();

        $ride = Ride::where('id', $request->ride_id)
            ->whereIn('status', ['pending', 'rejected']) // Allow accepting rejected rides
            ->first();

        if (!$ride) {
            return response()->json(['message' => 'Ride not found or not available for acceptance.'], 404);
        }

        // Capture driver's exact location at the moment of accepting so the
        // admin dashboard can show it and draw the "on the way to passenger"
        // path. Prefer the lat/lng coming from the mobile app (most accurate),
        // fall back to the driver's last known location in Firebase.
        $acceptLat = $request->input('lat');
        $acceptLng = $request->input('lng');

        if ($acceptLat === null || $acceptLng === null) {
            $acceptLat = $driver->latitude;
            $acceptLng = $driver->longitude;
        }

        $updateData = [
            'driver_id' => $driver->id,
            'started_at' => $startTime,
            'status' => 'accepted',
            'accepted_at' => $startTime,
        ];

        if ($acceptLat !== null && $acceptLng !== null) {
            $updateData['driver_accept_lat'] = (float) $acceptLat;
            $updateData['driver_accept_lng'] = (float) $acceptLng;

            // Seed route_points with the first "to pickup" point so the admin
            // dashboard can render the path from moment of acceptance.
            $points = $ride->route_points ?? [];
            $points[] = [
                'lat' => (float) $acceptLat,
                'lng' => (float) $acceptLng,
                'timestamp' => $startTime->timestamp,
                'phase' => 'to_pickup',
                'seq' => 0,
                'source' => 'accept',
            ];
            $updateData['route_points'] = $points;
        }

        $ride->update($updateData);

        // Close out the pending offer for this captain as "accepted" in the
        // audit trail used by the admin dashboard filters.
        app(RideOfferService::class)->markAccepted($ride, (int) $driver->id);



        $response = ['message' => 'Ride accepted.'];

        // Generate verification code if feature is enabled and code is not yet set
        if (AppSetting::isRideVerificationEnabled() && !$ride->verification_code) {
            $ride->generateVerificationCode();
            $ride->refresh();
        }

        if ($ride->verification_code) {
            $response['verification_required'] = true;
            $response['message'] = 'Ride accepted. Verification code generated for user.';
        }

        // Broadcast status update via WebSocket (AFTER code generation)
        event(new RideStatusUpdated($ride));

        return response()->json($response);
    }

    //arrived
    public function arrived(Request $request)
    {
        $ride = $this->validateRide($request);

        // Capture driver's GPS at the moment they marked "arrived at pickup".
        // Used by the admin dashboard as the end-point of the "to pickup" path.
        $arrivedLat = $request->input('lat');
        $arrivedLng = $request->input('lng');

        if ($arrivedLat === null || $arrivedLng === null) {
            // Fall back to the last recorded route point if the app did not
            // send coordinates (old mobile versions).
            $points = $ride->route_points ?? [];
            if (!empty($points)) {
                $last = end($points);
                $arrivedLat = $last['lat'] ?? null;
                $arrivedLng = $last['lng'] ?? null;
            }

            if ($arrivedLat === null || $arrivedLng === null) {
                $arrivedLat = $driver->latitude ?? null;
                $arrivedLng = $driver->longitude ?? null;
            }
        }

        $updateData = [
            'status' => 'waiting_user',
            'arrived_at' => now(),
        ];

        if ($arrivedLat !== null && $arrivedLng !== null) {
            $updateData['driver_arrived_lat'] = (float) $arrivedLat;
            $updateData['driver_arrived_lng'] = (float) $arrivedLng;

            // Append arrival point to route_points as the final "to_pickup"
            // marker so the dashboard renders a complete pickup leg.
            $points = $ride->route_points ?? [];
            $nextSeq = !empty($points) ? (int)(end($points)['seq'] ?? count($points)) + 1 : 1;
            $points[] = [
                'lat' => (float) $arrivedLat,
                'lng' => (float) $arrivedLng,
                'timestamp' => now()->timestamp,
                'phase' => 'to_pickup',
                'seq' => $nextSeq,
                'source' => 'arrived',
            ];
            $updateData['route_points'] = $points;
        }

        $ride->update($updateData);

        // Broadcast status update via WebSocket
        event(new RideStatusUpdated($ride));

        return response()->json(['message' => 'Marked as arrived.']);
    }

    //start ride
    public function startRide(Request $request)
    {
        $ride = $this->validateRide($request);

        // Check if verification is required and not completed
        if (!$ride->canStart()) {
            return response()->json([
                'message' => 'Verification code required before starting the ride.',
                'verification_required' => true
            ], 422);
        }

        $ride->update([
            'status' => 'in_progress',
            'trip_started_at' => now(),
        ]);

        // Broadcast status update via WebSocket
        event(new RideStatusUpdated($ride));



        return response()->json(['message' => 'Ride started.']);
    }

    //verify ride code
    public function verifyRideCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ride_id' => 'required|exists:rides,id',
            'verification_code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        // Allow verification for rides in 'accepted' or 'waiting_user' status
        $ride = $this->validateRide($request);

        if (!in_array($ride->status->value, ['accepted', 'waiting_user'])) {
            return response()->json([
                'message' => 'Ride must be in accepted or waiting status for verification.',
                'current_status' => $ride->status->value
            ], 422);
        }

        if (!AppSetting::isRideVerificationEnabled()) {
            return response()->json(['message' => 'Verification feature is disabled.'], 400);
        }

        if (empty($ride->verification_code)) {
            return response()->json(['message' => 'No verification code generated for this ride.'], 400);
        }

        if ($ride->verification_code_verified) {
            return response()->json(['message' => 'Code already verified.'], 400);
        }

        if (!$ride->verifyCode($request->verification_code)) {
            return response()->json(['message' => 'Invalid verification code.'], 422);
        }



        return response()->json([
            'message' => 'Verification code verified successfully.',
            'can_start_ride' => true
        ]);
    }

    //get verification status
    public function getVerificationStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ride_id' => 'required|exists:rides,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $ride = $this->validateRide($request);

        $verificationService = new RideVerificationService();
        $status = $verificationService->getVerificationStatus($ride);

        return response()->json([
            'ride_id' => $ride->id,
            'status' => $ride->status->value,
            'verification' => $status
        ]);
    }

    //complete ride
    public function completeRide(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'ride_id' => 'required|exists:rides,id',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => $validation->errors()], 500);
        }

        $ride = Ride::findOrFail($request->ride_id);

        if ($ride->driver_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $points = is_array($ride->route_points) ? $ride->route_points : json_decode($ride->route_points, true);

        if (!is_array($points) || count($points) < 2) {
            return response()->json(['message' => 'Not enough points to calculate distance'], 400);
        }

        // 1️⃣ Distance Calculation
        $distanceKm = RideHelper::calculateTotalDistanceAccurate($points);

        // 2️⃣ Get Zone with Car Categories for zone-specific pricing
        if (!$ride->zone_id) {
            return response()->json(['message' => 'Ride has no zone assigned'], 400);
        }

        $zone = Zone::with('carCategories')->find($ride->zone_id);
        if (!$zone) {
            return response()->json(['message' => 'Zone not found'], 404);
        }

        // Find the specific category in this zone
        $categoryInZone = $zone->carCategories->where('id', $ride->car_category_id)->first();
        if (!$categoryInZone) {
            return response()->json(['message' => 'Car category not available in this zone'], 404);
        }

        // 3️⃣ Duration Calculation
        $actualStartTime = $ride->trip_started_at ?? $ride->started_at;
        if (!$actualStartTime) {
            return response()->json(['message' => 'Ride has no start time.'], 400);
        }

        $startTime = Carbon::parse($actualStartTime);
        $endTime = Carbon::now();
        $durationMinutes = ceil($startTime->floatDiffInMinutes($endTime));

        // 4️⃣ Fare Calculation using zone-specific pricing
        $base = $categoryInZone->pivot->base_price;
        $perKm = $categoryInZone->pivot->price_per_km;
        $perTime = $categoryInZone->pivot->price_per_min;
        $minPrice = $categoryInZone->pivot->min_price;

        $originalFare = $base + ($distanceKm * $perKm) + ($durationMinutes * $perTime);

        // Apply minimum price if calculated fare is lower
        if ($originalFare < $minPrice) {
            $originalFare = $minPrice;
        }

        // 5️⃣ Apply Referral Discounts
        $referralDiscountService = new ReferralDiscountService();
        $discountResult = $referralDiscountService->applyDiscounts($ride, $originalFare);
        $fare = $discountResult['final_fare'];
        // Round fareBeforeCoupon to 2 decimals for consistent discount calculation
        $fareBeforeCoupon = round($fare, 2);

        // 5.5️⃣ Calculate Coupon Discount (if coupon was used)
        $couponDiscountAmount = 0;
        if ($ride->coupon_id && $ride->coupon) {
            // Coupon discount is calculated based on the fare BEFORE applying coupon
            $couponDiscountAmount = $ride->coupon->calculateDiscount($fareBeforeCoupon);
            
            if ($couponDiscountAmount > 0) {
                // Calculate final fare from rounded fareBeforeCoupon to ensure consistency
                $fare = max(0, $fareBeforeCoupon - $couponDiscountAmount);
                
                // Add coupon to applied discounts
                $discountResult['applied_discounts'][] = [
                    'type' => 'coupon',
                    'code' => $ride->coupon->code,
                    'amount' => round($couponDiscountAmount, 2)
                ];
                
                $discountResult['total_discount_amount'] += $couponDiscountAmount;
            }
        }

        // 6️⃣ Admin Profit Calculation (on discounted fare)
        // Use zone-specific profit percentage if available, otherwise fall back to global setting.
        // IMPORTANT: We calculate admin profit on fare BEFORE coupon, so coupon benefits both user and driver.
        $adminProfitPercentage = $zone->admin_profit_percentage ?? AppSetting::getAdminProfitPercentage();
        $profitAmounts = RideProfit::calculateProfit($fareBeforeCoupon, $adminProfitPercentage);

        // Get driver reference
        $driver = $ride->driver;
        $user = $ride->user;

        // Initialize wallet payment tracking
        $walletPaidAmount = 0;
        $remainingAmount = round($fare, 2);

        // 9️⃣ Update Ride
        $updateData = [
            'calculated_final_price' => round($fare, 1),
            'original_price' => round($originalFare, 1),
            'discount_amount' => round($discountResult['total_discount_amount'], 2),
            'coupon_discount' => round($couponDiscountAmount, 2),
            'status' => 'completed',
            'ended_at' => $endTime,
            'time_taken' => $durationMinutes,
            'total_distance_in_km' => round($distanceKm, 1),
            'completed_at' => $endTime,
        ];
        
        // Store wallet payment amount (will be updated after wallet payment is processed)
        $updateData['wallet_paid_amount'] = 0;
        
        // Update calculated_initial_price to fareBeforeCoupon when coupon is used
        // This ensures the "Before" price in the view matches the price the coupon discount was calculated on
        if ($ride->coupon_id && $couponDiscountAmount > 0) {
            $updateData['calculated_initial_price'] = round($fareBeforeCoupon, 2);
        }
        
        Log::info("Completing ride {$ride->id} with coupon - BEFORE UPDATE", [
            'ride_id' => $ride->id,
            'current_status' => $ride->status->value,
            'coupon_id' => $ride->coupon_id,
            'coupon_discount' => round($couponDiscountAmount, 2),
            'original_fare' => round($originalFare, 1),
            'final_fare' => round($fare, 1),
            'total_discount' => round($discountResult['total_discount_amount'], 2),
            'update_data' => $updateData
        ]);
        
        // Use DB transaction to ensure atomic update of ALL operations
        DB::beginTransaction();
        try {
            // Update ride status and details
            $updateResult = $ride->update($updateData);
            
            Log::info("Ride update result", [
                'ride_id' => $ride->id,
                'update_result' => $updateResult,
                'updated_fields' => array_keys($updateData)
            ]);
            
            // Update coupon usage inside transaction
            if ($couponDiscountAmount > 0 && $ride->coupon_id) {
                Log::info("Updating coupon usage for ride {$ride->id}");
                $ride->couponUsage()->updateOrCreate(
                    ['ride_id' => $ride->id, 'coupon_id' => $ride->coupon_id],
                    [
                        'user_id' => $ride->user_id,
                        'discount_amount' => $couponDiscountAmount
                    ]
                );
            }

            // 7️⃣ Process wallet payment if user has wallet balance
            if ($user && $user->wallet > 0 && $fare > 0) {
                // Calculate how much can be paid from wallet (up to the fare amount)
                $walletPaidAmount = min($user->wallet, $fare);
                $remainingAmount = round($fare - $walletPaidAmount, 2);
                
                // Deduct from user wallet
                $user->decrement('wallet', $walletPaidAmount);
                
                // Update ride with wallet payment amount
                $ride->update(['wallet_paid_amount' => round($walletPaidAmount, 2)]);
                
                // Create transaction record for wallet payment
                Transaction::create([
                    'user_id' => $user->id,
                    'driver_id' => $driver ? $driver->id : null,
                    'amount' => -$walletPaidAmount, // Negative amount to indicate deduction
                    'description' => "Ride payment - Ride #{$ride->id} (Wallet: {$walletPaidAmount}, Remaining: {$remainingAmount})",
                ]);
                
                Log::info("Wallet payment processed for ride {$ride->id}", [
                    'user_id' => $user->id,
                    'wallet_paid' => $walletPaidAmount,
                    'remaining_amount' => $remainingAmount,
                    'final_fare' => $fare,
                    'user_wallet_after' => $user->fresh()->wallet
                ]);
            }

            // Prevent double settlement (wallet/profit) if completeRide is called more than once
            $settlementAlreadyProcessed = RideProfit::where('ride_id', $ride->id)->exists();
            if ($settlementAlreadyProcessed) {
                Log::warning("Settlement already processed for ride {$ride->id}; skipping wallet/profit settlement.");
            } else {
                // Update driver wallet inside transaction (admin commission)
                if ($driver && $profitAmounts['admin_profit_amount'] > 0) {
                    $adminProfitAmount = (float)$profitAmounts['admin_profit_amount'];

                    Log::info("Updating driver wallet for driver {$driver->id}, deducting admin profit {$adminProfitAmount}");
                    $driver->decrement('wallet', $adminProfitAmount);

                    // Wallet history entry for admin profit deduction (withdraw)
                    WalletRequest::create([
                        'driver_id' => $driver->id,
                        'amount' => round($adminProfitAmount, 2),
                        'type' => 'deduction',
                        'status' => 'approved',
                        'note' => "Admin profit commission for ride #{$ride->id}",
                    ]);

                    /**
                     * Coupon benefit for driver:
                     * If a coupon discounted the user, we also credit the driver wallet by the coupon discount value
                     * so coupon benefits both user and driver.
                     *
                     * Example: coupon 25% and admin profit 10% => net driver wallet change = +15% (25% - 10%).
                     */
                    if ($couponDiscountAmount > 0) {
                        $driverCouponCredit = round((float)$couponDiscountAmount, 2);
                        Log::info("Coupon applied; crediting driver {$driver->id} by coupon discount {$driverCouponCredit}.");
                        $driver->increment('wallet', $driverCouponCredit);

                        // Wallet history entry for coupon benefit (deposit)
                        WalletRequest::create([
                            'driver_id' => $driver->id,
                            'amount' => $driverCouponCredit,
                            'type' => 'deposit',
                            'status' => 'approved',
                            'note' => "Coupon benefit for ride #{$ride->id}",
                        ]);
                    }
                }

                // Create profit record inside transaction
                if ($adminProfitPercentage > 0) {
                    Log::info("Creating profit record for ride {$ride->id}");
                    RideProfit::createForRide($ride, $fareBeforeCoupon, $adminProfitPercentage);
                }
            }
            
            // Verify status was set correctly
            $ride->refresh();
            
            Log::info("Ride status after update and refresh", [
                'ride_id' => $ride->id,
                'status_value' => $ride->status->value,
                'status_raw' => $ride->getAttributes()['status'] ?? 'N/A',
                'completed_at' => $ride->completed_at
            ]);
            
            if ($ride->status->value !== 'completed') {
                Log::error("CRITICAL: Ride status not set to completed!", [
                    'ride_id' => $ride->id,
                    'expected_status' => 'completed',
                    'actual_status' => $ride->status->value,
                    'actual_status_raw' => $ride->getAttributes()['status'] ?? 'N/A',
                    'update_data_sent' => $updateData,
                    'fillable_fields' => $ride->getFillable()
                ]);
                
                // Try to force update status
                $ride->status = 'completed';
                $ride->save();
                $ride->refresh();
                
                Log::warning("Attempted force update of status", [
                    'ride_id' => $ride->id,
                    'new_status' => $ride->status->value
                ]);
            }
            
            // Broadcast status update via WebSocket
            event(new RideStatusUpdated($ride));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed to update ride during completion", [
                'ride_id' => $ride->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Failed to complete ride: ' . $e->getMessage()], 500);
        }

        // Check and award any bonus tier milestones the driver may have crossed
        try {
            $bonusService = app(BonusService::class);
            $bonusService->checkAndAwardTierBonus($driver);
        } catch (\Exception $e) {
            Log::error("Bonus check failed for driver {$driver->id} after ride {$ride->id}: " . $e->getMessage());
        }



        // Prepare wallet payment info for response
        // Original fare (before wallet deduction) = final fare after discounts
        // Wallet deduction = amount paid from user wallet
        // Remaining amount = amount to be paid by other payment method
        $walletPaymentInfo = [
            'original_fare_before_wallet' => round($fare, 2), // Fare before wallet deduction
            'wallet_deduction' => round($walletPaidAmount, 2), // Amount deducted from user wallet
            'remaining_amount_after_wallet' => round($remainingAmount, 2), // Amount remaining after wallet deduction
        ];

        $pricing = [
            'original_fare' => round($originalFare, 1), // Original fare before any discounts
            'final_fare' => round($fare, 1), // Final fare after discounts (before wallet deduction)
            'discount_amount' => round($discountResult['total_discount_amount'], 1),
            'coupon_discount' => round($couponDiscountAmount, 1),
            'applied_discounts' => $discountResult['applied_discounts'],
            'wallet_payment' => $walletPaymentInfo
        ];
        
        // Add fare_before_coupon when coupon is used for consistent display
        if ($ride->coupon_id && $couponDiscountAmount > 0) {
            $pricing['fare_before_coupon'] = round($fareBeforeCoupon, 2);
        }

        return response()->json([
            'message' => 'Ride completed.',
            'pricing' => $pricing,
            'ride_details' => [
                'distance_km' => round($distanceKm, 1),
                'duration_minutes' => $durationMinutes,
            ],
            'admin_profit' => [
                'percentage' => $adminProfitPercentage,
                'amount' => $profitAmounts['admin_profit_amount'],
                'driver_amount' => $profitAmounts['driver_amount']
            ]
        ]);
    }

    //finsh ride
    public function finishRide(Request $request)
    {
        $ride = $this->validateRide($request);

        $ride->update(['status' => 'finshed']);



        return response()->json(['message' => 'Ride started.']);
    }

    //cancel ride
    public function cancelRide(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ride_id' => 'required|exists:rides,id',
            'reason' => 'nullable|exists:cancellation_reasons,id',
        ]);

        if ($validator->fails()) {
            abort(response()->json(['message' => $validator->errors()->first()], 422));
        }

        $ride = Ride::findOrFail($request->ride_id);
        
        // Prevent canceling completed rides
        if (in_array($ride->status->value, ['completed', 'finshed'])) {
            Log::warning("Attempt to cancel completed ride", [
                'ride_id' => $ride->id,
                'current_status' => $ride->status->value,
                'driver_id' => $request->user()->id
            ]);
            return response()->json(['message' => 'Cannot cancel a completed ride'], 422);
        }

        // 👇 خد نسخة من driver_id قبل ما نفضيه
        $currentDriverId = $request->user()->id;
        $driver = $request->user();
        $now = now();

        // Calculate time since ride was created/accepted
        $rideCreatedAt = $ride->accepted_at ? Carbon::parse($ride->accepted_at) : Carbon::parse($ride->created_at);
        $minutesSinceBooking = ceil($rideCreatedAt->floatDiffInMinutes($now));

        // Get zone-based cancellation policy for driver
        $selectedPolicy = null;
        if ($ride->zone_id) {
            $selectedPolicy = CancellationPolicy::where('status', 'active')
                ->where('user_type', 'driver')
                ->where('zone_id', $ride->zone_id)
                ->first();
        }

        // Check if penalty should be applied based on time limit
        $shouldApplyPenalty = false;
        $penaltyAmount = 0;
        if ($selectedPolicy && $selectedPolicy->time_limit_minutes !== null) {
            // If minutes since booking exceeds time limit, apply penalty
            $shouldApplyPenalty = $minutesSinceBooking > $selectedPolicy->time_limit_minutes;

            if ($shouldApplyPenalty) {
                $estimatedFare = $ride->calculated_initial_price ?? 0;

                if ($selectedPolicy->penalty_amount !== null) {
                    $penaltyAmount = $selectedPolicy->penalty_amount;
                } elseif ($selectedPolicy->penalty_percent !== null) {
                    $penaltyAmount = $estimatedFare * ($selectedPolicy->penalty_percent / 100);
                }

                // Deduct from driver wallet
                if ($penaltyAmount > 0) {
                    $driver->wallet = max(0, $driver->wallet - $penaltyAmount);
                    $driver->save();

                    // Create transaction record for wallet history
                    Transaction::create([
                        'user_id' => null,
                        'driver_id' => $driver->id,
                        'amount' => -$penaltyAmount, // Negative amount to indicate deduction
                        'description' => "Cancellation penalty - Ride #{$ride->id} ({$selectedPolicy->name})",
                    ]);
                }
            }
        }

        // Save cancellation record (always create, even if no penalty)
        CancelationRide::create([
            'ride_id' => $ride->id,
            'user_id' => $ride->user_id,
            'driver_id' => $driver->id,
            'cancelation_policy_id' => $selectedPolicy ? $selectedPolicy->id : null,
            'canceled_by' => 'driver',
            'canceled_at' => $now,
            'penalty_applied' => $penaltyAmount > 0 ? 'yes' : 'no',
            'penalty_amount' => round($penaltyAmount, 2),
            'reason' => $request->reason ?? null,
        ]);

        // Add current driver to rejected drivers list
        $rejectedDrivers = $ride->rejected_drivers ?? [];
        if ($currentDriverId && !in_array($currentDriverId, $rejectedDrivers)) {
            $rejectedDrivers[] = $currentDriverId;
        }

        // Put the driver on a 1-minute cooldown for this ride request
        if ($currentDriverId) {
            \Illuminate\Support\Facades\Cache::put("ride_cooldown:{$ride->id}:{$currentDriverId}", 'manual', now()->addMinutes(1));
        }

        // Record the captain's response in the offer audit trail BEFORE we
        // blank out the driver on the ride. If the captain had already
        // accepted (ride has accepted_at) this counts as a cancellation
        // after accept; otherwise it's a plain rejection.
        $wasAccepted = !is_null($ride->accepted_at) || in_array($ride->status->value, ['accepted', 'waiting_user', 'in_progress']);
        $offerService = app(RideOfferService::class);
        if ($wasAccepted) {
            $offerService->markCancelledAfterAccept($ride, (int) $currentDriverId, $request->reason ? "reason_id:{$request->reason}" : null);
        } else {
            $offerService->markRejected($ride, (int) $currentDriverId, $request->reason ? "reason_id:{$request->reason}" : null);
        }

        DB::beginTransaction();

        try {
            // Update ride: reset driver_id, add to rejected drivers
            $ride->update([
                'driver_id' => null,
                'rejected_drivers' => $rejectedDrivers,
                'canceled_at' => now()->toIso8601String(),
                'status' => 'pending',
            ]);

            DB::commit();

            // ✅ Broadcast initial cancellation status update via WebSocket (after commit)
            try {
                event(new RideStatusUpdated($ride));
                Log::info("📡 Broadcasted initial RideStatusUpdated event for ride {$ride->id} after driver cancel in RideActionsController");
            } catch (\Exception $e) {
                Log::error("⚠️ Failed to broadcast initial RideStatusUpdated on driver cancel: " . $e->getMessage());
            }

            // دور على بديل
            $rideEstimateController = new RideEstimateController();
            $alternativeDriver = $rideEstimateController->searchAlternativeDriver($ride);

            if ($alternativeDriver) {
                $driverId = $alternativeDriver['driver_id'] ?? $alternativeDriver['id'] ?? null;

                if (!$driverId) {
                    Log::error("Invalid alternative driver structure in cancelRide", ['alternative_driver' => $alternativeDriver]);
                    return response()->json(['message' => 'Failed to find alternative driver'], 500);
                }

                $ride->update([
                    'driver_id' => $driverId,
                    'status' => 'pending',
                    'cancellation_reason_id' => $request->reason ?? null,
                    'reassigned_at' => now(),
                ]);

                // ✅ Broadcast the reassignment to passenger
                try {
                    event(new RideStatusUpdated($ride));
                    Log::info("📡 Broadcasted RideStatusUpdated event for reassigned ride {$ride->id} after driver cancel in RideActionsController");
                } catch (\Exception $e) {
                    Log::error("⚠️ Failed to broadcast RideStatusUpdated on driver cancel reassignment: " . $e->getMessage());
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
                    'status' => 'pending',
                    'penalty_applied' => $penaltyAmount > 0,
                    'penalty_amount' => round($penaltyAmount, 2),
                ]);
            } else {
                return response()->json([
                    'message' => 'Ride rejected. No alternative drivers available.',
                    'status' => 'pending',
                    'penalty_applied' => $penaltyAmount > 0,
                    'penalty_amount' => round($penaltyAmount, 2),
                ]);
            }
        } catch (\Exception $e) {
            // Rollback only if we are still inside transaction
            if (DB::transactionLevel() > 0) {
                DB::rollback();
            }

            Log::error('Error in cancelRide: ' . $e->getMessage(), [
                'ride_id' => $ride->id,
                'driver_id' => $currentDriverId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to process ride rejection.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
