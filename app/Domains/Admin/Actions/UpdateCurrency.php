<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Currency;
use App\Support\Strings\UniqueSlug;
use Illuminate\Support\Facades\DB;

class UpdateCurrency
{
    /**
     * D4: `code` y `number` son inmutables; el slug se regenera solo si cambia el nombre.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(Currency $currency, array $data): Currency
    {
        return DB::transaction(function () use ($currency, $data): Currency {
            unset($data['slug'], $data['code'], $data['number']);

            $name = $data['name'] ?? $currency->name;

            if ($currency->slug === null || $currency->slug === '' || $name !== $currency->name) {
                $data['slug'] = UniqueSlug::from(
                    (string) $name,
                    fn (string $slug): bool => Currency::withTrashed()
                        ->whereKeyNot($currency->id)
                        ->where('slug', $slug)
                        ->exists(),
                );
            }

            $currency->update($data);

            return $currency->refresh();
        });
    }
}
