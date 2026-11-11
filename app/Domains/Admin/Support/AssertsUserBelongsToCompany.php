<?php

namespace App\Domains\Admin\Support;

use App\Domains\Partners\Models\Company;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Perímetro company↔user en Actions de ficha (docs y vecinos).
 */
trait AssertsUserBelongsToCompany
{
    protected function assertBelongsToCompany(Company $company, User $member): void
    {
        if (! $member->companies()->where('companies.id', $company->id)->exists()) {
            throw ValidationException::withMessages([
                'user' => __('usuario_no_vinculado_empresa'),
            ]);
        }
    }
}
