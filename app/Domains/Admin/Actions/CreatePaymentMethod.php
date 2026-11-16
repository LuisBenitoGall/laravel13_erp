<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\PaymentMethodStatus;
use App\Domains\Admin\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;

class CreatePaymentMethod
{
    /** @param  array<string, mixed>  $data */
    public function handle(array $data): PaymentMethod
    {
        return DB::transaction(function () use ($data): PaymentMethod {
            $data['status'] ??= PaymentMethodStatus::Active->value;

            return PaymentMethod::query()->create($data);
        });
    }
}
