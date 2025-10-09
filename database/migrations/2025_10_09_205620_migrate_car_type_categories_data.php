<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing car_category_id relationships to the pivot table
        DB::statement('
            INSERT INTO car_category_car_type (car_category_id, car_type_id, created_at, updated_at)
            SELECT car_category_id, id, NOW(), NOW()
            FROM car_types
            WHERE car_category_id IS NOT NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore car_category_id from pivot table (take the first category for each type)
        DB::statement('
            UPDATE car_types ct
            INNER JOIN (
                SELECT car_type_id, MIN(car_category_id) as car_category_id
                FROM car_category_car_type
                GROUP BY car_type_id
            ) pivot ON ct.id = pivot.car_type_id
            SET ct.car_category_id = pivot.car_category_id
        ');
    }
};

