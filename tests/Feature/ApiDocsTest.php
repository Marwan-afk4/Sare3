<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiDocsTest extends TestCase
{
    public function test_serves_the_swagger_ui(): void
    {
        $this->get(route('docs.api'))
            ->assertSuccessful()
            ->assertSee('swagger-ui', false)
            ->assertSee('/docs/openapi.yaml', false);
    }

    public function test_serves_the_openapi_specification_using_app_url(): void
    {
        $apiBaseUrl = rtrim((string) config('app.url'), '/').'/api';

        $this->get(route('docs.openapi'))
            ->assertSuccessful()
            ->assertSee('Sare3 Delivery API', false)
            ->assertSee($apiBaseUrl, false)
            ->assertDontSee('{{API_BASE_URL}}', false)
            ->assertSee('/driver/send-otp', false)
            ->assertSee('/rider/required-docs', false)
            ->assertSee('/rider/store-vehicle', false)
            ->assertSee('/rider/delivery/accept', false)
            ->assertSee('/user/delivery/create', false)
            ->assertSee('/deliveries/{deliveryId}/tracking-data', false)
            ->assertSee('vehicle_type', false)
            ->assertSee('delivery.request.new', false);
    }
}
