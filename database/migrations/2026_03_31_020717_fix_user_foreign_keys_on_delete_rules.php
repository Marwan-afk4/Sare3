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
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);
            $table->dropForeign(['user_id']);
            $table->foreign('driver_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('wallet_requests', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);
            $table->foreign('driver_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('wallet_requests_messages', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);
            $table->dropForeign(['admin_id']);
            $table->foreign('driver_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('admin_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
