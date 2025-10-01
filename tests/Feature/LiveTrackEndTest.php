<?php

namespace Tests\Feature;

use App\Models\LiveTrack;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;

class LiveTrackEndTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_end_track_with_write_key(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $track = LiveTrack::factory()->create([
            'user_id' => $user->id,
            'write_key_hash' => Hash::make('secret123'),
            'is_active' => true,
            'started_at' => now(),
        ]);

        $res = $this->postJson('/api/live-tracks/'.$track->public_id.'/end', [], [
            'X-Track-Key' => 'secret123',
        ]);

        $res->assertOk();
        $this->assertFalse($track->fresh()->is_active);
        $this->assertNotNull($track->fresh()->ended_at);
    }

    public function test_effective_is_active_false_when_idle(): void
    {
        $track = LiveTrack::factory()->create([
            'is_active' => true,
            'started_at' => now()->subHour(),
        ]);

        // Old point more than 10 minutes ago
        $track->points()->create([
            'latitude' => 14.6,
            'longitude' => 120.98,
            'recorded_at' => now()->subMinutes(10),
        ]);

        $res = $this->getJson('/api/live-tracks/'.$track->public_id.'/points');
        $res->assertOk();
        $res->assertJsonPath('track.isActive', false);
    }

    public function test_effective_is_active_true_when_recent_points(): void
    {
        $track = LiveTrack::factory()->create([
            'is_active' => true,
            'started_at' => now()->subHour(),
        ]);

        // Recent point now
        $track->points()->create([
            'latitude' => 14.6,
            'longitude' => 120.98,
            'recorded_at' => now(),
        ]);

        $res = $this->getJson('/api/live-tracks/'.$track->public_id.'/points');
        $res->assertOk();
        $res->assertJsonPath('track.isActive', true);
    }
}
