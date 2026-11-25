<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\ExchangeRate;
use Carbon\CarbonInterface;

/**
 * Resuelve la cotización vigente: mayor rate_date ≤ D para el par dirigido.
 * Devuelve null si no existe ninguna cotización ≤ D.
 * NO invierte el par; NO infiere B→A desde A→B (D8).
 */
class ResolveExchangeRate
{
    public function handle(int $fromCurrencyId, int $toCurrencyId, CarbonInterface|string $date): ?ExchangeRate
    {
        return ExchangeRate::query()
            ->where('from_currency_id', $fromCurrencyId)
            ->where('to_currency_id', $toCurrencyId)
            ->whereDate('rate_date', '<=', $date)
            ->orderByDesc('rate_date')
            ->first();
    }
}
