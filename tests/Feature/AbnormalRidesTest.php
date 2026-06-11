<?php

namespace Tests\Feature;

use App\Models\Ride;
use App\Models\User;
use App\Models\RideOffer;
use App\Enums\RideStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AbnormalRidesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create the permission required by the middleware
        \Spatie\Permission\Models\Permission::firstOrCreate([
            'name' => 'إدارة الرحلات',
            'guard_name' => 'web'
        ]);
    }

    public function test_guests_cannot_access_abnormal_rides_page()
    {
        $response = $this->get(route('rides.abnormal'));

        $this->assertNotEquals(200, $response->getStatusCode());
    }

    public function test_authorized_user_can_access_abnormal_rides_page()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $user->givePermissionTo('إدارة الرحلات');

        $response = $this->actingAs($user)->get(route('rides.abnormal'));

        $response->assertStatus(200);
        $response->assertViewIs('rides.abnormal');
    }

    public function test_it_displays_multi_captain_rides()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $user->givePermissionTo('إدارة الرحلات');

        // Create a ride with 2 offers (multi-captain)
        $ride1 = Ride::create([
            'user_id' => $user->id,
            'status' => RideStatus::Pending->value,
            'pickup_lat' => 24.7136,
            'pickup_lng' => 46.6753,
            'pickup_address' => 'Pickup Location 1',
        ]);
        
        $driver1 = User::factory()->create(['role' => 'driver']);
        $driver2 = User::factory()->create(['role' => 'driver']);

        RideOffer::create([
            'ride_id' => $ride1->id,
            'driver_id' => $driver1->id,
            'offered_at' => now(),
            'response' => 'pending',
        ]);
        RideOffer::create([
            'ride_id' => $ride1->id,
            'driver_id' => $driver2->id,
            'offered_at' => now(),
            'response' => 'pending',
        ]);

        // Create a ride with 1 offer (not multi-captain)
        $ride2 = Ride::create([
            'user_id' => $user->id,
            'status' => RideStatus::Pending->value,
            'pickup_lat' => 24.7136,
            'pickup_lng' => 46.6753,
            'pickup_address' => 'Pickup Location 2',
        ]);
        
        RideOffer::create([
            'ride_id' => $ride2->id,
            'driver_id' => $driver1->id,
            'offered_at' => now(),
            'response' => 'pending',
        ]);

        $response = $this->actingAs($user)->get(route('rides.abnormal', ['active_tab' => 'multi_captain']));

        $response->assertStatus(200);
        $response->assertSee('<td>#' . $ride1->id . '</td>', false);
        $response->assertDontSee('<td>#' . $ride2->id . '</td>', false);
    }

    public function test_it_displays_suspicious_rides()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $user->givePermissionTo('إدارة الرحلات');
        $driver = User::factory()->create(['role' => 'driver']);

        // Ride A: Cancelled after starting
        $rideA = Ride::create([
            'user_id' => $user->id,
            'driver_id' => $driver->id,
            'status' => RideStatus::Cancelled->value,
            'trip_started_at' => now()->subMinutes(10),
            'pickup_lat' => 24.7136,
            'pickup_lng' => 46.6753,
            'pickup_address' => 'Pickup Location A',
        ]);

        // Ride B: Completed abnormally quickly (e.g., in 30 seconds)
        $rideB = Ride::create([
            'user_id' => $user->id,
            'driver_id' => $driver->id,
            'status' => RideStatus::Completed->value,
            'trip_started_at' => now()->subSeconds(30),
            'completed_at' => now(),
            'pickup_lat' => 24.7136,
            'pickup_lng' => 46.6753,
            'pickup_address' => 'Pickup Location B',
        ]);

        // Ride C: Completed normally (e.g., in 10 minutes)
        $rideC = Ride::create([
            'user_id' => $user->id,
            'driver_id' => $driver->id,
            'status' => RideStatus::Completed->value,
            'trip_started_at' => now()->subMinutes(10),
            'completed_at' => now(),
            'pickup_lat' => 24.7136,
            'pickup_lng' => 46.6753,
            'pickup_address' => 'Pickup Location C',
        ]);

        $response = $this->actingAs($user)->get(route('rides.abnormal', ['active_tab' => 'suspicious', 'duration_threshold' => 2]));

        $response->assertStatus(200);
        $response->assertSee('<td>#' . $rideA->id . '</td>', false);
        $response->assertSee('<td>#' . $rideB->id . '</td>', false);
        $response->assertDontSee('<td>#' . $rideC->id . '</td>', false);
    }
}
