<?php

namespace Tests\Unit;

use App\Http\Controllers\CatchController;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class GeohashTest extends TestCase
{
    /** @test */
    public function it_encodes_geohash_consistently(): void
    {
        $controller = new CatchController();
        $ref = new ReflectionClass($controller);
        $method = $ref->getMethod('encodeGeohash');
        $method->setAccessible(true);
        $hash = $method->invoke($controller, 14.5995, 120.9842, 10);
        $this->assertNotEmpty($hash);
        $this->assertSame(10, strlen($hash));
        $this->assertMatchesRegularExpression('/^[0-9bcdefghjkmnpqrstuvwxyz]+$/', $hash);
    }
}
