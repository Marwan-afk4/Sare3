<?php

namespace Tests\Feature\Api;

use App\Jobs\SendWhatsappMessage;
use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class KastanaOtpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.api_domain' => null,
            'services.kastana.url' => 'https://sms.kastana.mobi/service/sms.asmx/SendSMS',
            'services.kastana.username' => 'sareea',
            'services.kastana.password' => 'secret',
            'services.kastana.sender_id' => 'Sareea',
            'services.kastana.language' => 'English',
        ]);
    }

    public function test_otp_method_still_returns_backend_otp_when_kastana_is_selected(): void
    {
        AppSetting::setPhoneVerificationMethod('kastana');

        $this->getJson('/auth/otp-method')
            ->assertOk()
            ->assertJson(['method' => 'backend_otp']);

        $this->getJson('/driver/auth/otp-method')
            ->assertOk()
            ->assertJson(['method' => 'backend_otp']);
    }

    public function test_send_otp_uses_kastana_with_otp_body_and_phone_recipient(): void
    {
        AppSetting::setPhoneVerificationMethod('kastana');

        Http::fake([
            'sms.kastana.mobi/*' => Http::response('<long xmlns="http://crownit.com/">1</long>', 200),
        ]);

        $this->postJson('/send-otp', ['phone' => '+962791234567'])
            ->assertOk()
            ->assertJsonStructure(['message', 'isLogin']);

        $user = User::where('phone', '+962791234567')->first();
        $this->assertNotNull($user);
        $this->assertNotEmpty($user->otp_code);

        Http::assertSent(function ($request) use ($user) {
            parse_str(parse_url($request->url(), PHP_URL_QUERY) ?? '', $query);

            return str_contains($request->url(), '/service/sms.asmx/SendSMS')
                && ($query['Recipients'] ?? null) === '+962791234567'
                && ($query['SenderID'] ?? null) === 'Sareea'
                && ($query['Language'] ?? null) === 'English'
                && filled($query['Body'] ?? null)
                && str_contains($query['Body'], $user->otp_code);
        });
    }

    public function test_kastana_decodes_url_encoded_password_and_keeps_plus_on_recipient(): void
    {
        config(['services.kastana.password' => 'S@ree@20%2B26']);

        Http::fake([
            'sms.kastana.mobi/*' => Http::response('<long xmlns="http://crownit.com/">18618626</long>', 200),
        ]);

        $response = app(\App\Services\KastanaSmsService::class)->send('+962775126712', 'test bel plus');

        $this->assertTrue(app(\App\Services\KastanaSmsService::class)->succeeded($response));

        Http::assertSent(function ($request) {
            parse_str(parse_url($request->url(), PHP_URL_QUERY) ?? '', $query);

            return ($query['Recipients'] ?? null) === '+962775126712'
                && ($query['Password'] ?? null) === 'S@ree@20+26';
        });
    }

    public function test_send_otp_uses_whatsapp_when_backend_otp_is_selected(): void
    {
        AppSetting::setPhoneVerificationMethod('backend_otp');
        Bus::fake([SendWhatsappMessage::class]);
        Http::fake();

        $this->postJson('/send-otp', ['phone' => '+962791234567'])->assertOk();

        Bus::assertDispatched(SendWhatsappMessage::class);
        Http::assertNothingSent();
    }
}
