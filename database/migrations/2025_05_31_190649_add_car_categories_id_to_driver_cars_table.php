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
        Schema::table('driver_cars', function (Blueprint $table) {
            $table->foreignId('car_categories_id')->after('driver_id')->nullable()->constrained('car_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver_cars', function (Blueprint $table) {
            //
        });
    }
};
