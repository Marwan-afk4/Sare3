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
        Schema::table('rides', function (Blueprint $table) {
            $table->string('verification_code', 6)->nullable()->after('firebase_ride_id');
            $table->timestamp('verification_code_generated_at')->nullable()->after('verification_code');
            $table->boolean('verification_code_verified')->default(false)->after('verification_code_generated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            $table->dropColumn(['verification_code', 'verification_code_generated_at', 'verification_code_verified']);
        });
    }
};