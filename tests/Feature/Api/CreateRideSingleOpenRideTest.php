<?php

namespace Tests\Feature\Api;

use App\Enums\RideStatus;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CreateRideSingleOpenRideTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('rides');
        Schema::dropIfExists('car_categories');
        Schema::dropIfExists('zones');
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

        Schema::create('zones', function (Blueprint $table) {
            $table->id();
        });

        Schema::create('car_categories', function (Blueprint $table) {
            $table->id();
        });

        Schema::create('rides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('status')->default('pending');
            $table->decimal('pickup_lat', 10, 7)->nullable();
            $table->decimal('pickup_lng', 10, 7)->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('rides');
        Schema::dropIfExists('car_categories');
        Schema::dropIfExists('zones');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_passenger_with_an_open_ride_cannot_create_another(): void
    {
        $user = $this->makePassenger();

        foreach ([
            RideStatus::Pending,
            RideStatus::Accepted,
            RideStatus::WaitingUser,
            RideStatus::Arrived,
            RideStatus::InProgress,
        ] as $status) {
            Ride::query()->where('user_id', $user->id)->delete();

            Ride::create([
                'user_id' => $user->id,
                'status' => $status->value,
                'pickup_lat' => 33.3152,
                'pickup_lng' => 44.3661,
            ]);

            $response = $this->actingAs($user, 'sanctum')
                ->postJson($this->createRideUrl(), []);

            $response->assertStatus(409);
            $response->assertJson([
                'message' => 'You already have an open ride.',
            ]);
            $this->assertSame(1, Ride::where('user_id', $user->id)->count());
        }
    }

    public function test_passenger_can_request_a_ride_after_the_previous_one_is_closed(): void
    {
        $user = $this->makePassenger();

        foreach ([
            RideStatus::Completed,
            RideStatus::Cancelled,
            RideStatus::Rejected,
            RideStatus::Finshed,
        ] as $status) {
            Ride::query()->where('user_id', $user->id)->delete();

            Ride::create([
                'user_id' => $user->id,
                'status' => $status->value,
                'pickup_lat' => 33.3152,
                'pickup_lng' => 44.3661,
            ]);

            $response = $this->actingAs($user, 'sanctum')
                ->postJson($this->createRideUrl(), []);

            $this->assertNotSame(409, $response->getStatusCode());
            $response->assertJsonMissing([
                'message' => 'You already have an open ride.',
            ]);
        }
    }

    private function createRideUrl(): string
    {
        $domain = config('app.api_domain');

        return $domain ? "http://{$domain}/api/user/ride/create" : '/api/user/ride/create';
    }

    private function makePassenger(): User
    {
        return User::create([
            'name' => 'Passenger',
            'email' => 'passenger@example.com',
            'phone' => '07700000000',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
    }
}
