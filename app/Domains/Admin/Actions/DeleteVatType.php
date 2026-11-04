<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\VatType;
use Illuminate\Support\Facades\DB;

class DeleteVatType
{
    public function handle(VatType $vatType): void
    {
        DB::transaction(fn () => $vatType->delete());
    }
}
