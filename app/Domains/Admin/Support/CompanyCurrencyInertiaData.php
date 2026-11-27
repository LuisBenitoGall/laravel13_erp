<?php

namespace App\Domains\Admin\Support;

use App\Domains\Admin\Enums\CurrencyStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Currency;

/**
 * Payload Inertia compartido de monedas de uso (Edit Company / Delegación / hub).
 */
final class CompanyCurrencyInertiaData
{
    /**
     * @return array{
     *     currencies: list<array{id: int, code: string|null, symbol: string|null, name: string, status: string, is_default: bool}>,
     *     currency_options: list<array{id: int, code: string|null, symbol: string|null, name: string, status: string}>,
     *     default_currency_id: int|null
     * }
     */
    public static function for(Company $company): array
    {
        return [
            'currencies' => self::mapCurrencies($company),
            'currency_options' => self::options(),
            'default_currency_id' => $company->defaultCurrency()?->id,
        ];
    }

    /**
     * @return list<array{id: int, code: string|null, symbol: string|null, name: string, status: string, is_default: bool}>
     */
    public static function mapCurrencies(Company $company): array
    {
        return $company->currencies
            ->map(fn (Currency $currency): array => [
                'id' => $currency->id,
                'code' => $currency->code,
                'symbol' => $currency->symbol,
                'name' => $currency->name,
                'status' => $currency->status->value,
                'is_default' => (bool) data_get($currency->getRelations(), 'pivot.is_default', false),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: int, code: string|null, symbol: string|null, name: string, status: string}>
     */
    public static function options(): array
    {
        return Currency::query()
            ->where('status', CurrencyStatus::Active)
            ->orderBy('code')
            ->get(['id', 'code', 'symbol', 'name', 'status'])
            ->map(fn (Currency $currency): array => [
                'id' => $currency->id,
                'code' => $currency->code,
                'symbol' => $currency->symbol,
                'name' => $currency->name,
                'status' => $currency->status->value,
            ])
            ->values()
            ->all();
    }
}
