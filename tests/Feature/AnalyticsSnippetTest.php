<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsSnippetTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_includes_analytics_when_config_present(): void
    {
        config(['services.analytics.measurement_id' => 'G-TEST123456']);

        $resp = $this->get('/');

        $resp->assertOk();
        $resp->assertSee('G-TEST123456');
        $resp->assertSee('googletagmanager.com/gtag/js?id=G-TEST123456');
    }

    public function test_welcome_excludes_analytics_when_config_missing(): void
    {
        config(['services.analytics.measurement_id' => null]);

        $resp = $this->get('/');

        $resp->assertOk();
        $resp->assertDontSee('googletagmanager.com/gtag/js');
    }
}
