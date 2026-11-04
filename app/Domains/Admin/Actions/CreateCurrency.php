<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\CurrencyStatus;
use App\Domains\Admin\Models\Currency;
use App\Support\Strings\UniqueSlug;
use Illuminate\Support\Facades\DB;

class CreateCurrency
{
    /** @param  array<string, mixed>  $data */
    public function handle(array $data): Currency
    {
        return DB::transaction(function () use ($data): Currency {
            $data['slug'] = UniqueSlug::from(
                (string) ($data['name'] ?? ''),
                fn (string $slug): bool => Currency::withTrashed()->where('slug', $slug)->exists(),
            );
            $data['status'] ??= CurrencyStatus::Active->value;

            return Currency::query()->create($data);
        });
    }
}
