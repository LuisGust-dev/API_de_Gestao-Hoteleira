<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request responsável pela validação de atualização de reservas.
 *
 * Como a atualização pode ser parcial, os campos usam "sometimes" e só são
 * validados quando presentes no payload.
 */
class UpdateReservationRequest extends FormRequest
{
    /**
     * Define se a requisição está autorizada a prosseguir.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validação para atualização parcial ou total de reservas.
     */
    public function rules(): array
    {
        $reservationId = $this->route('reservation')?->id;

        return [
            'hotel_id' => ['sometimes', 'integer', 'exists:hotels,id'],
            'room_id' => ['sometimes', 'integer', 'exists:rooms,id'],
            'room_reservation_id' => [
                'sometimes',
                'integer',
                Rule::unique('reservations', 'room_reservation_id')->ignore($reservationId),
            ],
            'customer.first_name' => ['sometimes', 'string', 'max:255'],
            'customer.last_name' => ['sometimes', 'string', 'max:255'],
            'booked_at_date' => ['sometimes', 'date'],
            'booked_at_time' => ['sometimes', 'date_format:H:i:s'],
            'arrival_date' => ['sometimes', 'date'],
            'departure_date' => ['sometimes', 'date', 'after:arrival_date'],
            'currency_code' => ['sometimes', 'string', 'size:3'],
            'meal_plan' => ['sometimes', 'nullable', 'string', 'max:255'],
            'total_price' => ['sometimes', 'numeric', 'min:0'],
            'guests' => ['sometimes', 'array', 'min:1'],
            'guests.*.type' => ['required_with:guests', 'string', 'max:255'],
            'guests.*.count' => ['required_with:guests', 'integer', 'min:1'],
            'prices' => ['sometimes', 'array', 'min:1'],
            'prices.*.rate_id' => ['nullable', 'integer', 'exists:rates,id'],
            'prices.*.reference_date' => ['required_with:prices', 'date'],
            'prices.*.amount' => ['required_with:prices', 'numeric', 'min:0'],
        ];
    }
}
