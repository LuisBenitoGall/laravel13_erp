<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Company;
use Illuminate\Support\Facades\DB;

/**
 * Sincroniza monedas de uso de una Company: set permitido + ≤1 default.
 */
class SyncCompanyCurrencies
{
    /**
     * @param  list<int|string>  $currencyIds
     */
    public function handle(Company $company, array $currencyIds, ?int $defaultCurrencyId): Company
    {
        return DB::transaction(function () use ($company, $currencyIds, $defaultCurrencyId): Company {
            $ids = array_values(array_unique(array_map('intval', $currencyIds)));

            if ($ids === []) {
                $company->currencies()->detach();
                $company->touch();

                return $company->refresh();
            }

            $sync = [];
            foreach ($ids as $id) {
                $sync[$id] = [
                    'is_default' => $defaultCurrencyId !== null && $id === $defaultCurrencyId,
                ];
            }

            $company->currencies()->sync($sync);
            // V-20: Auditable en Company (updated_by / updated_at).
            $company->touch();

            return $company->refresh();
        });
    }
}
