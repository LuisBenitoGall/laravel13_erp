<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\ExchangeRate;
use Illuminate\Support\Facades\DB;

class DeleteExchangeRate
{
    /** Soft delete de ambos sentidos del mismo par unordered + fecha. */
    public function handle(ExchangeRate $exchangeRate): void
    {
        DB::transaction(function () use ($exchangeRate): void {
            $currencyA = min($exchangeRate->from_currency_id, $exchangeRate->to_currency_id);
            $currencyB = max($exchangeRate->from_currency_id, $exchangeRate->to_currency_id);
            $date = $exchangeRate->rate_date->toDateString();

            ExchangeRate::query()
                ->whereDate('rate_date', $date)
                ->where(function ($query) use ($currencyA, $currencyB): void {
                    $query->where(function ($pair) use ($currencyA, $currencyB): void {
                        $pair->where('from_currency_id', $currencyA)
                            ->where('to_currency_id', $currencyB);
                    })->orWhere(function ($pair) use ($currencyA, $currencyB): void {
                        $pair->where('from_currency_id', $currencyB)
                            ->where('to_currency_id', $currencyA);
                    });
                })
                ->get()
                ->each(fn (ExchangeRate $row) => $row->delete());
        });
    }
}
