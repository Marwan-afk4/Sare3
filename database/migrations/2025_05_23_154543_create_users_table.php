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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable()->unique();
            $table->string('phone')->unique();
            $table->string('password');
            $table->string('image')->nullable();
            $table->string('activity')->default('active');
            $table->float('wallet', 20, 3)->nullable();
            $table->enum('role', ['user', 'driver', 'admin']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
