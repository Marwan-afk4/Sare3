<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('signup_gift_received_at')->nullable()->after('wallet_limit');
            $table->decimal('signup_gift_amount', 10, 2)->nullable()->after('signup_gift_received_at');
            $table->foreignId('signup_gift_granted_by')
                ->nullable()
                ->after('signup_gift_amount')
                ->constrained('users')
                ->nullOnDelete();

            $table->index('signup_gift_received_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['signup_gift_received_at']);
            $table->dropConstrainedForeignId('signup_gift_granted_by');
            $table->dropColumn(['signup_gift_received_at', 'signup_gift_amount']);
        });
    }
};
