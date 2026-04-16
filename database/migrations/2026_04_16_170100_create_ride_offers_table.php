<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Keeps an audit trail of every captain a ride was offered to and how they
     * reacted (accepted / rejected / ignored / cancelled after accepting /
     * cancelled by the passenger before they had a chance to respond).
     *
     * This is what the admin dashboard uses to answer questions like:
     *  - "Which captains saw this ride before it was accepted?"
     *  - "Which rides were accepted after more than 60 seconds?"
     *  - "Which rides cycled through many captains with no accept?"
     */
    public function up(): void
    {
        Schema::create('ride_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ride_id')->constrained('rides')->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();

            $table->timestamp('offered_at')->useCurrent();
            $table->timestamp('responded_at')->nullable();

            // pending | accepted | rejected | ignored |
            // cancelled_after_accept | cancelled_by_user
            $table->string('response', 32)->default('pending');

            // Seconds between offered_at and responded_at. Duplicated here so
            // filters / reports don't need to recompute every time.
            $table->unsignedInteger('response_seconds')->nullable();

            // Which pass through the driver list this was (useful for the
            // round-robin cycling fallback that kicks in after 12 rejections).
            $table->unsignedTinyInteger('attempt')->default(1);

            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['ride_id', 'response']);
            $table->index(['driver_id', 'response']);
            $table->index('offered_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ride_offers');
    }
};
