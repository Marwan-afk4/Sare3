<?php

namespace App\Http\Controllers\Api\Driver;

use App\Helpers\RideHelper;
use App\Http\Controllers\Api\User\RideEstimateController;
use App\Http\Controllers\Controller;
use App\Jobs\AutoRejectRideJob;
use App\Models\AppSetting;
use App\Models\CarCategory;
use App\Models\Ride;
use App\Models\RideProfit;
use App\Models\Zone;
use App\Services\ReferralDiscountService;
use App\Services\RideVerificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;

class RideActionsController extends Controller
{
    protected Database $firebase;

    public function __construct()
    {
        $this->firebase = (new Factory)
            ->withServiceAccount(storage_path('firebase/sarea-adce3-firebase-adminsdk-fbsvc-892a07f354.json'))
            ->withDatabaseUri('https://sarea-adce3-default-rtdb.firebaseio.com')
            ->createDatabase();
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

    protected function updateFirebase(Ride $ride, array $data): void
    {
        $firebaseRideId = 'ride_' . $ride->id;
        $this->firebase->getReference("rides/$firebaseRideId")->update($data);
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

        $ride->update([
            'driver_id' => $driver->id,
            'started_at' => $startTime,
            'status' => 'accepted',
            'accepted_at' => $startTime,
        ]);

        $firebaseData = [
            'driver_id' => $driver->id,
            'status' => 'accepted',
            'accepted_at' => now()->toIso8601String(),
        ];

        // Generate verification code if feature is enabled
        $verificationService = new RideVerificationService();
        $verificationCode = $verificationService->generateCodeForRide($ride);

        if ($verificationCode) {
            $firebaseData['verification_required'] = true;
            // Code is stored separately in Firebase for user access only
        }

        try {
            $this->updateFirebase($ride, $firebaseData);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ride accepted in DB, but failed in Firebase.',
                'error' => $e->getMessage()
            ], 500);
        }

        $response = ['message' => 'Ride accepted.'];

        if ($verificationCode) {
            $response['verification_required'] = true;
            $response['message'] = 'Ride accepted. Verification code generated for user.';
        }

        return response()->json($response);
    }

    //arrived
    public function arrived(Request $request)
    {
        $ride = $this->validateRide($request);

        $ride->update([
            'status' => 'waiting_user',
            'arrived_at' => now(),
        ]);

        try {
            $this->updateFirebase($ride, [
                'status' => 'waiting_user',
                'arrived_at' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Firebase error.', 'error' => $e->getMessage()], 500);
        }

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

        try {
            $this->updateFirebase($ride, [
                'status' => 'in_progress',
                'started_at' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Firebase error.', 'error' => $e->getMessage()], 500);
        }

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

        try {
            $this->updateFirebase($ride, [
                'verification_verified' => true,
                'verified_at' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Firebase error.', 'error' => $e->getMessage()], 500);
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
        if (!$ride->started_at) {
            return response()->json(['message' => 'Ride has no start time.'], 400);
        }

        $startTime = Carbon::parse($ride->started_at);
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

        // 6️⃣ Admin Profit Calculation (on discounted fare)
        $adminProfitPercentage = AppSetting::getAdminProfitPercentage();
        $profitAmounts = RideProfit::calculateProfit($fare, $adminProfitPercentage);

        // 7️⃣ Update Driver Wallet (deduct admin profit)
        $driver = $ride->driver;
        if ($driver && $profitAmounts['admin_profit_amount'] > 0) {
            $driver->decrement('wallet', $profitAmounts['admin_profit_amount']);
        }

        // 8️⃣ Create Profit Record
        if ($adminProfitPercentage > 0) {
            RideProfit::createForRide($ride, $fare, $adminProfitPercentage);
        }

        // 9️⃣ Update Ride
        $ride->update([
            'calculated_final_price' => round($fare, 1),
            'original_price' => round($originalFare, 1),
            'discount_amount' => round($discountResult['total_discount_amount'], 2),
            'status' => 'completed',
            'ended_at' => $endTime,
            'time_taken' => $durationMinutes,
            'total_distance_in_km' => round($distanceKm, 1),
            'completed_at' => $endTime,
        ]);

        // 🔟 Push to Firebase
        try {
            $firebaseData = [
                'status' => 'completed',
                'completed_at' => $endTime->toIso8601String(),
                'final_price' => [
                    'original_fare' => round($originalFare, 1),
                    'final_fare' => round($fare, 1),
                    'discount_amount' => round($discountResult['total_discount_amount'], 1),
                    'distance_km' => round($distanceKm, 1),
                    'duration_minutes' => $durationMinutes,
                ],
            ];

            if (!empty($discountResult['applied_discounts'])) {
                $firebaseData['discounts'] = $discountResult['applied_discounts'];
            }

            $this->updateFirebase($ride, $firebaseData);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Firebase error.', 'error' => $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'Ride completed.',
            'pricing' => [
                'original_fare' => round($originalFare, 1),
                'final_fare' => round($fare, 1),
                'discount_amount' => round($discountResult['total_discount_amount'], 1),
                'applied_discounts' => $discountResult['applied_discounts']
            ],
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

        try {
            $this->updateFirebase($ride, [
                'status' => 'finshed',
                'started_at' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Firebase error.', 'error' => $e->getMessage()], 500);
        }

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

        // 👇 خد نسخة من driver_id قبل ما نفضيه
        $currentDriverId = $request->user()->id;

        // Add current driver to rejected drivers list
        $rejectedDrivers = $ride->rejected_drivers ?? [];
        if ($currentDriverId && !in_array($currentDriverId, $rejectedDrivers)) {
            $rejectedDrivers[] = $currentDriverId;
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

            // Sync Firebase
            $this->updateFirebase($ride, [
                'driver_id' => null,
                'rejected_drivers' => $rejectedDrivers,
                'status' => 'pending',
                'canceled_at' => now()->toIso8601String(),
            ]);

            // دور على بديل
            $rideEstimateController = new RideEstimateController();
            $alternativeDriver = $rideEstimateController->searchAlternativeDriver($ride);

            DB::commit();

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

                $this->updateFirebase($ride, [
                    'driver_id' => $driverId,
                    'status' => 'pending',
                    'reassigned_at' => now()->toIso8601String(),
                    'previous_rejections' => count($rejectedDrivers),
                ]);

                // Schedule auto-reject job for the new driver
                $timeoutSeconds = config('ride.auto_reject_timeout_seconds', 15);
                AutoRejectRideJob::dispatch(
                    $ride->id,
                    $driverId,
                    $ride->updated_at->format('Y-m-d H:i:s')
                )->delay(now()->addSeconds($timeoutSeconds));
            } else {
                return response()->json([
                    'message' => 'Ride rejected. No alternative drivers available.',
                    'status' => 'pending'
                ]);
            }
        } catch (\Exception $e) {
            DB::rollback();

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
