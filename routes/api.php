<?php

use App\Http\Controllers\Api\ImportController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\RoomController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas da API
|--------------------------------------------------------------------------
|
| Todas as rotas deste arquivo são carregadas com o prefixo "/api" pelo
| RouteServiceProvider. Este arquivo concentra apenas endpoints HTTP da API,
| separados das rotas web tradicionais.
|
| Estratégia adotada:
| - importação XML por endpoint dedicado
| - CRUD de quartos via apiResource
| - leitura/criação de reservas via apiResource parcial
|
*/

// Dispara a importação dos arquivos XML do desafio para o banco relacional.
Route::post('/imports/xml', ImportController::class);

// CRUD completo de quartos.
Route::apiResource('rooms', RoomController::class);

// Reservas expõem listagem, criação, consulta, atualização e remoção.
Route::apiResource('reservations', ReservationController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
