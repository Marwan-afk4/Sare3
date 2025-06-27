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
        Schema::create('ride_estimates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('car_category_id')->constrained('car_categories')->onDelete('cascade');

            $table->decimal('pickup_lat',10,8);
            $table->decimal('pickup_lng',10,8);
            $table->decimal('dropoff_lat',10,8);
            $table->decimal('dropoff_lng',10,8);

            $table->double('estimated_km');
            $table->double('estimated_time');
            $table->double('calculated_price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ride_estimates');
    }
};
