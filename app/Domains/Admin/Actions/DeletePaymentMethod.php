<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;

class DeletePaymentMethod
{
    public function handle(PaymentMethod $paymentMethod): void
    {
        DB::transaction(fn () => $paymentMethod->delete());
    }
}
