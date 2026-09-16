<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Audit trail of every rider a delivery was offered to and how they
     * reacted. Mirrors `ride_offers`.
     */
    public function up(): void
    {
        Schema::create('delivery_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained('deliveries')->cascadeOnDelete();
            $table->foreignId('rider_id')->constrained('users')->cascadeOnDelete();

            $table->timestamp('offered_at')->useCurrent();
            $table->timestamp('responded_at')->nullable();

            // pending | accepted | rejected | ignored |
            // cancelled_after_accept | cancelled_by_user
            $table->string('response', 32)->default('pending');
            $table->unsignedInteger('response_seconds')->nullable();
            $table->unsignedTinyInteger('attempt')->default(1);
            $table->text('note')->nullable();

            $table->decimal('rider_lat', 10, 7)->nullable();
            $table->decimal('rider_lng', 10, 7)->nullable();

            $table->timestamps();

            $table->index(['delivery_id', 'response']);
            $table->index(['rider_id', 'response']);
            $table->index('offered_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_offers');
    }
};
