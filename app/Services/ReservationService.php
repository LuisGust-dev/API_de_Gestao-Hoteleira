<?php

namespace App\Services;

use App\Exceptions\ReservationConflictException;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Support\Facades\DB;

/**
 * Serviço de aplicação responsável pelo caso de uso de criação de reservas.
 *
 * Esta camada orquestra o fluxo de reserva:
 * - valida a consistência entre quarto e hotel
 * - valida disponibilidade via AvailabilityService
 * - persiste o agregado de reserva de forma transacional
 * - retorna o agregado já hidratado para saída da API
 */
class ReservationService
{
    public function __construct(
        private readonly AvailabilityService $availabilityService
    ) {
    }

    /**
     * Cria o agregado de reserva a partir de um payload normalizado.
     *
     * @throws ReservationConflictException quando o quarto não pertence ao hotel
     * informado ou quando o período solicitado conflita com outra reserva.
     */
    public function create(array $payload): Reservation
    {
        /** @var Room $room */
        $room = Room::query()->findOrFail($payload['room_id']);

        if ((int) $room->hotel_id !== (int) $payload['hotel_id']) {
            throw new ReservationConflictException('The selected room does not belong to the provided hotel.');
        }

        if (! $this->availabilityService->isRoomAvailable(
            (int) $payload['room_id'],
            $payload['arrival_date'],
            $payload['departure_date']
        )) {
            throw new ReservationConflictException('Room is unavailable for the selected period.');
        }

        return DB::transaction(function () use ($payload): Reservation {
            // Os IDs de reserva vêm de dados externos durante a importação; por isso,
            // novas reservas manuais continuam a sequência atual em vez de usar auto incremento.
            $reservation = Reservation::query()->create([
                'id' => $payload['id'] ?? $this->nextId(Reservation::class),
                'hotel_id' => $payload['hotel_id'],
                'room_id' => $payload['room_id'],
                'room_reservation_id' => $payload['room_reservation_id'] ?? $this->nextRoomReservationId(),
                'customer_first_name' => $payload['customer']['first_name'],
                'customer_last_name' => $payload['customer']['last_name'],
                'booked_at_date' => $payload['booked_at_date'],
                'booked_at_time' => $payload['booked_at_time'],
                'arrival_date' => $payload['arrival_date'],
                'departure_date' => $payload['departure_date'],
                'currency_code' => $payload['currency_code'],
                'meal_plan' => $payload['meal_plan'] ?? null,
                'total_price' => $payload['total_price'],
            ]);

            $reservation->guests()->createMany($payload['guests']);
            $reservation->prices()->createMany(array_map(fn (array $price): array => [
                'rate_id' => $price['rate_id'] ?? null,
                'reference_date' => $price['reference_date'],
                'amount' => $price['amount'],
            ], $payload['prices']));

            return $reservation->load(['hotel', 'room', 'guests', 'prices']);
        });
    }

    /**
     * Gera o próximo identificador no estilo dos dados externos para models sem auto incremento.
     */
    private function nextId(string $modelClass): int
    {
        return ((int) $modelClass::query()->max('id')) + 1;
    }

    /**
     * Gera o próximo identificador de room reservation usado pelos dados importados.
     */
    private function nextRoomReservationId(): int
    {
        return ((int) Reservation::query()->max('room_reservation_id')) + 1;
    }
}
