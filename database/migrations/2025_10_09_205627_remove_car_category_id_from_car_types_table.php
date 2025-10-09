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
            $table->dropForeign(['car_category_id']);
            $table->dropColumn('car_category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_types', function (Blueprint $table) {
            $table->foreignId('car_category_id')->nullable()->constrained('car_categories')->onDelete('cascade');
        });
    }
};
