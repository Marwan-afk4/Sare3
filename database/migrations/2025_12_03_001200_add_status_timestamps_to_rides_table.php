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
            $table->timestamp('accepted_at')->nullable()->after('status');
            $table->timestamp('arrived_at')->nullable()->after('accepted_at');
            $table->timestamp('trip_started_at')->nullable()->after('arrived_at');
            $table->timestamp('completed_at')->nullable()->after('trip_started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            $table->dropColumn(['accepted_at', 'arrived_at', 'trip_started_at', 'completed_at']);
        });
    }
};
