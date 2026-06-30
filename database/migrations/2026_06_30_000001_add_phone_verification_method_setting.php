<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('app_settings')->where('key', 'phone_verification_method')->exists()) {
            return;
        }

        DB::table('app_settings')->insert([
            'key'         => 'phone_verification_method',
            'value'       => 'backend_otp',
            'type'        => 'string',
            'description' => 'Active phone verification method for mobile apps (backend_otp or firebase_otp)',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('app_settings')->where('key', 'phone_verification_method')->delete();
    }
};
