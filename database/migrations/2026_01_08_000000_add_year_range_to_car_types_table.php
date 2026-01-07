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
        Schema::table('car_types', function (Blueprint $table) {
            // Add new year range columns
            $table->integer('year_from')->nullable()->after('type_name');
            $table->integer('year_to')->nullable()->after('year_from');

            // Drop the old single year column
            $table->dropColumn('type_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_types', function (Blueprint $table) {
            // Add back the old single year column
            $table->integer('type_year')->nullable()->after('type_name');

            // Drop the new year range columns
            $table->dropColumn(['year_from', 'year_to']);
        });
    }
};
