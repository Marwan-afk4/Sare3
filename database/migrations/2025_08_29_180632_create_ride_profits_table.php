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
        Schema::create('ride_profits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ride_id')->constrained('rides')->onDelete('cascade');
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
            $table->decimal('total_fare', 10, 2);
            $table->decimal('admin_profit_percentage', 5, 2);
            $table->decimal('admin_profit_amount', 10, 2);
            $table->decimal('driver_amount', 10, 2);
            $table->timestamp('processed_at');
            $table->timestamps();

            $table->index(['driver_id', 'processed_at']);
            $table->index(['processed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ride_profits');
    }
};
