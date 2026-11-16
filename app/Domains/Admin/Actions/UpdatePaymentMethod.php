<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;

class UpdatePaymentMethod
{
    /** @param  array<string, mixed>  $data */
    public function handle(PaymentMethod $paymentMethod, array $data): PaymentMethod
    {
        return DB::transaction(function () use ($paymentMethod, $data): PaymentMethod {
            $paymentMethod->update($data);

            return $paymentMethod->refresh();
        });
    }
}
