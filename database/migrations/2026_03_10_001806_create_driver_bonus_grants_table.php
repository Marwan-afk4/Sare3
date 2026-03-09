<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_bonus_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->enum('type', ['tier', 'manual'])->default('manual')
                ->comment('tier = auto-granted from milestone, manual = admin gave');
            $table->foreignId('bonus_tier_id')->nullable()->constrained('bonus_tiers')->nullOnDelete();
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete()
                ->comment('Admin user who manually granted the bonus');
            $table->string('note')->nullable();
            $table->unsignedInteger('rides_count')->default(0)->comment('Driver rides count at time of grant');
            $table->string('period_label')->nullable()->comment('e.g. "2026-03" for monthly period');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_bonus_grants');
    }
};
