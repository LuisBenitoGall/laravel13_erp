<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\PaymentMethodStatus;
use App\Domains\Admin\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;

class TogglePaymentMethodStatus
{
    public function handle(PaymentMethod $paymentMethod, ?PaymentMethodStatus $status = null): PaymentMethod
    {
        return DB::transaction(function () use ($paymentMethod, $status): PaymentMethod {
            $paymentMethod->update([
                'status' => $status ?? ($paymentMethod->status === PaymentMethodStatus::Active
                    ? PaymentMethodStatus::Inactive
                    : PaymentMethodStatus::Active),
            ]);

            return $paymentMethod->refresh();
        });
    }
}
