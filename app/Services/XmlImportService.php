<?php

namespace App\Services;

use App\Models\Hotel;
use App\Models\Rate;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use SimpleXMLElement;

/**
 * Importa os datasets XML distribuídos com o desafio para o modelo relacional.
 *
 * Decisões principais de design:
 * - a ordem de importação respeita dependências de chave estrangeira
 * - cada importação é idempotente via updateOrCreate
 * - a importação completa roda dentro de transação para evitar persistência parcial
 */
class XmlImportService
{
    private const AVAILABLE_DATASETS = [
        'hotels',
        'rooms',
        'rates',
        'reservations',
    ];

    /**
     * Executa uma importação transacional para o subconjunto de datasets solicitado.
     *
     * O serviço mantém uma ordem determinística porque reservas dependem de hotéis,
     * quartos e tarifas previamente existentes.
     */
    public function import(array $datasets = self::AVAILABLE_DATASETS): array
    {
        $datasets = array_values(array_unique($datasets));

        foreach ($datasets as $dataset) {
            if (! in_array($dataset, self::AVAILABLE_DATASETS, true)) {
                throw new RuntimeException("Unsupported dataset [{$dataset}].");
            }
        }

        $orderedDatasets = array_values(array_intersect(self::AVAILABLE_DATASETS, $datasets));
        $summary = [];

        DB::transaction(function () use ($orderedDatasets, &$summary): void {
            foreach ($orderedDatasets as $dataset) {
                $summary[$dataset] = $this->{"import{$this->studly($dataset)}"}();
            }
        });

        return $summary;
    }

    /**
     * Importa o dataset de hotéis.
     *
     * O XML de hotéis armazena o nome em um nó filho <name>, e não diretamente
     * no elemento <hotel>; por isso o importador lê $hotelNode->name.
     */
    private function importHotels(): array
    {
        $xml = $this->loadXml('hotels.xml');
        $imported = 0;

        foreach ($xml->hotel as $hotelNode) {
            Hotel::query()->updateOrCreate(
                ['id' => (int) $hotelNode['id']],
                ['name' => trim((string) ($hotelNode->name ?? $hotelNode))]
            );
            $imported++;
        }

        return ['imported' => $imported];
    }

    /**
     * Importa quartos e vincula cada quarto ao seu respectivo hotel.
     */
    private function importRooms(): array
    {
        $xml = $this->loadXml('rooms.xml');
        $imported = 0;

        foreach ($xml->room as $roomNode) {
            Room::query()->updateOrCreate(
                ['id' => (int) $roomNode['id']],
                [
                    'hotel_id' => (int) $roomNode['hotel_id'],
                    'name' => trim((string) $roomNode),
                    'inventory_count' => (int) $roomNode['inventory_count'],
                ]
            );
            $imported++;
        }

        return ['imported' => $imported];
    }

    /**
     * Importa tarifas do hotel e normaliza campos booleanos e decimais vindos do XML.
     */
    private function importRates(): array
    {
        $xml = $this->loadXml('rates.xml');
        $imported = 0;

        foreach ($xml->rate as $rateNode) {
            Rate::query()->updateOrCreate(
                ['id' => (int) $rateNode['id']],
                [
                    'hotel_id' => (int) $rateNode['hotel_id'],
                    'name' => trim((string) $rateNode),
                    'active' => ((string) $rateNode['active']) === '1',
                    'price' => (float) $rateNode['price'],
                ]
            );
            $imported++;
        }

        return ['imported' => $imported];
    }

    /**
     * Importa a raiz do agregado de reserva junto com hóspedes e preços aninhados.
     *
     * As coleções filhas são recriadas a cada importação para que a estrutura persistida
     * reflita exatamente o XML de origem após reprocessamento.
     */
    private function importReservations(): array
    {
        $xml = $this->loadXml('reservations.xml');
        $imported = 0;

        foreach ($xml->reservation as $reservationNode) {
            $reservation = Reservation::query()->updateOrCreate(
                ['id' => (int) $reservationNode->id],
                [
                    'hotel_id' => (int) $reservationNode->hotel_id,
                    'room_id' => (int) $reservationNode->room->id,
                    'room_reservation_id' => (int) $reservationNode->room->roomreservation_id,
                    'customer_first_name' => trim((string) $reservationNode->customer->first_name),
                    'customer_last_name' => trim((string) $reservationNode->customer->last_name),
                    'booked_at_date' => (string) $reservationNode->date,
                    'booked_at_time' => (string) $reservationNode->time,
                    'arrival_date' => (string) $reservationNode->room->arrival_date,
                    'departure_date' => (string) $reservationNode->room->departure_date,
                    'currency_code' => trim((string) $reservationNode->room->currencycode),
                    'meal_plan' => trim((string) $reservationNode->room->meal_plan),
                    'total_price' => (float) $reservationNode->room->totalprice,
                ]
            );

            $reservation->guests()->delete();
            foreach ($reservationNode->room->guest_counts->guest_count as $guestNode) {
                $reservation->guests()->create([
                    'type' => (string) $guestNode['type'],
                    'count' => (int) $guestNode['count'],
                ]);
            }

            $reservation->prices()->delete();
            foreach ($reservationNode->room->price as $priceNode) {
                $reservation->prices()->create([
                    'rate_id' => isset($priceNode['rate_id']) ? (int) $priceNode['rate_id'] : null,
                    'reference_date' => (string) $priceNode['date'],
                    'amount' => (float) $priceNode,
                ]);
            }

            $imported++;
        }

        return ['imported' => $imported];
    }

    /**
     * Carrega e interpreta um arquivo XML a partir do diretório database do projeto.
     */
    private function loadXml(string $fileName): SimpleXMLElement
    {
        $path = database_path($fileName);

        if (! is_file($path)) {
            throw new RuntimeException("XML file [{$fileName}] not found.");
        }

        $xml = simplexml_load_file($path);

        if ($xml === false) {
            throw new RuntimeException("Unable to parse XML file [{$fileName}].");
        }

        return $xml;
    }

    /**
     * Converte chaves como "hotels" em sufixos internos como "Hotels".
     */
    private function studly(string $value): string
    {
        return ucfirst(rtrim($value, 's')).'s';
    }
}
