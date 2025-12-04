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
        Schema::table('car_types', function (Blueprint $table) {
            $table->integer('type_year')->nullable()->after('type_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_types', function (Blueprint $table) {
            $table->dropColumn('type_year');
        });
    }
};
