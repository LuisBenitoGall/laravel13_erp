<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\UnitStatus;
use App\Domains\Admin\Models\Unit;
use Illuminate\Support\Facades\DB;

class ToggleUnitStatus
{
    public function handle(Unit $unit, ?UnitStatus $status = null): Unit
    {
        return DB::transaction(function () use ($unit, $status): Unit {
            $unit->update([
                'status' => $status ?? ($unit->status === UnitStatus::Active
                    ? UnitStatus::Inactive
                    : UnitStatus::Active),
            ]);

            return $unit->refresh();
        });
    }
}
