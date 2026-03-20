<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

/**
 * Provider responsável por políticas de autorização e regras de acesso.

 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Mapeamento entre models e suas policies.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [

    ];

    /**
     * Inicializa serviços de autenticação e autorização.
     */
    public function boot(): void
    {
     
    }
}
