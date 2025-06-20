<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('point_type', ['pickup', 'dropoff' , 'driverLocation']);
            $table->decimal('latitude', 10,8);
            $table->decimal('longitude',10,8);
            $table->timestamps();
        });
        DB::statement("
            ALTER TABLE points
            ADD location POINT
            GENERATED ALWAYS AS (
                ST_SRID(POINT(longitude, latitude), 4326)
            ) STORED NOT NULL
        ");

        // Add spatial index
        DB::statement('CREATE SPATIAL INDEX location_index ON points(location)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('points');
    }
};
