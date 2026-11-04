<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\VatTypeStatus;
use App\Domains\Admin\Models\VatType;
use Illuminate\Support\Facades\DB;

class ToggleVatTypeStatus
{
    public function handle(VatType $vatType, ?VatTypeStatus $status = null): VatType
    {
        return DB::transaction(function () use ($vatType, $status): VatType {
            $vatType->update([
                'status' => $status ?? ($vatType->status === VatTypeStatus::Active
                    ? VatTypeStatus::Inactive
                    : VatTypeStatus::Active),
            ]);

            return $vatType->refresh();
        });
    }
}
