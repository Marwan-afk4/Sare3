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
        Schema::create('car_category_zone', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_category_id')->constrained('car_categories')->onDelete('cascade');
            $table->foreignId('zone_id')->constrained('zones')->onDelete('cascade');

            $table->decimal('base_price', 10, 2);
            $table->decimal('price_per_km', 10, 2);
            $table->decimal('price_per_min', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_category_zone');
    }
};
