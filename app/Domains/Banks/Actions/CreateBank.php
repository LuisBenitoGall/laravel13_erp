<?php

namespace App\Domains\Banks\Actions;

use App\Domains\Banks\Enums\BankStatus;
use App\Domains\Banks\Models\Bank;
use App\Domains\Banks\Support\SwiftCode;
use App\Support\Strings\UniqueSlug;
use Illuminate\Support\Facades\DB;

class CreateBank
{
    /** @param  array<string, mixed>  $data */
    public function handle(array $data): Bank
    {
        return DB::transaction(function () use ($data): Bank {
            $data['slug'] = UniqueSlug::from(
                (string) ($data['name'] ?? ''),
                fn (string $slug): bool => Bank::withTrashed()->where('slug', $slug)->exists(),
            );
            $data['status'] ??= BankStatus::Active->value;

            $swift = SwiftCode::normalize(
                isset($data['swift']) && is_string($data['swift']) ? $data['swift'] : null,
            );
            $data['swift'] = $swift;

            if ($swift !== null) {
                $data['swift_source'] = 'manual';
                $data['swift_updated_at'] = now();
            } else {
                $data['swift_source'] = null;
                $data['swift_updated_at'] = null;
            }

            return Bank::query()->create($data);
        });
    }
}
