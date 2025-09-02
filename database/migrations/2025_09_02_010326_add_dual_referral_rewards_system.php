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
        Schema::table('referrals', function (Blueprint $table) {
            // Add referrer rewards
            $table->decimal('referrer_discount_percentage', 5, 2)->default(0);
            $table->integer('referrer_discount_rides_count')->default(0)->after('referrer_discount_percentage');
            $table->integer('referrer_used_rides_count')->default(0)->after('referrer_discount_rides_count');
            $table->boolean('referrer_rewards_active')->default(true)->after('referrer_used_rides_count');
        });

        // Create referrer_discounts table for tracking referrer rewards
        Schema::create('referrer_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_id')->constrained('referrals')->onDelete('cascade');
            $table->foreignId('ride_id')->constrained('rides')->onDelete('cascade');
            $table->foreignId('referrer_id')->constrained('users')->onDelete('cascade');
            $table->decimal('original_amount', 10, 2);
            $table->decimal('discount_amount', 10, 2);
            $table->decimal('final_amount', 10, 2);
            $table->decimal('discount_percentage', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrer_discounts');

        Schema::table('referrals', function (Blueprint $table) {
            $table->dropColumn([
                'referrer_discount_percentage',
                'referrer_discount_rides_count',
                'referrer_used_rides_count',
                'referrer_rewards_active'
            ]);
        });
    }
};
