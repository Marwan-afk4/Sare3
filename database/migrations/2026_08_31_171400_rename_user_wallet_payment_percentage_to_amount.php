<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $legacy = DB::table('app_settings')->where('key', 'user_wallet_payment_percentage')->first();
        $amountExists = DB::table('app_settings')->where('key', 'user_wallet_payment_amount')->exists();

        if ($legacy && !$amountExists) {
            DB::table('app_settings')->where('id', $legacy->id)->update([
                'key' => 'user_wallet_payment_amount',
                'description' => 'Maximum amount of the ride fare that can be paid from the user wallet',
                'updated_at' => now(),
            ]);

            return;
        }

        if ($legacy && $amountExists) {
            DB::table('app_settings')->where('key', 'user_wallet_payment_percentage')->delete();
        }

        if (!$amountExists) {
            DB::table('app_settings')->insert([
                'key' => 'user_wallet_payment_amount',
                'value' => $legacy->value ?? '0',
                'type' => 'string',
                'description' => 'Maximum amount of the ride fare that can be paid from the user wallet',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $amount = DB::table('app_settings')->where('key', 'user_wallet_payment_amount')->first();
        $percentageExists = DB::table('app_settings')->where('key', 'user_wallet_payment_percentage')->exists();

        if ($amount && !$percentageExists) {
            DB::table('app_settings')->where('id', $amount->id)->update([
                'key' => 'user_wallet_payment_percentage',
                'description' => 'Maximum percentage of the ride fare that can be paid from the user wallet (0-100%)',
                'updated_at' => now(),
            ]);

            return;
        }

        if ($amount) {
            DB::table('app_settings')->where('key', 'user_wallet_payment_amount')->delete();
        }
    }
};
