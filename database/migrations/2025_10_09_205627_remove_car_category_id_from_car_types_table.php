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
        // Check if foreign key exists and drop it
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'car_types' 
            AND COLUMN_NAME = 'car_category_id' 
            AND CONSTRAINT_NAME != 'PRIMARY'
        ");
        
        if (!empty($foreignKeys)) {
            Schema::table('car_types', function (Blueprint $table) {
                $table->dropForeign(['car_category_id']);
            });
        }
        
        // Drop the column if it exists
        if (Schema::hasColumn('car_types', 'car_category_id')) {
            Schema::table('car_types', function (Blueprint $table) {
                $table->dropColumn('car_category_id');
            });
        }
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
