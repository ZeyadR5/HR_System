<?php

namespace Tests\Unit;

use App\Support\Geofence;
use PHPUnit\Framework\TestCase;

class GeofenceTest extends TestCase
{
    public function test_zero_distance_for_same_point(): void
    {
        $this->assertSame(0, Geofence::distanceMeters(30.0444, 31.2357, 30.0444, 31.2357));
    }

    public function test_short_distance_is_accurate(): void
    {
        // 0.001 degree latitude ≈ 111 meters
        $d = Geofence::distanceMeters(30.0000, 31.0000, 30.0010, 31.0000);
        $this->assertGreaterThanOrEqual(108, $d);
        $this->assertLessThanOrEqual(114, $d);
    }

    public function test_cairo_to_alexandria_is_about_180km(): void
    {
        $d = Geofence::distanceMeters(30.0444, 31.2357, 31.2001, 29.9187);
        $this->assertGreaterThan(170000, $d);
        $this->assertLessThan(190000, $d);
    }
}
