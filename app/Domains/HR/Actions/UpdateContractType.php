<?php

namespace App\Domains\HR\Actions;

use App\Domains\HR\Models\ContractType;
use Illuminate\Support\Facades\DB;

class UpdateContractType
{
    /** @param  array<string, mixed>  $data */
    public function handle(ContractType $contractType, array $data): ContractType
    {
        return DB::transaction(function () use ($contractType, $data): ContractType {
            // Slug inmutable tras create (design §3).
            unset($data['slug']);

            $contractType->update($data);

            return $contractType->refresh();
        });
    }
}
