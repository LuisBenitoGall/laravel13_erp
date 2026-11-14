<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Company;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UpdateCompanyParent
{
    public function handle(Company $company, ?Company $parent): Company
    {
        if ($company->wouldCycleWithParent($parent)) {
            throw new InvalidArgumentException(__('jerarquia_ciclo_rechazado'));
        }

        return DB::transaction(function () use ($company, $parent): Company {
            $company->update([
                'parent_id' => $parent?->id,
            ]);

            return $company->refresh();
        });
    }
}
