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
        Schema::table('cancellation_policies', function (Blueprint $table) {
            // Add zone_id
            $table->foreignId('zone_id')->nullable()->after('name')->constrained('zones')->onDelete('cascade');
            
            // Drop old columns
            $table->dropColumn(['min_minutes', 'max_minutes']);
            
            // Add new time_limit_minutes column
            $table->integer('time_limit_minutes')->nullable()->after('zone_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cancellation_policies', function (Blueprint $table) {
            // Drop zone_id
            $table->dropForeign(['zone_id']);
            $table->dropColumn('zone_id');
            
            // Drop time_limit_minutes
            $table->dropColumn('time_limit_minutes');
            
            // Restore old columns
            $table->integer('min_minutes')->nullable();
            $table->integer('max_minutes')->nullable();
        });
    }
};
