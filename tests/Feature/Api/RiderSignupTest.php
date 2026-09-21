<?php

namespace Tests\Feature\Api;

use App\Jobs\SendWhatsappMessage;
use App\Models\RiderVehicle;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RiderSignupTest extends TestCase
{
    private const PHONE = '+962791234567';

    private const PNG = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.api_domain' => null]);
        Bus::fake([SendWhatsappMessage::class]);
        Http::fake();
        Storage::fake('public');
        $this->createSignupSchema();
    }

    private function signupPost(string $path, array $data)
    {
        return $this->postJson('http://sare3.tld/api'.$path, $data);
    }

    public function test_delivery_signup_survives_post_name_and_saves_the_rider_vehicle(): void
    {
        $this->signupPost('/driver/send-otp', [
            'phone' => self::PHONE,
            'role' => 'delivery',
            'vehicle_type' => 'bike',
        ])->assertOk()->assertJson([
            'role' => 'delivery',
            'vehicle_type' => 'bike',
        ]);

        $user = User::where('phone', self::PHONE)->first();
        $this->assertNotNull($user);
        $this->assertSame('delivery', $user->role);
        $this->assertDatabaseHas('rider_vehicles', [
            'rider_id' => $user->id,
            'type' => 'bike',
        ]);

        $this->signupPost('/driver/verify-otp', [
            'phone' => self::PHONE,
            'otp_code' => $user->otp_code,
        ])->assertOk()->assertJson([
            'role' => 'delivery',
            'vehicle_type' => 'bike',
        ]);

        // Captain app always calls this shared endpoint after OTP. It used to
        // overwrite role=driver, which hid the account from Delivery Agents.
        $this->signupPost('/driver/post-name', [
            'phone' => self::PHONE,
            'name' => 'Bike Rider',
            'fcm_token' => 'test-token',
        ])->assertOk()->assertJson([
            'role' => 'delivery',
            'vehicle_type' => 'bike',
        ]);

        $user->refresh();
        $this->assertSame('delivery', $user->role);
        $this->assertSame('Bike Rider', $user->name);

        $this->signupPost('/rider/store-vehicle', [
            'phone' => self::PHONE,
            'documents' => [
                'rider_image' => self::PNG,
                'identity_image' => self::PNG,
                'vehicle_image' => self::PNG,
            ],
        ])->assertOk();

        $user->refresh();
        $this->assertSame('delivery', $user->role);
        $this->assertSame('active', $user->activity->value);

        $vehicle = RiderVehicle::where('rider_id', $user->id)->first();
        $this->assertNotNull($vehicle);
        $this->assertSame('bike', $vehicle->type->value);
        $this->assertNotEmpty($vehicle->rider_image);
        $this->assertNotEmpty($vehicle->identity_image);
        $this->assertNotEmpty($vehicle->vehicle_image);

        $this->assertSame(1, User::where('role', 'delivery')->count());
    }

    public function test_role_rider_is_stored_as_delivery(): void
    {
        $this->signupPost('/driver/send-otp', [
            'phone' => self::PHONE,
            'role' => 'rider',
            'vehicle_type' => 'motorcycle',
        ])->assertOk()->assertJson([
            'role' => 'delivery',
            'vehicle_type' => 'motorcycle',
        ]);

        $user = User::where('phone', self::PHONE)->first();
        $this->assertSame('delivery', $user->role);

        $this->signupPost('/driver/post-name', [
            'phone' => self::PHONE,
            'name' => 'Moto Rider',
            'fcm_token' => 'test-token',
            'role' => 'rider',
            'vehicle_type' => 'motorcycle',
        ])->assertOk()->assertJson([
            'role' => 'delivery',
        ]);

        $this->assertSame('delivery', $user->fresh()->role);
        $this->assertDatabaseHas('rider_vehicles', [
            'rider_id' => $user->id,
            'type' => 'motorcycle',
        ]);
    }

    public function test_store_vehicle_recovers_an_incomplete_account_overwritten_as_driver(): void
    {
        $user = User::factory()->create([
            'phone' => self::PHONE,
            'role' => 'driver',
            'activity' => 'in_progress',
            'name' => 'Stuck Rider',
        ]);

        $this->signupPost('/rider/store-vehicle', [
            'phone' => self::PHONE,
            'vehicle_type' => 'bike',
            'documents' => [
                'rider_image' => self::PNG,
                'identity_image' => self::PNG,
                'vehicle_image' => self::PNG,
            ],
        ])->assertOk();

        $user->refresh();
        $this->assertSame('delivery', $user->role);
        $this->assertDatabaseHas('rider_vehicles', [
            'rider_id' => $user->id,
            'type' => 'bike',
        ]);
    }

    /**
     * sqlite RefreshDatabase cannot run this project's MySQL-only migrations,
     * so the signup tables are created directly for this test.
     */
    private function createSignupSchema(): void
    {
        Schema::dropIfExists('rider_vehicles');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('otp_limits');
        Schema::dropIfExists('app_settings');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->string('phone')->nullable()->unique();
            $table->string('password')->nullable();
            $table->string('image')->nullable();
            $table->string('activity')->nullable()->default('active');
            $table->float('wallet', 20, 3)->nullable();
            $table->string('role', 32)->default('user');
            $table->string('name')->nullable();
            $table->string('remember_token')->nullable();
            $table->unsignedInteger('otp_limit')->nullable();
            $table->unsignedInteger('otp_used')->nullable()->default(0);
            $table->string('otp_code')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->boolean('phone_verified')->nullable()->default(false);
            $table->string('gender')->nullable();
            $table->string('fcm_token')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('status')->nullable()->default('pending');
            $table->string('referral_code')->nullable();
            $table->timestamp('signup_gift_received_at')->nullable();
            $table->decimal('signup_gift_amount', 10, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('rider_vehicles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rider_id');
            $table->string('type');
            $table->string('rider_image')->nullable();
            $table->string('identity_image')->nullable();
            $table->string('vehicle_image')->nullable();
            $table->string('license_image')->nullable();
            $table->timestamps();
        });

        Schema::create('otp_limits', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->unsignedBigInteger('otp_limit')->nullable();
            $table->timestamps();
        });

        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }
}
