<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\AccountingAccountType;
use Illuminate\Support\Facades\DB;

class CreateAccountingAccountType
{
    /** @param  array<string, mixed>  $data */
    public function handle(array $data): AccountingAccountType
    {
        return DB::transaction(function () use ($data): AccountingAccountType {
            $data['autoreference'] = 0;
            unset($data['mode']);

            return AccountingAccountType::query()->create($data);
        });
    }
}
