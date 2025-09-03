<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->string('room_id')->unique();
            $table->unsignedBigInteger('participant_1_id');
            $table->string('participant_1_type');
            $table->unsignedBigInteger('participant_2_id');
            $table->string('participant_2_type');
            $table->text('last_message')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['participant_1_id', 'participant_1_type']);
            $table->index(['participant_2_id', 'participant_2_type']);
            $table->index('room_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('chats');
    }
};