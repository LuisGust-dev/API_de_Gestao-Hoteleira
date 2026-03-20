<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request responsável pela validação de criação de reservas.
 *
 * Esta validação garante integridade estrutural do payload antes da execução
 * das regras de negócio de disponibilidade e persistência.
 */
class StoreReservationRequest extends FormRequest
{
    /**
     * Define se a requisição está autorizada a prosseguir.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validação para criação de reservas.
     *
     * Esta camada valida:
     * - existência de hotel, quarto e tarifa
     * - consistência temporal básica das datas
     * - estrutura do cliente, hóspedes e preços
     * - integridade de identificadores quando enviados manualmente
     */
    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'unique:reservations,id'],
            'hotel_id' => ['required', 'integer', 'exists:hotels,id'],
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'room_reservation_id' => ['nullable', 'integer', 'unique:reservations,room_reservation_id'],
            'customer.first_name' => ['required', 'string', 'max:255'],
            'customer.last_name' => ['required', 'string', 'max:255'],
            'booked_at_date' => ['required', 'date'],
            'booked_at_time' => ['required', 'date_format:H:i:s'],
            'arrival_date' => ['required', 'date', 'after_or_equal:booked_at_date'],
            'departure_date' => ['required', 'date', 'after:arrival_date'],
            'currency_code' => ['required', 'string', 'size:3'],
            'meal_plan' => ['nullable', 'string', 'max:255'],
            'total_price' => ['required', 'numeric', 'min:0'],
            'guests' => ['required', 'array', 'min:1'],
            'guests.*.type' => ['required', 'string', 'max:255'],
            'guests.*.count' => ['required', 'integer', 'min:1'],
            'prices' => ['required', 'array', 'min:1'],
            'prices.*.rate_id' => ['nullable', 'integer', 'exists:rates,id'],
            'prices.*.reference_date' => ['required', 'date'],
            'prices.*.amount' => ['required', 'numeric', 'min:0'],
        ];
    }
}
