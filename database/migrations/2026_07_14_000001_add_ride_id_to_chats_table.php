<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->foreignId('ride_id')
                ->nullable()
                ->after('room_id')
                ->constrained('rides')
                ->nullOnDelete();

            $table->index('ride_id');
        });
    }

    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->dropForeign(['ride_id']);
            $table->dropColumn('ride_id');
        });
    }
};
