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
            AND TABLE_NAME = 'car_types' 
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            AND CONSTRAINT_NAME LIKE '%car_model_id%'
        ", [$dbname]);
        
        Schema::table('car_types', function (Blueprint $table) use ($fkExists) {
            // Drop the foreign key if it exists
            if ($fkExists[0]->count > 0) {
                $table->dropForeign(['car_model_id']);
            }
            
            // Drop the column if it exists
            if (Schema::hasColumn('car_types', 'car_model_id')) {
                $table->dropColumn('car_model_id');
            }
            
            // Add car_category_id foreign key if it doesn't exist
            if (!Schema::hasColumn('car_types', 'car_category_id')) {
                $table->foreignId('car_category_id')
                    ->after('id')
                    ->constrained('car_categories')
                    ->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_types', function (Blueprint $table) {
            // Drop the car_category_id foreign key and column
            $table->dropForeign(['car_category_id']);
            $table->dropColumn('car_category_id');
            
            // Add back car_model_id
            $table->foreignId('car_model_id')
                ->nullable()
                ->after('id')
                ->constrained('car_models')
                ->nullOnDelete();
        });
    }
};
