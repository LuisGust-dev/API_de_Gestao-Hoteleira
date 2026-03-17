<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\XmlImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Ponto de entrada HTTP para a importação dos XMLs fornecidos no desafio.
 *
 * A responsabilidade do controlle:
 * - validar quais datasets podem ser importados
 * - delegar o fluxo de importação para a camada de serviço
 * - converter o resultado em resposta JSON

 * Regras de parsing, ordenação e persistência ficam no XmlImportService.
 */
class ImportController extends Controller
{
    /**
     * Importa os datasets solicitados ou o conjunto completo quando nada é informado.
     */
    public function __invoke(Request $request, XmlImportService $importService): JsonResponse
    {
        $validated = $request->validate([
            'datasets' => ['sometimes', 'array', 'min:1'],
            'datasets.*' => ['string', 'in:hotels,rooms,rates,reservations'],
        ]);

        $summary = $importService->import($validated['datasets'] ?? ['hotels', 'rooms', 'rates', 'reservations']);

        return response()->json([
            'message' => 'XML import completed successfully.',
            'summary' => $summary,
        ]);
    }
}
