<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            $table->decimal('from_lat', 15, 10)->change();
            $table->decimal('from_lng', 15, 10)->change();
            $table->decimal('to_lat', 15, 10)->change();
            $table->decimal('to_lng', 15, 10)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            $table->decimal('from_lat', 10, 7)->change();
            $table->decimal('from_lng', 10, 7)->change();
            $table->decimal('to_lat', 10, 7)->change();
            $table->decimal('to_lng', 10, 7)->change();
        });
    }
};
