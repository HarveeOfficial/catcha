<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Guidance;

class GuidanceApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--no-interaction' => true]);
    }

    public function test_new_guidance_is_pending_and_hidden(): void
    {
        $expert = User::factory()->create(['role'=>'expert']);
        $this->actingAs($expert)->post(route('guidances.store'), [
            'title' => 'Test Guidance',
            'content' => 'Content',
            'type' => 'best_practice'
        ])->assertRedirect();
        $g = Guidance::first();
        $this->assertEquals('pending', $g->status);
        $this->assertFalse($g->active);
    }

    public function test_admin_can_approve_guidance(): void
    {
        $expert = User::factory()->create(['role'=>'expert']);
        $admin = User::factory()->create(['role'=>'admin']);
        $g = Guidance::create([
            'title' => 'Pending G',
            'content' => 'C',
            'type' => 'best_practice',
            'status' => 'pending',
            'active' => false
        ]);
        $this->actingAs($admin)->post(route('guidances.approve',$g))->assertRedirect();
        $g->refresh();
        $this->assertEquals('approved',$g->status);
        $this->assertTrue($g->active);
        $this->assertNotNull($g->approved_at);
    }

    public function test_admin_can_reject_guidance(): void
    {
        $admin = User::factory()->create(['role'=>'admin']);
        $g = Guidance::create([
            'title' => 'Pending G2',
            'content' => 'C',
            'type' => 'best_practice',
            'status' => 'pending',
            'active' => false
        ]);
        $this->actingAs($admin)->post(route('guidances.reject',$g), ['reason'=>'Not appropriate'])->assertRedirect();
        $g->refresh();
        $this->assertEquals('rejected',$g->status);
        $this->assertFalse($g->active);
        $this->assertEquals('Not appropriate',$g->rejected_reason);
    }
}
