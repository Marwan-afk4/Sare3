<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Stores the GPS polyline the captain drove while heading to the
     * passenger's pickup location (from accept until arrival). This is the
     * "on the way to passenger" leg, kept separate from `route_points`
     * which holds the actual trip leg (pickup -> destination).
     */
    public function up(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            $table->json('to_pickup_route_points')->nullable()->after('route_points');
        });
    }

    public function down(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            $table->dropColumn('to_pickup_route_points');
        });
    }
};
