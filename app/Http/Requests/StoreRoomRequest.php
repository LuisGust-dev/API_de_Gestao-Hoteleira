<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request responsável pela validação de criação de quartos.
 *
 * Esta camada protege o controller contra payloads inválidos e garante que
 * o model receba apenas dados estruturados corretamente.
 */
class StoreRoomRequest extends FormRequest
{
    /**
     * Define se a requisição está autorizada a prosseguir.
     *
     * Como o desafio não exige autenticação, qualquer chamada é aceita.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validação para criação de quartos.
     *
     * Pontos importantes:
     * - id é obrigatório e único
     * - hotel_id deve apontar para um hotel já persistido
     * - inventory_count precisa ser ao menos 1
     */
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'unique:rooms,id'],
            'hotel_id' => ['required', 'integer', 'exists:hotels,id'],
            'name' => ['required', 'string', 'max:255'],
            'inventory_count' => ['required', 'integer', 'min:1'],
        ];
    }
}
