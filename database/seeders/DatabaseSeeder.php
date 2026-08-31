<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ensure a base test user exists without relying on factory columns
        if (!User::where('email', 'test@example.com')->exists()) {
            User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '+966555000001',
                'password' => 'password123',
                'role' => 'user',
                'activity' => 'active',
            ]);
        }

        // Seed test drivers with cars and demo rides
        $this->call([
            SuperAdminSeeder::class,
            CitySeeder::class,
            TestDriversSeeder::class,
            TestRidesSeeder::class,
        ]);
    }
}
