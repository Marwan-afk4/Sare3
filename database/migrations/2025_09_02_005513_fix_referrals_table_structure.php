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
            // Rename existing columns to match our new structure
            if (Schema::hasColumn('referrals', 'rides_count')) {
                $table->renameColumn('rides_count', 'discount_rides_count');
            }
            if (Schema::hasColumn('referrals', 'used_rides')) {
                $table->renameColumn('used_rides', 'used_rides_count');
            }
            
            // Add missing columns
            if (!Schema::hasColumn('referrals', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('is_active');
            }
        });

        // Add referral_code to users table if not exists
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'referral_code')) {
                $table->string('referral_code', 10)->unique()->nullable()->after('referrer_id');
            }
        });

        // Create referral_discounts table
        if (!Schema::hasTable('referral_discounts')) {
            Schema::create('referral_discounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('referral_id')->constrained('referrals')->onDelete('cascade');
                $table->foreignId('ride_id')->constrained('rides')->onDelete('cascade');
                $table->decimal('original_amount', 10, 2);
                $table->decimal('discount_amount', 10, 2);
                $table->decimal('final_amount', 10, 2);
                $table->decimal('discount_percentage', 5, 2);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_discounts');
        
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'referral_code')) {
                $table->dropColumn('referral_code');
            }
        });
        
        Schema::table('referrals', function (Blueprint $table) {
            if (Schema::hasColumn('referrals', 'expires_at')) {
                $table->dropColumn('expires_at');
            }
            if (Schema::hasColumn('referrals', 'discount_rides_count')) {
                $table->renameColumn('discount_rides_count', 'rides_count');
            }
            if (Schema::hasColumn('referrals', 'used_rides_count')) {
                $table->renameColumn('used_rides_count', 'used_rides');
            }
        });
    }
};