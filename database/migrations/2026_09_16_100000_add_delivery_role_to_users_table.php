<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add the `delivery` value to the users.role enum so delivery captains
     * (bike / motorcycle riders) can be distinguished from car drivers.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('user','driver','admin','delivery') NOT NULL");
    }

    public function down(): void
    {
        // Revert any delivery accounts to driver before shrinking the enum
        DB::statement("UPDATE `users` SET `role` = 'driver' WHERE `role` = 'delivery'");
        DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('user','driver','admin') NOT NULL");
    }
};
