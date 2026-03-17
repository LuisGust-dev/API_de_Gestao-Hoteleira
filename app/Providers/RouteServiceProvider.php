<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

/**
 * Provider responsável por configurar rotas e rate limiting da aplicação.
 *
 * É aqui que o Laravel define:
 * - prefixo /api para as rotas da API
 * - middleware aplicado às rotas API e web
 * - limite de requisições por cliente
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * Caminho padrão de redirecionamento após autenticação.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Configura rate limiting e registra grupos de rotas web e api.
     */
    public function boot(): void
    {
        // Limita as chamadas da API por usuário autenticado ou, na ausência dele, por IP.
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            // Todas as rotas de routes/api.php recebem automaticamente o prefixo /api.
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Rotas tradicionais renderizadas para navegador permanecem no grupo web.
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
