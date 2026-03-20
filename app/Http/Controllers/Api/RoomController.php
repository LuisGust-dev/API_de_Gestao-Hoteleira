<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Models\Room;
use Illuminate\Http\JsonResponse;

/**
 * Controller responsável pelo gerenciamento HTTP de quartos.
 *
 * Papel desta classe:
 * - expor endpoints de CRUD para a entidade Room
 * - delegar validação estrutural aos Form Requests
 * - usar o model de forma direta, já que o caso de uso de quartos é simples
 * - devolver respostas JSON adequadas para cada operação
 *
 * Diferentemente do fluxo de reservas, aqui não existe uma regra de negócio
 * complexa exigindo service dedicado. Por isso o controller consegue operar
 * diretamente sobre o model sem comprometer a organização da aplicação.
 */
class RoomController extends Controller
{
    /**
     * Lista todos os quartos cadastrados.
     *
     * O relacionamento com hotel é carregado junto para que o consumidor da API
     * consiga identificar rapidamente a qual hotel cada quarto pertence, sem a
     * necessidade de múltiplas chamadas adicionais.
     */
    public function index(): JsonResponse
    {
        return response()->json(Room::query()->with('hotel')->orderBy('id')->get());
    }

    /**
     * Cria um novo quarto a partir de um payload validado.
     *
     * Fluxo:
     * 1. StoreRoomRequest valida estrutura e integridade básica do payload
     * 2. O model Room persiste o novo registro
     * 3. O relacionamento com hotel é carregado para compor a resposta
     * 4. A API retorna 201 Created
     */
    public function store(StoreRoomRequest $request): JsonResponse
    {
        $room = Room::query()->create($request->validated())->load('hotel');

        return response()->json($room, 201);
    }

    /**
     * Retorna o detalhe de um quarto específico.
     *
     * O model é resolvido automaticamente pelo route model binding do Laravel.
     * Além do hotel, a resposta carrega também as reservas relacionadas para
     * oferecer uma visão mais completa do estado atual do quarto.
     */
    public function show(Room $room): JsonResponse
    {
        return response()->json($room->load(['hotel', 'reservations']));
    }

    /**
     * Atualiza um quarto existente.
     *
     * O UpdateRoomRequest permite atualização parcial, então somente os campos
     * enviados no payload são validados e persistidos. Após o update, o registro
     * é recarregado do banco para garantir que a resposta reflita o estado final.
     */
    public function update(UpdateRoomRequest $request, Room $room): JsonResponse
    {
        $room->update($request->validated());

        return response()->json($room->fresh()->load('hotel'));
    }

    /**
     * Remove um quarto existente.
     *
     * A resposta 204 é usada porque a operação foi concluída com sucesso e não há
     * necessidade de retornar corpo na resposta após a exclusão.
     */
    public function destroy(Room $room): JsonResponse
    {
        $room->delete();

        return response()->json(status: 204);
    }
}
