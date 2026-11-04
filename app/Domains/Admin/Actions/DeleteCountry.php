<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Country;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteCountry
{
    /** D7: se bloquea el borrado si hay provincias no eliminadas (sin huérfanos silenciosos). */
    public function handle(Country $country): void
    {
        if ($country->provinces()->exists()) {
            throw ValidationException::withMessages([
                'country' => __('pais_con_provincias'),
            ]);
        }

        if ($country->vatTypes()->exists()) {
            throw ValidationException::withMessages([
                'country' => __('pais_con_tipos_iva'),
            ]);
        }

        DB::transaction(fn () => $country->delete());
    }
}
