<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('car_category_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('pickup_lat', 10, 7);
            $table->decimal('pickup_lng', 10, 7);
            $table->decimal('dropoff_lat', 10, 7);
            $table->decimal('dropoff_lng', 10, 7);
            $table->enum('status', ['pending', 'accepted', 'on_the_way', 'arrived', 'completed', 'canceled'])->default('pending');
            $table->decimal('estimated_km', 6, 2)->nullable();
            $table->integer('estimated_time')->nullable();
            $table->decimal('calculated_initial_price', 8, 2)->nullable();
            $table->json('route_points')->nullable();
            $table->decimal('calculated_final_price', 8, 2)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->string('time_taken')->nullable();
            $table->string('firebase_ride_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rides');
    }
};
