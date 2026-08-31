<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        if (! DB::table('app_settings')->where('key', 'signup_gift_enabled')->exists()) {
            DB::table('app_settings')->insert([
                'key' => 'signup_gift_enabled',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Automatically add a welcome gift to a user wallet on first signup',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (! DB::table('app_settings')->where('key', 'signup_gift_amount')->exists()) {
            DB::table('app_settings')->insert([
                'key' => 'signup_gift_amount',
                'value' => '0',
                'type' => 'string',
                'description' => 'Welcome gift amount added to the user wallet on first signup',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('app_settings')->whereIn('key', ['signup_gift_enabled', 'signup_gift_amount'])->delete();
    }
};
