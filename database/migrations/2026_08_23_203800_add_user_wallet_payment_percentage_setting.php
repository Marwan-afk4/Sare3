<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('app_settings')->where('key', 'user_wallet_payment_percentage')->exists()) {
            return;
        }

        DB::table('app_settings')->insert([
            'key' => 'user_wallet_payment_percentage',
            'value' => '100',
            'type' => 'string',
            'description' => 'Maximum percentage of the ride fare that can be paid from the user wallet (0-100%)',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('app_settings')->where('key', 'user_wallet_payment_percentage')->delete();
    }
};
