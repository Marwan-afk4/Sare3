<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_api_usages', function (Blueprint $table) {
            $table->id();
            $table->date('usage_date');
            $table->string('sku', 64);
            $table->unsignedInteger('requests')->default(0);
            $table->unsignedInteger('units')->default(0);
            $table->timestamps();

            $table->unique(['usage_date', 'sku']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_api_usages');
    }
};
