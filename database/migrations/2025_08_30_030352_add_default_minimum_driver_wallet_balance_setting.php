<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert default minimum driver wallet balance setting
        DB::table('app_settings')->insert([
            'key' => 'minimum_driver_wallet_balance',
            'value' => '0',
            'type' => 'string',
            'description' => 'Minimum wallet balance required for drivers to go online',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('app_settings')->where('key', 'minimum_driver_wallet_balance')->delete();
    }
};
