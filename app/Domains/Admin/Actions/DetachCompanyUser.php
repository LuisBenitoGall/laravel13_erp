<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Partners\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DetachCompanyUser
{
    public function handle(Company $company, User $member): void
    {
        if (! $member->companies()->where('companies.id', $company->id)->exists()) {
            throw ValidationException::withMessages([
                'user' => __('usuario_no_vinculado_empresa'),
            ]);
        }

        DB::transaction(function () use ($company, $member): void {
            $company->users()->detach($member->id);
        });
    }
}
