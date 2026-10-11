<?php

namespace Tests\Feature;

use App\Models\Ride;
use App\Models\RideOffer;
use App\Models\User;
use App\Services\RideOfferService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RideSearchStopsWhenPassengerCancelsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('ride_offers');
        Schema::dropIfExists('chats');
        Schema::dropIfExists('rides');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('password');
            $table->string('role')->nullable();
            $table->string('referral_code')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('rides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ride_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('ride_offers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ride_id');
            $table->unsignedBigInteger('driver_id');
            $table->timestamp('offered_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->string('response')->default('pending');
            $table->unsignedInteger('response_seconds')->nullable();
            $table->unsignedTinyInteger('attempt')->default(1);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('ride_offers');
        Schema::dropIfExists('chats');
        Schema::dropIfExists('rides');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_a_cancelled_ride_is_not_offered_to_another_captain(): void
    {
        $ride = $this->makeRide('cancelled', 10);

        $claimed = $ride->updateWhileAwaiting([
            'driver_id' => 20,
            'status' => 'pending',
        ], true, 10);

        $ride->refresh();

        $this->assertFalse($claimed);
        $this->assertSame('cancelled', $ride->status->value);
        $this->assertSame(10, (int) $ride->driver_id);
        $this->assertNull(app(RideOfferService::class)->recordOffer($ride, 20, 1, 'reassignment'));
        $this->assertSame(0, RideOffer::query()->count());
    }

    public function test_an_accepted_ride_is_not_put_back_into_search(): void
    {
        $ride = $this->makeRide('accepted', 10);

        $claimed = $ride->updateWhileAwaiting([
            'driver_id' => 20,
            'status' => 'pending',
        ], true, 10);

        $ride->refresh();

        $this->assertFalse($claimed);
        $this->assertSame('accepted', $ride->status->value);
        $this->assertSame(10, (int) $ride->driver_id);
    }

    public function test_a_second_search_cannot_replace_the_captain_already_offered_the_ride(): void
    {
        $ride = $this->makeRide('pending', null);

        $first = $ride->updateWhileAwaiting([
            'driver_id' => 20,
            'status' => 'pending',
        ], true, null);

        $second = $ride->updateWhileAwaiting([
            'driver_id' => 30,
            'status' => 'pending',
        ], true, null);

        $ride->refresh();

        $this->assertTrue($first);
        $this->assertFalse($second);
        $this->assertSame(20, (int) $ride->driver_id);
        $this->assertSame('pending', $ride->status->value);
    }

    private function makeRide(string $status, ?int $driverId): Ride
    {
        $passenger = User::create([
            'name' => 'Passenger',
            'email' => 'passenger-' . $status . '@example.com',
            'phone' => '962700000001',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        return Ride::create([
            'user_id' => $passenger->id,
            'driver_id' => $driverId,
            'status' => $status,
        ]);
    }
}
