<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Stores the captain's GPS coordinates and timestamp at the exact moment
     * they cancelled a ride they had already accepted (or rejected while it
     * was still pending). Used by the admin dashboard map to show where the
     * captain was when they backed out, even long after accepting.
     */
    public function up(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            $table->double('driver_cancel_lat', 11, 7)->nullable()->after('driver_arrived_lng');
            $table->double('driver_cancel_lng', 11, 7)->nullable()->after('driver_cancel_lat');
            $table->timestamp('driver_cancelled_at')->nullable()->after('driver_cancel_lng');
            $table->unsignedBigInteger('driver_cancelled_by')->nullable()->after('driver_cancelled_at');
        });
    }

    public function down(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            $table->dropColumn([
                'driver_cancel_lat',
                'driver_cancel_lng',
                'driver_cancelled_at',
                'driver_cancelled_by',
            ]);
        });
    }
};
