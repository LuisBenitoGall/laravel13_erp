<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\GeographyStatus;
use App\Domains\Admin\Models\Country;
use Illuminate\Support\Facades\DB;

class ToggleCountryStatus
{
    /** Sin `$status` alterna Active/Inactive; con `$status` lo fija. */
    public function handle(Country $country, ?GeographyStatus $status = null): Country
    {
        return DB::transaction(function () use ($country, $status): Country {
            $country->update([
                'status' => $status ?? ($country->status === GeographyStatus::Active
                    ? GeographyStatus::Inactive
                    : GeographyStatus::Active),
            ]);

            return $country->refresh();
        });
    }
}
