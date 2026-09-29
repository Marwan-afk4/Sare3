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
            $table->decimal('user_wallet_before', 20, 3)->nullable()->after('wallet_paid_amount');
            $table->decimal('user_wallet_after', 20, 3)->nullable()->after('user_wallet_before');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            $table->dropColumn(['user_wallet_before', 'user_wallet_after']);
        });
    }
};
