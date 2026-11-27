<?php

namespace App\Domains\Calendar\Actions;

use App\Domains\Admin\Models\Workplace;
use App\Domains\Calendar\Models\WorkCalendar;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FindOrCreateWorkCalendar
{
    public function handle(Workplace $workplace): WorkCalendar
    {
        return DB::transaction(function () use ($workplace): WorkCalendar {
            $existing = WorkCalendar::withTrashed()
                ->where('workplace_id', $workplace->id)
                ->first();

            if ($existing !== null) {
                if ($existing->trashed()) {
                    $existing->restore();
                }

                if ((int) $existing->company_id !== (int) $workplace->company_id) {
                    throw new InvalidArgumentException('WorkCalendar company_id does not match workplace.');
                }

                return $existing->refresh();
            }

            return WorkCalendar::query()->create([
                'company_id' => $workplace->company_id,
                'workplace_id' => $workplace->id,
            ]);
        });
    }
}
