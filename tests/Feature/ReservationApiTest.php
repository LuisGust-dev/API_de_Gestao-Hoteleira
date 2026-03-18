<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\Rate;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_reservation_when_room_is_available(): void
    {
        $hotel = Hotel::query()->create([
            'id' => 1375988,
            'name' => 'Hotel Foco Prime',
        ]);

        $room = Room::query()->create([
            'id' => 137598802,
            'hotel_id' => $hotel->id,
            'name' => 'Deluxe Double Room',
            'inventory_count' => 15,
        ]);

        $rate = Rate::query()->create([
            'id' => 5333849,
            'hotel_id' => $hotel->id,
            'name' => 'Standard Rate',
            'active' => true,
            'price' => 150,
        ]);

        $payload = [
            'hotel_id' => $hotel->id,
            'room_id' => $room->id,
            'customer' => [
                'first_name' => 'Luis',
                'last_name' => 'Gustavo',
            ],
            'booked_at_date' => '2026-03-16',
            'booked_at_time' => '10:00:00',
            'arrival_date' => '2026-04-20',
            'departure_date' => '2026-04-22',
            'currency_code' => 'BRL',
            'meal_plan' => 'Breakfast included.',
            'total_price' => 300,
            'guests' => [
                ['type' => 'adult', 'count' => 2],
            ],
            'prices' => [
                ['rate_id' => $rate->id, 'reference_date' => '2026-04-20', 'amount' => 150],
                ['rate_id' => $rate->id, 'reference_date' => '2026-04-21', 'amount' => 150],
            ],
        ];

        $response = $this->postJson('/api/reservations', $payload);

        $response
            ->assertCreated()
            ->assertJsonPath('hotel_id', $hotel->id)
            ->assertJsonPath('room_id', $room->id)
            ->assertJsonCount(1, 'guests')
            ->assertJsonCount(2, 'prices');

        $this->assertDatabaseCount('reservations', 1);
        $this->assertDatabaseHas('reservations', [
            'hotel_id' => $hotel->id,
            'room_id' => $room->id,
            'arrival_date' => '2026-04-20 00:00:00',
            'departure_date' => '2026-04-22 00:00:00',
        ]);
    }

    public function test_it_blocks_a_reservation_when_dates_overlap(): void
    {
        $hotel = Hotel::query()->create([
            'id' => 1375988,
            'name' => 'Hotel Foco Prime',
        ]);

        $room = Room::query()->create([
            'id' => 137598802,
            'hotel_id' => $hotel->id,
            'name' => 'Deluxe Double Room',
            'inventory_count' => 15,
        ]);

        $rate = Rate::query()->create([
            'id' => 5333849,
            'hotel_id' => $hotel->id,
            'name' => 'Standard Rate',
            'active' => true,
            'price' => 150,
        ]);

        Reservation::query()->create([
            'id' => 3820212524,
            'hotel_id' => $hotel->id,
            'room_id' => $room->id,
            'room_reservation_id' => 3641632087,
            'customer_first_name' => 'Bruno',
            'customer_last_name' => 'Nascimento',
            'booked_at_date' => '2026-03-16',
            'booked_at_time' => '09:15:00',
            'arrival_date' => '2026-04-10',
            'departure_date' => '2026-04-12',
            'currency_code' => 'BRL',
            'meal_plan' => 'Breakfast included.',
            'total_price' => 300,
        ]);

        $payload = [
            'hotel_id' => $hotel->id,
            'room_id' => $room->id,
            'customer' => [
                'first_name' => 'Ana',
                'last_name' => 'Silva',
            ],
            'booked_at_date' => '2026-03-16',
            'booked_at_time' => '12:00:00',
            'arrival_date' => '2026-04-11',
            'departure_date' => '2026-04-13',
            'currency_code' => 'BRL',
            'meal_plan' => 'Breakfast included.',
            'total_price' => 300,
            'guests' => [
                ['type' => 'adult', 'count' => 2],
            ],
            'prices' => [
                ['rate_id' => $rate->id, 'reference_date' => '2026-04-11', 'amount' => 150],
                ['rate_id' => $rate->id, 'reference_date' => '2026-04-12', 'amount' => 150],
            ],
        ];

        $response = $this->postJson('/api/reservations', $payload);

        $response
            ->assertStatus(422)
            ->assertJsonPath('message', 'Room is unavailable for the selected period.');

        $this->assertDatabaseCount('reservations', 1);
    }
}
