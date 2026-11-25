<?php

namespace App\Domains\Admin\Http\Resources;

use App\Domains\Admin\Models\Currency;
use App\Domains\Admin\Models\ExchangeRate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ExchangeRate */
class ExchangeRateResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $attributes = $this->resource->getAttributes();

        $currencyAId = isset($attributes['currency_a_id'])
            ? (int) $attributes['currency_a_id']
            : min((int) $this->from_currency_id, (int) $this->to_currency_id);

        $currencyBId = isset($attributes['currency_b_id'])
            ? (int) $attributes['currency_b_id']
            : max((int) $this->from_currency_id, (int) $this->to_currency_id);

        $codeA = $attributes['code_a'] ?? Currency::query()->whereKey($currencyAId)->value('code');
        $codeB = $attributes['code_b'] ?? Currency::query()->whereKey($currencyBId)->value('code');

        $rateAb = array_key_exists('rate_ab', $attributes)
            ? $attributes['rate_ab']
            : ExchangeRate::query()
                ->where('from_currency_id', $currencyAId)
                ->where('to_currency_id', $currencyBId)
                ->whereDate('rate_date', $this->rate_date->toDateString())
                ->value('rate');

        $rateBa = array_key_exists('rate_ba', $attributes)
            ? $attributes['rate_ba']
            : ExchangeRate::query()
                ->where('from_currency_id', $currencyBId)
                ->where('to_currency_id', $currencyAId)
                ->whereDate('rate_date', $this->rate_date->toDateString())
                ->value('rate');

        return [
            'id' => $this->id,
            'currency_a_id' => $currencyAId,
            'currency_b_id' => $currencyBId,
            'code_a' => $codeA,
            'code_b' => $codeB,
            'pair_label' => trim((string) $codeA).'/'.trim((string) $codeB),
            'rate_ab' => $rateAb,
            'rate_ba' => $rateBa,
            'rate_date' => $this->rate_date->toDateString(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
