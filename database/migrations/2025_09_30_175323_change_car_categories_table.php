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
        Schema::table('car_categories', function (Blueprint $table) {
            //make base_price nullable
            $table->decimal('base_price', 8, 2)->nullable()->change();
            //make price_per_time nullable
            $table->decimal('price_per_time', 8, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
