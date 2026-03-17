<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

/**
 * Provider de broadcasting do Laravel.
 *
 * Não é parte central do desafio, mas continua disponível para registrar
 * canais caso a aplicação evolua para eventos em tempo real.
 */
class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Registra rotas e definições de canais de broadcasting.
     */
    public function boot(): void
    {
        Broadcast::routes();

        require base_path('routes/channels.php');
    }
}
