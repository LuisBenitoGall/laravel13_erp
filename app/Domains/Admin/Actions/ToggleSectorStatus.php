<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\SectorStatus;
use App\Domains\Admin\Models\Sector;
use Illuminate\Support\Facades\DB;

class ToggleSectorStatus
{
    public function handle(Sector $sector, ?SectorStatus $status = null): Sector
    {
        return DB::transaction(function () use ($sector, $status): Sector {
            $sector->update([
                'status' => $status ?? ($sector->status === SectorStatus::Active
                    ? SectorStatus::Inactive
                    : SectorStatus::Active),
            ]);

            return $sector->refresh();
        });
    }
}
