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
        Schema::table('ride_estimates', function (Blueprint $table) {
            $table->double('estimated_km')->nullable()->change();
            $table->double('estimated_time')->nullable()->change();;
            $table->double('calculated_price')->nullable()->change();;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ride_estimates', function (Blueprint $table) {
            //
        });
    }
};
