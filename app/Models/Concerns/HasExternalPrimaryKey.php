<?php

namespace App\Models\Concerns;

/**
 * Configura models que usam IDs externos em vez de auto incremento padrão.
 *
 * Esse comportamento é necessário porque os XMLs do desafio já trazem
 * identificadores próprios para hotéis, quartos, tarifas e reservas.
 */
trait HasExternalPrimaryKey
{
    /**
     * Ajusta a configuração do model durante sua inicialização pelo Eloquent.
     */
    public function initializeHasExternalPrimaryKey(): void
    {
        $this->incrementing = false;
        $this->keyType = 'int';
    }
}
