<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the `delivery` value to the users.role enum so delivery captains
     * (bike / motorcycle riders) can be distinguished from car drivers.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('user','driver','admin','delivery') NOT NULL");

            return;
        }

        // SQLite (tests) stores Laravel enums as strings with a CHECK that
        // rejects `delivery`. Widen the column so signup can persist.
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 32)->default('user')->change();
        });
    }

    public function down(): void
    {
        DB::statement("UPDATE `users` SET `role` = 'driver' WHERE `role` = 'delivery'");

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('user','driver','admin') NOT NULL");
        }
    }
};
