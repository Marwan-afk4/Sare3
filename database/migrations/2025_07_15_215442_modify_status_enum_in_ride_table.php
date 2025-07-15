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
        Schema::table('rides', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('rides', function (Blueprint $table) {
            $table->enum('status', ['pending', 'accepted', 'rejected', 'completed','arrived','waiting_user', 'cancelled', 'in_progress','finshed'])
                ->default('pending')
                ->after('firebase_ride_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ride', function (Blueprint $table) {
            //
        });
    }
};
