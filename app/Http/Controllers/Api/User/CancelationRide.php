<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\CancelationRide as ModelsCancelationRide;
use App\Models\CancellationPolicy;
use App\Models\Ride;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

        // التأكد من أن الرحلة لم تُلغَ مسبقًا
        if ($ride->status->value === 'cancelled') {
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

        // حساب الوقت منذ بداية الحجز بالتقريب لأعلى دقيقة
        $rideCreatedAt = Carbon::parse($ride->created_at);
        $minutesSinceBooking = ceil($rideCreatedAt->floatDiffInMinutes($now));

        // جلب كل السياسات النشطة
        $policies = CancellationPolicy::where('status', true)->get();

        // تحديد السياسة المناسبة بناءً على المدة الزمنية
        $selectedPolicy = $policies->first(function ($policy) use ($minutesSinceBooking) {
            return $policy->min_minutes <= $minutesSinceBooking &&
                ($policy->max_minutes === null || $minutesSinceBooking <= $policy->max_minutes);
        });

        if (!$selectedPolicy) {
            ModelsCancelationRide::create([
                'ride_id' => $ride->id,
                'user_id' => $isPassenger ? $user->id : null,
                'driver_id' => $isDriver ? $user->id : null,
                'canceled_by' => $isPassenger ? 'user' : ($isDriver ? 'driver' : 'unknown'),
                'canceled_at' => $now,
                'reason' => $request->input('reason'),
            ]);

            // Update ride in DB
            $ride->update(['status' => 'cancelled']);

            // Update Firebase
            try {
                $firebase = (new Factory)
                    ->withServiceAccount(storage_path('firebase/sarea-adce3-firebase-adminsdk-fbsvc-892a07f354.json'))
                    ->withDatabaseUri('https://sarea-adce3-default-rtdb.firebaseio.com')
                    ->createDatabase();

                $firebaseRef = $firebase->getReference("rides/{$ride->firebase_ride_id}");

                // ✅ Firebase logic based on who canceled
                if ($isDriver) {
                    // Driver canceled - update status
                    $firebaseRef->update([
                        'status' => 'canceled',
                        'canceled_by' => 'driver',
                        'canceled_at' => $now->toIso8601String(),
                    ]);
                } elseif ($isPassenger) {
                    // Passenger canceled - remove from Firebase
                    $firebaseRef->remove();
                }
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Ride canceled, but failed to update Firebase',
                    'firebase_error' => $e->getMessage(),
                ], 500);
            }

            // ✅ Only now return response
            return response()->json([
                'message' => 'Ride canceled successfully , No applicable cancellation policy found.',
            ], 200);
        }

        // حساب الغرامة
        $estimatedFare = $ride->calculated_initial_price ?? 0;

        $penaltyAmount = 0;
        if ($selectedPolicy->penalty_amount !== null) {
            $penaltyAmount = $selectedPolicy->penalty_amount;
        } elseif ($selectedPolicy->penalty_percent !== null) {
            $penaltyAmount = $estimatedFare * ($selectedPolicy->penalty_percent / 100);
        }

        $user->wallet = $user->wallet - $penaltyAmount;
        $user->save();

        // Create transaction record for wallet history if penalty is applied
        if ($penaltyAmount > 0) {
            Transaction::create([
                'user_id' => $user->id,
                'driver_id' => $isPassenger ? ($ride->driver_id ?? null) : null,
                'amount' => -$penaltyAmount, // Negative amount to indicate deduction
                'description' => "Cancellation penalty - Ride #{$ride->id} ({$selectedPolicy->name})",
            ]);
        }

        // تحديث حالة الرحلة
        $ride->update(['status' => 'cancelled']);

        // حفظ سجل الإلغاء
        ModelsCancelationRide::create([
            'ride_id' => $ride->id,
            'user_id' => $isPassenger ? $user->id : null,
            'driver_id' => $isDriver ? $user->id : null,
            'cancelation_policy_id' => $selectedPolicy->id,
            'canceled_by' => $isPassenger ? 'user' : ($isDriver ? 'driver' : 'unknown'),
            'canceled_at' => $now,
            'penalty_applied' => $penaltyAmount > 0,
            'penalty_amount' => round($penaltyAmount, 2),
            'reason' => $request->input('reason'),
        ]);

        try {
            $firebase = (new Factory)
                ->withServiceAccount(storage_path('firebase/sarea-adce3-firebase-adminsdk-fbsvc-892a07f354.json'))
                ->withDatabaseUri('https://sarea-adce3-default-rtdb.firebaseio.com')
                ->createDatabase();

            $firebaseRef = $firebase->getReference("rides/{$ride->firebase_ride_id}");

            // ✅ Firebase logic based on who canceled
            if ($isDriver) {
                // Driver canceled - update status
                $firebaseRef->update([
                    'status' => 'canceled',
                    'canceled_by' => 'driver',
                    'canceled_at' => $now->toIso8601String(),
                    'penalty_amount' => round($penaltyAmount, 2),
                ]);
            } elseif ($isPassenger) {
                // Passenger canceled - remove from Firebase
                $firebaseRef->remove();
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ride canceled, but failed to update Firebase',
                'firebase_error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Ride canceled successfully',
            'minutes_since_booking' => $minutesSinceBooking,
            'cancellation_policy_used' => $selectedPolicy->name,
            'penalty_applied' => $penaltyAmount > 0,
            'penalty_amount' => round($penaltyAmount, 2),
        ]);
    }
}
