<?php

namespace Tests\Unit;

use App\Services\AvailabilityService;
use Tests\TestCase;

class AvailabilityServiceTest extends TestCase
{
    public function test_it_detects_overlapping_periods(): void
    {
        $service = new AvailabilityService();

        $this->assertTrue(
            $service->overlaps('2026-04-11', '2026-04-13', '2026-04-10', '2026-04-12')
        );
    }

    public function test_it_allows_adjacent_periods_without_overlap(): void
    {
        $service = new AvailabilityService();

        $this->assertFalse(
            $service->overlaps('2026-04-12', '2026-04-14', '2026-04-10', '2026-04-12')
        );
    }
}
