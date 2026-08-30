<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\Transaction;
use App\Models\User;
use App\Services\SignupGiftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SignupGiftTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.api_domain' => null]);
        config(['app.dashboard_domain' => null]);
    }

    private function enableGift(float $amount = 5): void
    {
        AppSetting::setSignupGiftEnabled(true);
        AppSetting::setSignupGiftAmount($amount);
    }

    public function test_first_post_name_credits_the_configured_gift(): void
    {
        $this->enableGift(7.5);

        $user = User::factory()->create([
            'name' => null,
            'phone' => '+962790000001',
            'role' => 'user',
            'wallet' => null,
        ]);

        $response = $this->postJson('/post-name', [
            'phone' => '+962790000001',
            'name' => 'Ahmad',
        ]);

        $response->assertOk()
            ->assertJsonPath('signup_gift.granted', true)
            ->assertJsonPath('signup_gift.amount', 7.5)
            ->assertJsonPath('wallet', 7.5);

        $user->refresh();

        $this->assertEquals(7.5, (float) $user->wallet);
        $this->assertNotNull($user->signup_gift_received_at);
        $this->assertEquals(7.5, (float) $user->signup_gift_amount);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount' => 7.5,
            'description' => SignupGiftService::TRANSACTION_DESCRIPTION,
        ]);
    }

    public function test_post_name_does_not_grant_when_user_already_has_a_name(): void
    {
        $this->enableGift(5);

        $user = User::factory()->create([
            'name' => 'Existing User',
            'phone' => '+962790000002',
            'role' => 'user',
            'wallet' => 3,
        ]);

        $this->postJson('/post-name', [
            'phone' => '+962790000002',
            'name' => 'Existing User',
        ])->assertOk()
            ->assertJsonPath('signup_gift.granted', false);

        $user->refresh();
        $this->assertEquals(3, (float) $user->wallet);
        $this->assertNull($user->signup_gift_received_at);
    }

    public function test_post_name_does_not_overwrite_an_existing_wallet(): void
    {
        $this->enableGift(5);

        $user = User::factory()->create([
            'name' => 'Wallet User',
            'phone' => '+962790000003',
            'role' => 'user',
            'wallet' => 20,
        ]);

        $this->postJson('/post-name', [
            'phone' => '+962790000003',
            'name' => 'Wallet User',
        ])->assertOk();

        $user->refresh();
        $this->assertEquals(20, (float) $user->wallet);
    }

    public function test_gift_is_not_granted_twice(): void
    {
        $this->enableGift(5);
        $service = app(SignupGiftService::class);

        $user = User::factory()->create([
            'name' => 'Sara',
            'role' => 'user',
            'wallet' => 0,
        ]);

        $first = $service->grantIfEligible($user);
        $second = $service->grantIfEligible($user->fresh());

        $this->assertNotNull($first);
        $this->assertNull($second);
        $this->assertEquals(5, (float) $user->fresh()->wallet);
        $this->assertEquals(1, Transaction::where('user_id', $user->id)->count());
    }

    public function test_disabled_or_zero_amount_does_not_auto_grant(): void
    {
        $user = User::factory()->create(['role' => 'user', 'wallet' => 0]);

        AppSetting::setSignupGiftEnabled(false);
        AppSetting::setSignupGiftAmount(10);
        $this->assertNull(app(SignupGiftService::class)->grantIfEligible($user));

        AppSetting::setSignupGiftEnabled(true);
        AppSetting::setSignupGiftAmount(0);
        $this->assertNull(app(SignupGiftService::class)->grantIfEligible($user->fresh()));
    }

    public function test_admin_can_grant_to_a_user_who_missed_the_gift(): void
    {
        AppSetting::setSignupGiftEnabled(false);
        AppSetting::setSignupGiftAmount(4);

        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user', 'wallet' => 1, 'name' => 'Lina']);

        $result = app(SignupGiftService::class)->grantManually($user, $admin);

        $this->assertEquals(4, $result['amount']);
        $user->refresh();
        $this->assertEquals(5, (float) $user->wallet);
        $this->assertEquals($admin->id, $user->signup_gift_granted_by);
        $this->assertNotNull($user->signup_gift_received_at);
    }

    public function test_admin_page_lists_users_without_a_gift(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        \Spatie\Permission\Models\Permission::firstOrCreate([
            'name' => 'إدارة المستخدمين',
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $admin->givePermissionTo('إدارة المستخدمين');

        $pending = User::factory()->create(['role' => 'user', 'name' => 'Pending Gift User']);
        $gifted = User::factory()->create([
            'role' => 'user',
            'name' => 'Already Gifted User',
            'signup_gift_received_at' => now(),
            'signup_gift_amount' => 5,
        ]);

        $response = $this->actingAs($admin)->get(route('signup-gifts.index'));

        $response->assertOk();
        $response->assertSee('Pending Gift User');
        $response->assertDontSee('Already Gifted User');
    }
}
