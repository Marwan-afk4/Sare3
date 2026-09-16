<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-zone pricing for delivery, split by vehicle type. Bike and
     * motorcycle each have their own prices. This is NOT a car-category
     * stack; it is a flat price table edited on the zone screen.
     */
    public function up(): void
    {
        Schema::create('delivery_zone_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained('zones')->onDelete('cascade');
            $table->enum('vehicle_type', ['bike', 'motorcycle']);

            $table->decimal('base_price', 10, 2)->default(0);
            $table->decimal('price_per_km', 10, 2)->default(0);
            $table->decimal('price_per_min', 10, 2)->default(0);
            $table->decimal('min_price', 10, 2)->default(0);

            $table->timestamps();

            $table->unique(['zone_id', 'vehicle_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_zone_prices');
    }
};
