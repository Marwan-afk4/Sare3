<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Flag rides that the passenger cancelled while the request was still
     * cycling through captains with no one accepting. Admin dashboard gets
     * a dedicated filter for this case so support can review why captains
     * are ignoring those requests.
     */
    public function up(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            $table->boolean('cancelled_before_accept')->default(false)->after('status');
            $table->index('cancelled_before_accept');
        });
    }

    public function down(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            $table->dropIndex(['cancelled_before_accept']);
            $table->dropColumn('cancelled_before_accept');
        });
    }
};
