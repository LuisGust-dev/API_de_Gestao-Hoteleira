<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Provider base para registrar bindings e configurações globais da aplicação.
 *
 * Neste projeto ele permanece enxuto porque não foi necessário adicionar
 * container bindings ou bootstrapping global customizado.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Registra serviços da aplicação no container de dependências.
     */
    public function register(): void
    {
        //
    }

    /**
     * Executa inicializações globais após o container estar montado.
     */
    public function boot(): void
    {
        //
    }
}
