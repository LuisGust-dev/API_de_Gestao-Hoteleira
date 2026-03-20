<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request responsável pela validação de atualização de quartos.
 *
 * Todas as regras usam "sometimes" porque, em operações de update, o cliente
 * pode enviar apenas os campos que deseja alterar.
 */
class UpdateRoomRequest extends FormRequest
{
    /**
     * Define se a requisição está autorizada a prosseguir.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validação para atualização parcial ou total de quartos.
     */
    public function rules(): array
    {
        return [
            'hotel_id' => ['sometimes', 'integer', 'exists:hotels,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'inventory_count' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
