<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ReservationConflictException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Http\JsonResponse;

/**
 * Expõe os endpoints de leitura e criação de reservas.
 *
 * O controller não contém regras de negócio da reserva. Ele apenas orquestra HTTP:
 * - a validação de entrada fica em StoreReservationRequest
 * - as regras de reserva ficam em ReservationService
 * - conflitos de domínio são traduzidos em resposta 422
 */
class ReservationController extends Controller
{
    /**
     * Lista reservas com todos os relacionamentos necessários para consumo da API.
     */
    public function index(): JsonResponse
    {
        $reservations = Reservation::query()
            ->with(['hotel', 'room', 'guests', 'prices'])
            ->orderBy('arrival_date')
            ->get();

        return response()->json($reservations);
    }

    /**
     * Cria uma reserva após validação da requisição e checagens de disponibilidade.
     */
    public function store(StoreReservationRequest $request, ReservationService $reservationService): JsonResponse
    {
        try {
            $reservation = $reservationService->create($request->validated());
        } catch (ReservationConflictException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json($reservation, 201);
    }

    /**
     * Retorna uma reserva específica com seus relacionamentos carregados.
     */
    public function show(Reservation $reservation): JsonResponse
    {
        return response()->json($reservation->load(['hotel', 'room', 'guests', 'prices']));
    }
}
