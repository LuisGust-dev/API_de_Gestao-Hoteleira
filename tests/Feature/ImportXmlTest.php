<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportXmlTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_all_xml_files(): void
    {
        $response = $this->postJson('/api/imports/xml');

        $response
            ->assertOk()
            ->assertJsonPath('summary.hotels.imported', 3)
            ->assertJsonPath('summary.rooms.imported', 3)
            ->assertJsonPath('summary.rates.imported', 3)
            ->assertJsonPath('summary.reservations.imported', 6);

        $this->assertDatabaseCount('hotels', 3);
        $this->assertDatabaseCount('rooms', 3);
        $this->assertDatabaseCount('rates', 3);
        $this->assertDatabaseCount('reservations', 6);
    }
}
