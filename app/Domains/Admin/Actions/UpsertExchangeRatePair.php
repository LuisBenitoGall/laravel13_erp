<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\ExchangeRate;
use Illuminate\Support\Facades\DB;

/**
 * Upserta ambos sentidos de un par dirigido en una sola transacción (sin fecha).
 * Los sentidos con rate null/vacío se omiten (no se crean ni borran).
 */
class UpsertExchangeRatePair
{
    /** @param  array<string, mixed>  $data */
    public function handle(array $data): void
    {
        DB::transaction(function () use ($data): void {
            $fromId = (int) $data['from_currency_id'];
            $toId = (int) $data['to_currency_id'];

            $rateAb = $data['rate_ab'] ?? null;
            $rateBa = $data['rate_ba'] ?? null;

            if ($rateAb !== null && $rateAb !== '') {
                $this->upsertRow($fromId, $toId, (string) $rateAb);
            }

            if ($rateBa !== null && $rateBa !== '') {
                $this->upsertRow($toId, $fromId, (string) $rateBa);
            }
        });
    }

    private function upsertRow(int $fromId, int $toId, string $rate): void
    {
        $existing = ExchangeRate::withTrashed()
            ->where('from_currency_id', $fromId)
            ->where('to_currency_id', $toId)
            ->first();

        if ($existing !== null) {
            if ($existing->trashed()) {
                $existing->restore();
            }

            $existing->update(['rate' => $rate]);
        } else {
            ExchangeRate::query()->create([
                'from_currency_id' => $fromId,
                'to_currency_id' => $toId,
                'rate' => $rate,
            ]);
        }
    }
}
