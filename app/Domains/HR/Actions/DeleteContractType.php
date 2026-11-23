<?php

namespace App\Domains\HR\Actions;

use App\Domains\HR\Models\ContractType;
use Illuminate\Support\Facades\DB;

class DeleteContractType
{
    public function handle(ContractType $contractType): void
    {
        DB::transaction(fn () => $contractType->delete());
    }
}
