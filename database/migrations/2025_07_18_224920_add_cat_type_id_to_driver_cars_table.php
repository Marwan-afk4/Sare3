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
            $table->dropColumn('car_type');

            $table->foreignId('car_type_id')
                ->nullable()
                ->constrained('car_types')
                ->onDelete('set null')
                ->after('car_categories_id');
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
