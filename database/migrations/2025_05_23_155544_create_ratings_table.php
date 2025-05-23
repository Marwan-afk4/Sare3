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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rater_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('ratee_id')->constrained('users')->onDelete('cascade');
            $table->integer('rate')->default(0);
            $table->string('comment')->nullable();
            $table->enum('ratee_type', ['user', 'driver'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
