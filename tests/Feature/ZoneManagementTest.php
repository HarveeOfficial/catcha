<?php

namespace Tests\Feature;

use App\Models\Species;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ZoneManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Species $species;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->species = Species::factory()->create();
    }

    public function test_admin_can_view_zones_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.zones.index'))
            ->assertStatus(200)
            ->assertViewIs('admin.zones.index');
    }

    public function test_admin_can_create_zone_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.zones.create'))
            ->assertStatus(200)
            ->assertViewIs('admin.zones.create');
    }

    public function test_admin_can_store_zone(): void
    {
        $geometry = [
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Polygon',
                        'coordinates' => [
                            [
                                [123.5, 10.3],
                                [123.6, 10.3],
                                [123.6, 10.4],
                                [123.5, 10.4],
                                [123.5, 10.3],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.zones.store'), [
                'name' => 'North Bay',
                'color' => '#00FF00',
                'description' => 'Rich fishing grounds',
                'geometry' => json_encode($geometry),
                'species_ids' => [$this->species->id],
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('zones', [
            'name' => 'North Bay',
            'color' => '#00FF00',
            'is_active' => true,
        ]);

        $zone = Zone::where('name', 'North Bay')->first();
        $this->assertTrue($zone->species->contains($this->species->id));
    }

    public function test_admin_can_view_zone(): void
    {
        $zone = Zone::factory()->create();

        $this->actingAs($this->admin)
            ->get(route('admin.zones.show', $zone))
            ->assertStatus(200)
            ->assertViewIs('admin.zones.show');
    }

    public function test_admin_can_edit_zone_page(): void
    {
        $zone = Zone::factory()->create();

        $this->actingAs($this->admin)
            ->get(route('admin.zones.edit', $zone))
            ->assertStatus(200)
            ->assertViewIs('admin.zones.edit');
    }

    public function test_admin_can_update_zone(): void
    {
        $zone = Zone::factory()->create();

        $geometry = [
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Polygon',
                        'coordinates' => [
                            [
                                [123.5, 10.3],
                                [123.6, 10.3],
                                [123.6, 10.4],
                                [123.5, 10.4],
                                [123.5, 10.3],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.zones.update', $zone), [
                'name' => 'Updated Zone',
                'color' => '#FF0000',
                'description' => 'Updated description',
                'geometry' => json_encode($geometry),
                'species_ids' => [$this->species->id],
                'is_active' => false,
            ]);

        $response->assertRedirect();

        $zone->refresh();
        $this->assertEquals('Updated Zone', $zone->name);
        $this->assertEquals('#FF0000', $zone->color);
        $this->assertFalse($zone->is_active);
        $this->assertTrue($zone->species->contains($this->species->id));
    }

    public function test_admin_can_delete_zone(): void
    {
        $zone = Zone::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.zones.destroy', $zone));

        $response->assertRedirect();
        $this->assertModelMissing($zone);
    }

    public function test_public_can_view_zones_page(): void
    {
        $this->get(route('zones'))
            ->assertStatus(200)
            ->assertViewIs('zones');
    }

    public function test_api_zones_data_returns_active_zones(): void
    {
        $activeZone = Zone::factory()->create(['is_active' => true]);
        $inactiveZone = Zone::factory()->create(['is_active' => false]);

        $response = $this->get(route('api.zones.data'));

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertCount(1, $data['zones']);
        $this->assertEquals($activeZone->id, $data['zones'][0]['id']);
    }

    public function test_zone_species_relationship(): void
    {
        $zone = Zone::factory()->create();
        $species1 = Species::factory()->create();
        $species2 = Species::factory()->create();

        $zone->species()->attach([$species1->id, $species2->id]);

        $this->assertCount(2, $zone->species);
        $this->assertTrue($zone->species->contains($species1));
        $this->assertTrue($zone->species->contains($species2));
    }

    public function test_non_admin_cannot_access_zone_routes(): void
    {
        $user = User::factory()->create(['role' => 'fisher']);
        $zone = Zone::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.zones.index'))
            ->assertStatus(403);

        $this->actingAs($user)
            ->get(route('admin.zones.create'))
            ->assertStatus(403);

        $this->actingAs($user)
            ->get(route('admin.zones.edit', $zone))
            ->assertStatus(403);
    }
}
