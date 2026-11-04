<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\GeographyStatus;
use App\Domains\Admin\Models\Town;
use Illuminate\Support\Facades\DB;

class ToggleTownStatus
{
    /** Sin `$status` alterna Active/Inactive; con `$status` lo fija. */
    public function handle(Town $town, ?GeographyStatus $status = null): Town
    {
        return DB::transaction(function () use ($town, $status): Town {
            $town->update([
                'status' => $status ?? ($town->status === GeographyStatus::Active
                    ? GeographyStatus::Inactive
                    : GeographyStatus::Active),
            ]);

            return $town->refresh();
        });
    }
}
