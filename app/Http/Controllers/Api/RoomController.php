<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Models\Room;
use Illuminate\Http\JsonResponse;

/**
 * Responsável pelos endpoints de CRUD de quartos.
 *
 * Este controller permanece enxuto de forma intencional. As regras de negócio
 * mais críticas do desafio estão concentradas em reservas, não em quartos.
 */
class RoomController extends Controller
{
    /**
     * Lista quartos incluindo o hotel relacionado para facilitar o consumo da API.
     */
    public function index(): JsonResponse
    {
        return response()->json(Room::query()->with('hotel')->orderBy('id')->get());
    }

    /**
     * Cria um quarto a partir de um payload validado.
     */
    public function store(StoreRoomRequest $request): JsonResponse
    {
        $room = Room::query()->create($request->validated())->load('hotel');

        return response()->json($room, 201);
    }

    /**
     * Retorna um quarto com as reservas atualmente vinculadas a ele.
     */
    public function show(Room $room): JsonResponse
    {
        return response()->json($room->load(['hotel', 'reservations']));
    }

    /**
     * Aplica atualização parcial ou completa com base no payload validado.
     */
    public function update(UpdateRoomRequest $request, Room $room): JsonResponse
    {
        $room->update($request->validated());

        return response()->json($room->fresh()->load('hotel'));
    }

    /**
     * Remove o quarto e retorna resposta 204 sem conteúdo.
     */
    public function destroy(Room $room): JsonResponse
    {
        $room->delete();

        return response()->json(status: 204);
    }
}
