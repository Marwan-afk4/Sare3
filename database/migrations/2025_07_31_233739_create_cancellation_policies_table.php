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
        Schema::create('cancellation_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Standard Policy", "Late Cancel Fee"
            $table->integer('free_cancellation_minutes')->nullable(); // e.g., cancel within 5 mins of booking
            $table->decimal('penalty_amount', 10, 2)->nullable(); // fixed penalty
            $table->decimal('penalty_percent', 5, 2)->nullable(); // optional: percent of estimated fare
            $table->text('description')->nullable(); // shown to users
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cancellation_policies');
    }
};
