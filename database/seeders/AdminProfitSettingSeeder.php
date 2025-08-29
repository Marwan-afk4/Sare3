<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminProfitSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\AppSetting::set(
            'admin_profit_percentage', 
            10, // Default 10% profit
            'string', 
            'Admin profit percentage from rides'
        );
    }
}
