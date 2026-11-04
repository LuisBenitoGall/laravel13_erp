<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Unit;
use Illuminate\Support\Facades\DB;

class DeleteUnit
{
    public function handle(Unit $unit): void
    {
        DB::transaction(fn () => $unit->delete());
    }
}
