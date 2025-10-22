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
        Schema::create('car_category_driver_car', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_car_id')->constrained('driver_cars')->onDelete('cascade');
            $table->foreignId('car_category_id')->constrained('car_categories')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['driver_car_id', 'car_category_id'], 'car_category_driver_car_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_category_driver_car');
    }
};


