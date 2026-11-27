<?php

namespace App\Domains\Catalog\Actions;

use App\Domains\Catalog\Models\Family;
use Illuminate\Support\Facades\DB;

class UpdateFamily
{
    /** @param  array<string, mixed>  $data */
    public function handle(Family $family, array $data): Family
    {
        return DB::transaction(function () use ($family, $data): Family {
            // Slug inmutable tras create (design D2).
            unset($data['slug']);

            $family->update($data);

            return $family->refresh();
        });
    }
}
