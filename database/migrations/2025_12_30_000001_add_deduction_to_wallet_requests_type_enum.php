<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // SQLite doesn't enforce ENUM types the same way; no schema change needed there.
        if ($driver === 'sqlite') {
            return;
        }

        // MySQL / MariaDB: expand ENUM to include 'deduction'
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE wallet_requests MODIFY type ENUM('withdraw','deposit','deduction') NOT NULL");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE wallet_requests MODIFY type ENUM('withdraw','deposit') NOT NULL");
        }
    }
};


