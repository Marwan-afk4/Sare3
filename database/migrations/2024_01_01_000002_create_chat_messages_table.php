<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('sender_id');
            $table->string('sender_type');
            $table->unsignedBigInteger('receiver_id');
            $table->string('receiver_type');
            $table->text('message');
            $table->enum('status', ['sent', 'delivered', 'read'])->default('sent');
            $table->string('firebase_message_id')->nullable();
            $table->timestamps();

            $table->index(['sender_id', 'sender_type']);
            $table->index(['receiver_id', 'receiver_type']);
            $table->index('chat_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_messages');
    }
};