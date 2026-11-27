<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\TableFilterPreset;
use Illuminate\Support\Facades\DB;

class DeleteTableFilterPreset
{
    public function handle(TableFilterPreset $preset): void
    {
        DB::transaction(fn () => $preset->delete());
    }
}
