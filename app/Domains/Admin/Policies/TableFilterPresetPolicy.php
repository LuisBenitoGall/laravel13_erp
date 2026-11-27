<?php

namespace App\Domains\Admin\Policies;

use App\Domains\Admin\Models\TableFilterPreset;
use App\Models\User;
use App\Support\Tenancy\CurrentCompany;

/**
 * Presets personales de filtros: sin Spatie Functionality (como Preference).
 */
class TableFilterPresetPolicy
{
    public function viewAny(User $user): bool
    {
        return app(CurrentCompany::class)->id() !== null;
    }

    public function create(User $user): bool
    {
        return app(CurrentCompany::class)->id() !== null;
    }

    public function update(User $user, TableFilterPreset $preset): bool
    {
        return (int) $preset->user_id === (int) $user->id
            && $preset->belongsToCurrentCompany();
    }

    public function delete(User $user, TableFilterPreset $preset): bool
    {
        return (int) $preset->user_id === (int) $user->id
            && $preset->belongsToCurrentCompany();
    }
}
