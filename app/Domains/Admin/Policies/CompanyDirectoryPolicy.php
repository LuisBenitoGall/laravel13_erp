<?php

namespace App\Domains\Admin\Policies;

use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Enums\SectorStatus;
use App\Domains\Admin\Models\Company;
use App\Models\User;

/**
 * Directorio de descubrimiento: Spatie `company-directory.view`.
 * Universo (Show 404): Active + NIF trim + ≥1 sector Active (no forUser).
 */
class CompanyDirectoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('company-directory.view');
    }

    public function view(User $user, Company $company): bool
    {
        return $user->can('company-directory.view');
    }

    public static function inUniverse(Company $company): bool
    {
        if ($company->status !== CompanyStatus::Active) {
            return false;
        }

        $nif = trim((string) ($company->nif ?? ''));
        if ($nif === '') {
            return false;
        }

        return $company->sectors()
            ->where('sectors.status', SectorStatus::Active)
            ->exists();
    }
}
