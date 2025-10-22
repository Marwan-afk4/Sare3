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
        Schema::create('car_category_car_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_category_id')->constrained('car_categories')->onDelete('cascade');
            $table->foreignId('car_type_id')->constrained('car_types')->onDelete('cascade');
            $table->timestamps();
            
            // Ensure unique combination of category and type
            $table->unique(['car_category_id', 'car_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_category_car_type');
    }
};
