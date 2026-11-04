<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\AccountingAccountType;
use Illuminate\Support\Facades\DB;

class UpdateAccountingAccountType
{
    /** @param  array<string, mixed>  $data */
    public function handle(AccountingAccountType $type, array $data): AccountingAccountType
    {
        return DB::transaction(function () use ($type, $data): AccountingAccountType {
            unset($data['code'], $data['autoreference'], $data['mode']);

            $type->update($data);

            return $type->refresh();
        });
    }
}
