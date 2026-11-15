<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\WorkplaceStatus;
use App\Domains\Admin\Models\Workplace;
use Illuminate\Support\Facades\DB;

class ToggleWorkplaceStatus
{
    public function handle(Workplace $workplace, ?WorkplaceStatus $status = null): Workplace
    {
        return DB::transaction(function () use ($workplace, $status): Workplace {
            $workplace->update([
                'status' => $status ?? ($workplace->status === WorkplaceStatus::Active
                    ? WorkplaceStatus::Inactive
                    : WorkplaceStatus::Active),
            ]);

            return $workplace->refresh();
        });
    }
}
