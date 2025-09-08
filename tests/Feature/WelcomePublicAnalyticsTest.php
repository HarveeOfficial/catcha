<?php

namespace Tests\Feature;

use App\Models\FishCatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WelcomePublicAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_page_shows_public_summary_cards(): void
    {
        FishCatch::factory()->count(2)->create(['quantity' => 3, 'count' => 2]);
        $resp = $this->get('/');
        $resp->assertOk();
        $resp->assertSee('Live Public Summary');
        $resp->assertSee('Total Catches');
    }
}
