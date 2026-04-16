<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Stores the captain's GPS coordinates at the exact moment they accepted
     * the request and at the moment they arrived at the passenger's pickup
     * location. Used by the admin dashboard to draw the "on the way to
     * passenger" path and show where the captain was when accepting.
     */
    public function up(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            $table->double('driver_accept_lat', 11, 7)->nullable()->after('accepted_at');
            $table->double('driver_accept_lng', 11, 7)->nullable()->after('driver_accept_lat');
            $table->double('driver_arrived_lat', 11, 7)->nullable()->after('arrived_at');
            $table->double('driver_arrived_lng', 11, 7)->nullable()->after('driver_arrived_lat');
        });
    }

    public function down(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            $table->dropColumn([
                'driver_accept_lat',
                'driver_accept_lng',
                'driver_arrived_lat',
                'driver_arrived_lng',
            ]);
        });
    }
};
