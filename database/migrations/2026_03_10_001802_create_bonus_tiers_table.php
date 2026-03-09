<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bonus_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable()->comment('Display name, e.g. "Bronze", "Silver"');
            $table->unsignedInteger('rides_count')->comment('Minimum completed rides to earn this bonus');
            $table->decimal('bonus_amount', 10, 2)->comment('Bonus amount credited to driver wallet');
            $table->enum('period_type', ['weekly', 'monthly', 'all_time'])->default('monthly');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bonus_tiers');
    }
};
