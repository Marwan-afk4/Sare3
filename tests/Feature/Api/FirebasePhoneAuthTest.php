<?php

namespace Tests\Feature\Api;

use App\Models\AppSetting;
use App\Models\User;
use App\Services\FirebasePhoneAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FirebasePhoneAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.api_domain' => null]);
    }

    public function test_otp_method_returns_backend_otp_by_default(): void
    {
        $response = $this->getJson('/auth/otp-method');

        $response->assertOk()
            ->assertJson(['method' => 'backend_otp']);
    }

    public function test_otp_method_returns_firebase_otp_when_configured(): void
    {
        AppSetting::setPhoneVerificationMethod('firebase_otp');

        $response = $this->getJson('/auth/otp-method');

        $response->assertOk()
            ->assertJson(['method' => 'firebase_otp']);
    }

    public function test_firebase_login_creates_user_and_returns_verify_otp_response_shape(): void
    {
        AppSetting::setPhoneVerificationMethod('firebase_otp');

        $this->mock(FirebasePhoneAuthService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('verifyIdTokenAndGetPhone')
                ->once()
                ->with('valid-firebase-token')
                ->andReturn('+962791234567');
        });

        $response = $this->postJson('/firebase-phone-login', [
            'firebase_id_token' => 'valid-firebase-token',
            'phone'             => '962791234567',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'token',
                'user_otp_limit',
            ])
            ->assertJson([
                'message' => 'OTP verified successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'phone'          => '+962791234567',
            'phone_verified' => true,
            'status'         => 'approved',
        ]);
    }

    public function test_firebase_login_verifies_existing_user(): void
    {
        AppSetting::setPhoneVerificationMethod('firebase_otp');

        $user = User::factory()->create([
            'phone'          => '+962791234567',
            'phone_verified' => false,
            'status'         => 'pending',
            'otp_code'       => '123456',
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $this->mock(FirebasePhoneAuthService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('verifyIdTokenAndGetPhone')
                ->once()
                ->andReturn('+962791234567');
        });

        $response = $this->postJson('/firebase-phone-login', [
            'firebase_id_token' => 'valid-firebase-token',
            'phone'             => '+962791234567',
        ]);

        $response->assertOk()
            ->assertJson([
                'message'        => 'OTP verified successfully',
                'user_otp_limit' => $user->otp_limit,
            ]);

        $user->refresh();
        $this->assertTrue($user->phone_verified);
        $this->assertSame('approved', $user->status);
        $this->assertNull($user->otp_code);
        $this->assertNull($user->otp_expires_at);
    }

    public function test_firebase_login_rejects_phone_mismatch(): void
    {
        AppSetting::setPhoneVerificationMethod('firebase_otp');

        $this->mock(FirebasePhoneAuthService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('verifyIdTokenAndGetPhone')
                ->once()
                ->andReturn('+962791234567');
        });

        $response = $this->postJson('/firebase-phone-login', [
            'firebase_id_token' => 'valid-firebase-token',
            'phone'             => '+962799999999',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Phone number does not match Firebase verification.',
            ]);
    }

    public function test_firebase_login_rejects_when_backend_otp_is_active(): void
    {
        AppSetting::setPhoneVerificationMethod('backend_otp');

        $this->mock(FirebasePhoneAuthService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldNotReceive('verifyIdTokenAndGetPhone');
        });

        $response = $this->postJson('/firebase-phone-login', [
            'firebase_id_token' => 'valid-firebase-token',
            'phone'             => '+962791234567',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Firebase phone authentication is not the active verification method.',
            ]);
    }

    public function test_firebase_login_rejects_invalid_token(): void
    {
        AppSetting::setPhoneVerificationMethod('firebase_otp');

        $this->mock(FirebasePhoneAuthService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('verifyIdTokenAndGetPhone')
                ->once()
                ->andThrow(new \App\Exceptions\FirebasePhoneAuthException('Invalid or expired Firebase token.', 401));
        });

        $response = $this->postJson('/firebase-phone-login', [
            'firebase_id_token' => 'invalid-token',
            'phone'             => '+962791234567',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid or expired Firebase token.',
            ]);
    }

    public function test_firebase_login_validates_required_fields(): void
    {
        AppSetting::setPhoneVerificationMethod('firebase_otp');

        $response = $this->postJson('/firebase-phone-login', []);

        $response->assertStatus(422);
    }

    public function test_driver_otp_method_returns_backend_otp_by_default(): void
    {
        $response = $this->getJson('/driver/auth/otp-method');

        $response->assertOk()
            ->assertJson(['method' => 'backend_otp']);
    }

    public function test_driver_firebase_login_creates_driver_and_returns_verify_otp_response_shape(): void
    {
        AppSetting::setPhoneVerificationMethod('firebase_otp');

        $this->mock(FirebasePhoneAuthService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('verifyIdTokenAndGetPhone')
                ->once()
                ->with('valid-firebase-token')
                ->andReturn('+962791234568');
        });

        $response = $this->postJson('/driver/firebase-phone-login', [
            'firebase_id_token' => 'valid-firebase-token',
            'phone'             => '962791234568',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'token',
            ])
            ->assertJson([
                'message' => 'Phone number verified successfully',
            ])
            ->assertJsonMissing(['user_otp_limit']);

        $this->assertDatabaseHas('users', [
            'phone'          => '+962791234568',
            'role'           => 'driver',
            'phone_verified' => true,
            'activity'       => 'in_progress',
        ]);
    }

    public function test_driver_firebase_login_verifies_existing_driver(): void
    {
        AppSetting::setPhoneVerificationMethod('firebase_otp');

        $driver = User::factory()->create([
            'phone'          => '+962791234568',
            'role'           => 'driver',
            'activity'       => 'in_progress',
            'phone_verified' => false,
            'otp_code'       => '123456',
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $this->mock(FirebasePhoneAuthService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('verifyIdTokenAndGetPhone')
                ->once()
                ->andReturn('+962791234568');
        });

        $response = $this->postJson('/driver/firebase-phone-login', [
            'firebase_id_token' => 'valid-firebase-token',
            'phone'             => '+962791234568',
        ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Phone number verified successfully',
            ]);

        $driver->refresh();
        $this->assertTrue($driver->phone_verified);
        $this->assertNull($driver->otp_code);
        $this->assertNull($driver->otp_expires_at);
    }

    public function test_driver_firebase_login_rejects_when_backend_otp_is_active(): void
    {
        AppSetting::setPhoneVerificationMethod('backend_otp');

        $this->mock(FirebasePhoneAuthService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldNotReceive('verifyIdTokenAndGetPhone');
        });

        $response = $this->postJson('/driver/firebase-phone-login', [
            'firebase_id_token' => 'valid-firebase-token',
            'phone'             => '+962791234568',
        ]);

        $response->assertStatus(403);
    }
}
