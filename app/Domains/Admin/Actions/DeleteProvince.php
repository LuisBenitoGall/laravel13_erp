<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Province;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteProvince
{
    /** D7: se bloquea el borrado si hay poblaciones no eliminadas. */
    public function handle(Province $province): void
    {
        if ($province->towns()->exists()) {
            throw ValidationException::withMessages([
                'province' => __('provincia_con_poblaciones'),
            ]);
        }

        DB::transaction(fn () => $province->delete());
    }
}
