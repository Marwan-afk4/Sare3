<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Where the captain was when they ignored (timed out on) a ride offer.
     * Stored per offer so each ignored captain keeps their own pin.
     */
    public function up(): void
    {
        Schema::table('ride_offers', function (Blueprint $table) {
            $table->double('driver_lat', 11, 7)->nullable()->after('note');
            $table->double('driver_lng', 11, 7)->nullable()->after('driver_lat');
        });
    }

    public function down(): void
    {
        Schema::table('ride_offers', function (Blueprint $table) {
            $table->dropColumn(['driver_lat', 'driver_lng']);
        });
    }
};
