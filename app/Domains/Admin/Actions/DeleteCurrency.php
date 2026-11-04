<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Currency;
use Illuminate\Support\Facades\DB;

class DeleteCurrency
{
    /** D8: soft delete; hoy no hay consumidores de Currency en v2. */
    public function handle(Currency $currency): void
    {
        DB::transaction(fn () => $currency->delete());
    }
}
