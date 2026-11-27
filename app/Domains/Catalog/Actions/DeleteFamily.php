<?php

namespace App\Domains\Catalog\Actions;

use App\Domains\Catalog\Models\Family;
use Illuminate\Support\Facades\DB;

class DeleteFamily
{
    public function handle(Family $family): void
    {
        DB::transaction(fn () => $family->delete());
    }
}
