<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_track_and_push_point_and_poll(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $res = $this->postJson(route('live-tracks.create'), [ 'title' => 'Trip A' ]);
        $res->assertOk();
        $publicId = $res['publicId'];
        $writeKey = $res['writeKey'];

        $store = $this->postJson(route('live-tracks.points.store', $publicId), [
            'lat' => 10.1234567,
            'lng' => 123.1234567,
            'accuracy' => 5,
        ], [ 'X-Track-Key' => $writeKey ]);

        $store->assertCreated();

        $poll = $this->getJson(route('live-tracks.points.index', $publicId));
        $poll->assertOk()->assertJsonStructure(['points']);
        $this->assertCount(1, $poll['points']);
        $this->assertSame(10.1234567, $poll['points'][0]['lat']);
    }
}
