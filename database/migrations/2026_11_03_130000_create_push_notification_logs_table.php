<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recipient_id')->nullable();
            $table->string('recipient_role', 20)->nullable();
            $table->unsignedBigInteger('ride_id')->nullable();
            $table->unsignedBigInteger('delivery_id')->nullable();
            $table->string('msg_type', 64)->nullable();
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->string('status', 20);
            $table->boolean('has_token')->default(false);
            $table->string('token_prefix', 24)->nullable();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->string('fcm_message_id')->nullable();
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->boolean('high_priority')->default(false);
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamps();

            $table->index('ride_id');
            $table->index('delivery_id');
            $table->index('msg_type');
            $table->index('status');
            $table->index('created_at');
            $table->index(['recipient_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_notification_logs');
    }
};
