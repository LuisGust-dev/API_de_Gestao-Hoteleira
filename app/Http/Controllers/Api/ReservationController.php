<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ReservationConflictException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Http\JsonResponse;

/**
 * Controller responsável pelos endpoints HTTP de reservas.
 *
 * Papel desta classe dentro da arquitetura:
 * - receber chamadas da camada web/api
 * - delegar a validação estrutural do payload ao Form Request
 * - encaminhar a regra de negócio para o ReservationService
 * - transformar o resultado do domínio em resposta JSON adequada
 *
 * Este controller não decide disponibilidade, não calcula conflito de datas
 * e não executa parsing de dados complexos. Essas decisões pertencem à camada
 * de serviço para manter separação de responsabilidades e melhor testabilidade.
 */
class ReservationController extends Controller
{
    /**
     * Lista todas as reservas em ordem de chegada/check-in.
     *
     * A consulta carrega eager loading de:
     * - hotel: para devolver o contexto da reserva
     * - room: para identificar o quarto reservado
     * - guests: para devolver a composição de hóspedes
     * - prices: para devolver as diárias/preços vinculados
     *
     * O objetivo é evitar consultas adicionais no consumidor da API e também
     * evitar problema de N+1 queries durante a serialização da resposta.
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
     * Cria uma nova reserva a partir de um payload validado.
     *
     * Fluxo executado:
     * 1. StoreReservationRequest valida a estrutura dos dados enviados
     * 2. ReservationService valida relação hotel/quarto e disponibilidade
     * 3. ReservationService persiste reserva, hóspedes e preços em transação
     * 4. A API retorna 201 com o agregado criado
     *
     * Caso a regra de domínio detecte conflito de período ou inconsistência entre
     * quarto e hotel, o service lança ReservationConflictException. O controller
     * converte essa exceção em resposta HTTP 422 por se tratar de uma requisição
     * válida estruturalmente, porém inválida do ponto de vista do negócio.
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
     * Retorna o detalhe de uma reserva específica.
     *
     * O model é resolvido automaticamente pelo route model binding do Laravel
     * a partir do parâmetro da rota. Em seguida os relacionamentos necessários
     * são carregados para entregar uma visão completa do agregado de reserva.
     */
    public function show(Reservation $reservation): JsonResponse
    {
        return response()->json($reservation->load(['hotel', 'room', 'guests', 'prices']));
    }

    /**
     * Atualiza uma reserva existente reaplicando validações de domínio.
     */
    public function update(
        UpdateReservationRequest $request,
        Reservation $reservation,
        ReservationService $reservationService
    ): JsonResponse {
        try {
            $updatedReservation = $reservationService->update($reservation, $request->validated());
        } catch (ReservationConflictException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json($updatedReservation);
    }

    /**
     * Remove uma reserva existente e retorna resposta 204 sem conteúdo.
     */
    public function destroy(Reservation $reservation): JsonResponse
    {
        $reservation->delete();

        return response()->json(status: 204);
    }
}
