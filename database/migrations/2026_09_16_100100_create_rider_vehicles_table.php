<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A delivery captain's vehicle (bike or motorcycle) plus the required
     * document images. This is deliberately separate from `driver_cars`
     * because deliveries have no car model / type / category stack.
     */
    public function up(): void
    {
        Schema::create('rider_vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rider_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['bike', 'motorcycle']);

            // Required images (paths on the public disk). Filled during
            // store-vehicle; the row itself is created at signup so the
            // chosen vehicle type is remembered.
            $table->string('rider_image')->nullable();    // photo of the rider
            $table->string('identity_image')->nullable();  // identity document
            $table->string('vehicle_image')->nullable();   // photo of the bike/motorcycle
            $table->string('license_image')->nullable();   // motorcycle license (motorcycle only)

            $table->timestamps();

            $table->index('rider_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rider_vehicles');
    }
};
