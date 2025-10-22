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
        // Get database name
        $dbname = env('DB_DATABASE');
        
        // Check if foreign key exists
        $fkExists = \DB::select("
            SELECT COUNT(*) as count 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE CONSTRAINT_SCHEMA = ? 
            AND TABLE_NAME = 'car_models' 
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            AND CONSTRAINT_NAME LIKE '%car_categories_id%'
        ", [$dbname]);
        
        Schema::table('car_models', function (Blueprint $table) use ($fkExists) {
            // Drop the foreign key if it exists
            if ($fkExists[0]->count > 0) {
                $table->dropForeign(['car_categories_id']);
            }
            
            // Drop the column if it exists
            if (Schema::hasColumn('car_models', 'car_categories_id')) {
                $table->dropColumn('car_categories_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_models', function (Blueprint $table) {
            // Add back car_categories_id
            if (!Schema::hasColumn('car_models', 'car_categories_id')) {
                $table->foreignId('car_categories_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('car_categories')
                    ->onDelete('set null');
            }
        });
    }
};
