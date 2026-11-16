<?php

namespace App\Domains\Admin\Policies;

use App\Models\User;

/**
 * Hub Configuración de la empresa: Spatie `company-settings.view` / `.update`.
 */
class CompanySettingsPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('company-settings.view');
    }

    public function update(User $user): bool
    {
        return $user->can('company-settings.update');
    }
}
