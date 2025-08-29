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
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();

            // المستخدم اللي عمل الدعوة
            $table->foreignId('referrer_id')->constrained('users')->onDelete('cascade');

            // المستخدم الجديد اللي دخل من اللينك
            $table->foreignId('referred_user_id')->nullable()->constrained('users')->onDelete('cascade');

            // توكن مميز للينك
            $table->string('token')->unique();

            // تاريخ القبول (لما الـ referred يعمل تسجيل)
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
