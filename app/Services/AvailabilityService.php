<?php

namespace App\Services;

use App\Models\Reservation;
use Carbon\CarbonImmutable;

/**
 * Centraliza a regra de disponibilidade de reservas.
 *
 * O desafio exige que um quarto não possa ser reservado quando já existe outra
 * reserva ocupando o intervalo solicitado. Este serviço existe para evitar que
 * controllers e fluxos de escrita dupliquem a lógica de sobreposição de datas.
 */
class AvailabilityService
{
    /**
     * Consulta o estado persistido para saber se um quarto está disponível em um período.
     *
     * Regra de sobreposição:
     * existing.arrival_date < requested.departure_date
     * E
     * existing.departure_date > requested.arrival_date
     *
     * Isso trata a reserva como intervalo [check-in, check-out), permitindo reservas
     * adjacentes quando uma começa exatamente no momento em que a outra termina.
     */
    public function isRoomAvailable(
        int $roomId,
        string $arrivalDate,
        string $departureDate,
        ?int $ignoreReservationId = null
    ): bool
    {
        $query = Reservation::query()
            ->where('room_id', $roomId)
            ->where(function ($query) use ($arrivalDate, $departureDate) {
                $query
                    ->where('arrival_date', '<', $departureDate)
                    ->where('departure_date', '>', $arrivalDate);
            });

        if ($ignoreReservationId !== null) {
            $query->whereKeyNot($ignoreReservationId);
        }

        return ! $query->exists();
    }

    /**
     * Função pura de sobreposição usada nos testes unitários e como apoio de leitura
     * para entender a regra de datas sem depender do banco.
     */
    public function overlaps(
        string $arrivalDate,
        string $departureDate,
        string $existingArrivalDate,
        string $existingDepartureDate
    ): bool {
        $arrival = CarbonImmutable::parse($arrivalDate);
        $departure = CarbonImmutable::parse($departureDate);
        $existingArrival = CarbonImmutable::parse($existingArrivalDate);
        $existingDeparture = CarbonImmutable::parse($existingDepartureDate);

        return $arrival->lt($existingDeparture) && $departure->gt($existingArrival);
    }
}
