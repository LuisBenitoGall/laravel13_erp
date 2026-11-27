<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Company;
use Illuminate\Support\Facades\DB;

/**
 * Marca ≤1 sede preferida de la Delegación (`is_preferred_seat`).
 * No toca `is_preferred` (preferida por sede). El vínculo ya lo valida el FormRequest.
 */
class SetDelegationPreferredWorkplace
{
    public function handle(Company $delegation, ?int $workplaceId): Company
    {
        return DB::transaction(function () use ($delegation, $workplaceId): Company {
            DB::table('workplace_delegation')
                ->where('company_id', $delegation->id)
                ->where('is_preferred_seat', true)
                ->update(['is_preferred_seat' => false, 'updated_at' => now()]);

            if ($workplaceId !== null) {
                DB::table('workplace_delegation')
                    ->where('company_id', $delegation->id)
                    ->where('workplace_id', $workplaceId)
                    ->update(['is_preferred_seat' => true, 'updated_at' => now()]);
            }

            $delegation->touch();

            return $delegation->refresh();
        });
    }
}
