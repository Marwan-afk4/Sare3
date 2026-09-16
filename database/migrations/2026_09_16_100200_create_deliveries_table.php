<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A delivery request. Mirrors the shape of `rides` but:
     *  - `rider_id` instead of `driver_id` (role = delivery)
     *  - `vehicle_type` (bike | motorcycle) of the assigned rider
     *  - no car_category_id
     *  - reuses the RideStatus values (pending/accepted/rejected/arrived/finshed/cancelled)
     *
     * The user drops a PICKUP pin (shop / order location) and the DROPOFF is
     * where the user currently is. We match the nearest rider to the pickup.
     */
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('rider_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('zone_id')->nullable()->constrained('zones')->onDelete('set null');
            $table->foreignId('payment_method_id')->nullable()->constrained('paymenent_methods')->onDelete('set null');

            // Vehicle type the price/assignment is locked to (from the rider).
            $table->enum('vehicle_type', ['bike', 'motorcycle'])->nullable();

            // Pickup = where the parcel/order is picked up.
            $table->decimal('pickup_lat', 10, 7);
            $table->decimal('pickup_lng', 10, 7);
            $table->string('pickup_address')->nullable();

            // Dropoff = where the user is (destination).
            $table->decimal('dropoff_lat', 10, 7)->nullable();
            $table->decimal('dropoff_lng', 10, 7)->nullable();
            $table->string('dropoff_address')->nullable();

            $table->string('status')->default('pending');

            // Estimates & pricing
            $table->decimal('estimated_km', 6, 2)->nullable();
            $table->integer('estimated_time')->nullable();
            $table->decimal('calculated_initial_price', 8, 2)->nullable();
            $table->decimal('calculated_final_price', 8, 2)->nullable();
            $table->decimal('original_price', 8, 2)->nullable();
            $table->decimal('total_distance_in_km', 8, 2)->nullable();
            $table->string('time_taken')->nullable();

            // Route polylines (same structure as rides)
            $table->json('route_points')->nullable();
            $table->json('to_pickup_route_points')->nullable();

            // Matching / reassignment bookkeeping
            $table->json('rejected_riders')->nullable();
            $table->timestamp('reassigned_at')->nullable();
            $table->timestamp('rider_assigned_at')->nullable();
            $table->timestamp('auto_rejected_at')->nullable();

            // Lifecycle timestamps
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Rider GPS snapshots
            $table->decimal('rider_accept_lat', 10, 7)->nullable();
            $table->decimal('rider_accept_lng', 10, 7)->nullable();
            $table->decimal('rider_arrived_lat', 10, 7)->nullable();
            $table->decimal('rider_arrived_lng', 10, 7)->nullable();
            $table->decimal('rider_cancel_lat', 10, 7)->nullable();
            $table->decimal('rider_cancel_lng', 10, 7)->nullable();
            $table->timestamp('rider_cancelled_at')->nullable();
            $table->foreignId('rider_cancelled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('cancelled_before_accept')->default(false);

            $table->foreignId('cancellation_reason_id')->nullable();

            $table->timestamps();

            $table->index(['status', 'rider_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
