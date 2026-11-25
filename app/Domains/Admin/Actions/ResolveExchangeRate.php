<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\ExchangeRate;

/**
 * Resuelve la cotización actual del par dirigido (única fila vigente).
 * Devuelve null si no existe. NO invierte el par; NO infiere B→A desde A→B.
 */
class ResolveExchangeRate
{
    public function handle(int $fromCurrencyId, int $toCurrencyId): ?ExchangeRate
    {
        return ExchangeRate::query()
            ->where('from_currency_id', $fromCurrencyId)
            ->where('to_currency_id', $toCurrencyId)
            ->first();
    }
}
