<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

/**
 * Provider responsável pelo sistema de eventos e listeners da aplicação.
 *
 * Neste projeto ele segue praticamente o padrão do Laravel porque o desafio
 * não exigiu eventos customizados de domínio.
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * Mapeamento entre eventos e listeners da aplicação.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Inicializa eventos da aplicação.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Define se eventos e listeners devem ser descobertos automaticamente.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
