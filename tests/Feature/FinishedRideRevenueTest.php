<?php

namespace Tests\Feature;

use App\Models\Ride;
use App\Services\FinishedRideRevenueService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FinishedRideRevenueTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('rides');
        Schema::create('rides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status')->default('pending');
            $table->decimal('calculated_final_price', 8, 2)->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Schema::dropIfExists('rides');

        parent::tearDown();
    }

    public function test_it_sums_finished_ride_fares_by_the_month_they_ended(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-18 12:00:00'));

        $this->ride('finshed', 100, '2026-02-10 08:00:00');
        $this->ride('finshed', 40, '2026-03-02 08:00:00');
        $this->ride('finshed', 10.5, '2026-03-16 08:00:00');
        $this->ride('finshed', 7, '2025-12-20 08:00:00');
        $this->ride('completed', 999, '2026-03-04 08:00:00');
        $this->ride('cancelled', 50, '2026-03-05 08:00:00');
        $this->ride('finshed', null, '2026-03-06 08:00:00');

        $endedOnly = $this->ride('finshed', 20, null);
        DB::table('rides')->where('id', $endedOnly->id)->update([
            'completed_at' => null,
            'ended_at' => '2026-04-03 09:00:00',
        ]);

        $updatedOnly = $this->ride('finshed', 30, null);
        DB::table('rides')->where('id', $updatedOnly->id)->update([
            'completed_at' => null,
            'ended_at' => null,
            'updated_at' => '2026-05-02 09:00:00',
        ]);

        $revenue = app(FinishedRideRevenueService::class)->forYear(2026);

        $this->assertSame(2026, $revenue['year']);
        $this->assertTrue($revenue['is_current_year']);
        $this->assertCount(12, $revenue['months']);
        $this->assertSame(0.0, $revenue['months'][0]['total']);
        $this->assertSame(100.0, $revenue['months'][1]['total']);
        $this->assertSame(1, $revenue['months'][1]['rides']);
        $this->assertSame(50.5, $revenue['months'][2]['total']);
        $this->assertSame(3, $revenue['months'][2]['rides']);
        $this->assertSame(20.0, $revenue['months'][3]['total']);
        $this->assertSame(30.0, $revenue['months'][4]['total']);
        $this->assertSame(200.5, $revenue['year_total']);
        $this->assertSame(6, $revenue['year_rides']);
        $this->assertSame(33.42, $revenue['average_fare']);
        $this->assertTrue($revenue['months'][1]['is_best']);
        $this->assertTrue($revenue['months'][2]['is_current']);
        $this->assertSame(-49.5, $revenue['change_percent']);
        $this->assertSame([2026, 2025], $revenue['available_years']);

        $previousYear = app(FinishedRideRevenueService::class)->forYear(2025);

        $this->assertFalse($previousYear['is_current_year']);
        $this->assertNull($previousYear['this_month']);
        $this->assertNull($previousYear['change_percent']);
        $this->assertSame(7.0, $previousYear['months'][11]['total']);
        $this->assertSame(7.0, $previousYear['year_total']);
    }

    private function ride(string $status, ?float $price, ?string $completedAt): Ride
    {
        return Ride::create([
            'user_id' => 1,
            'status' => $status,
            'calculated_final_price' => $price,
            'completed_at' => $completedAt,
        ]);
    }
}
