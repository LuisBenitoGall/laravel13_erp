<?php

namespace App\Domains\HR\Actions;

use App\Domains\HR\Enums\ContractTypeStatus;
use App\Domains\HR\Models\ContractType;
use App\Support\Strings\UniqueSlug;
use Illuminate\Support\Facades\DB;

class CreateContractType
{
    /** @param  array<string, mixed>  $data */
    public function handle(array $data): ContractType
    {
        return DB::transaction(function () use ($data): ContractType {
            $data['slug'] = UniqueSlug::from(
                (string) ($data['name'] ?? ''),
                fn (string $slug): bool => ContractType::withTrashed()->where('slug', $slug)->exists(),
            );
            $data['status'] ??= ContractTypeStatus::Active->value;

            return ContractType::query()->create($data);
        });
    }
}
