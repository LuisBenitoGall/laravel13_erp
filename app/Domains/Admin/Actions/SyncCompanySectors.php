<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\SectorStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Sector;
use Illuminate\Support\Facades\DB;

class SyncCompanySectors
{
    /**
     * @param  list<int|string>  $sectorIds
     */
    public function handle(Company $company, array $sectorIds): Company
    {
        return DB::transaction(function () use ($company, $sectorIds): Company {
            $ids = array_values(array_unique(array_map('intval', $sectorIds)));

            $validIds = Sector::query()
                ->where('status', SectorStatus::Active)
                ->whereIn('id', $ids)
                ->pluck('id')
                ->all();

            $company->sectors()->sync($validIds);

            return $company->refresh();
        });
    }
}
