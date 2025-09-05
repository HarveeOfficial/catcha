<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\FishCatch;
use App\Models\Species;

class CatchAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--no-interaction' => true]);
    }

    public function test_fisher_sees_only_own_catches_in_analytics(): void
    {
        $fisher = User::factory()->create(['role'=>'fisher']);
        $other = User::factory()->create(['role'=>'fisher']);
        $species = Species::factory()->create();
        FishCatch::factory()->create(['user_id'=>$fisher->id,'species_id'=>$species->id,'quantity'=>5,'count'=>10]);
        FishCatch::factory()->create(['user_id'=>$other->id,'species_id'=>$species->id,'quantity'=>9,'count'=>20]);

        $resp = $this->actingAs($fisher)->get(route('catches.analytics'));
        $resp->assertStatus(200);
        // Ensure total quantity reflects only fisher's own (5 not 14)
        $resp->assertSee('5.00');
        $resp->assertDontSee('14.00');
    }

    public function test_admin_sees_all_catches_in_analytics(): void
    {
        $admin = User::factory()->create(['role'=>'admin']);
        $species = Species::factory()->create();
        FishCatch::factory()->count(2)->create(['species_id'=>$species->id,'quantity'=>3,'count'=>5]);
        $resp = $this->actingAs($admin)->get(route('catches.analytics'));
        $resp->assertStatus(200);
        // Total qty should be 6.00
        $resp->assertSee('6.00');
    }
}
