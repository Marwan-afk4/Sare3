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
        Schema::create('cancelation_rides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ride_id')->constrained('rides')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cancelation_policy_id')->nullable()->constrained('cancellation_policies')->nullOnDelete();
            $table->enum('canceled_by', ['user', 'driver', 'system'])->nullable();

            $table->enum('penalty_applied', ['yes', 'no'])->default('no');
            $table->decimal('penalty_amount', 10, 2)->nullable();

            $table->timestamp('canceled_at')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cancelation_rides');
    }
};
