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
        Schema::create('wallet_requests_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_request_id')->constrained('wallet_requests')->onDelete('cascade');
            $table->foreignId('admin_id')->nullable()->constrained('users')->setNullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('users')->setNullOnDelete();
            $table->text('admin_message')->nullable();
            $table->text('driver_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_requests_messages');
    }
};
