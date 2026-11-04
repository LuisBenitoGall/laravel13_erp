<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\VatType;
use Illuminate\Support\Facades\DB;

class UpdateVatType
{
    /** @param  array<string, mixed>  $data */
    public function handle(VatType $vatType, array $data): VatType
    {
        return DB::transaction(function () use ($vatType, $data): VatType {
            $vatType->update($data);

            return $vatType->refresh();
        });
    }
}
