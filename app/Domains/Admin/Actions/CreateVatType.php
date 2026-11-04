<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\VatTypeStatus;
use App\Domains\Admin\Models\VatType;
use Illuminate\Support\Facades\DB;

class CreateVatType
{
    /** @param  array<string, mixed>  $data */
    public function handle(array $data): VatType
    {
        return DB::transaction(function () use ($data): VatType {
            $data['status'] ??= VatTypeStatus::Active->value;

            return VatType::query()->create($data);
        });
    }
}
