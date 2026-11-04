<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\GeographyStatus;
use App\Domains\Admin\Models\Province;
use Illuminate\Support\Facades\DB;

class ToggleProvinceStatus
{
    /** Sin `$status` alterna Active/Inactive; con `$status` lo fija. */
    public function handle(Province $province, ?GeographyStatus $status = null): Province
    {
        return DB::transaction(function () use ($province, $status): Province {
            $province->update([
                'status' => $status ?? ($province->status === GeographyStatus::Active
                    ? GeographyStatus::Inactive
                    : GeographyStatus::Active),
            ]);

            return $province->refresh();
        });
    }
}
