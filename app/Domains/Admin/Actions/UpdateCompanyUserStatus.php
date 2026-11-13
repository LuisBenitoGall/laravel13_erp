<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateCompanyUserStatus
{
    public function handle(Company $company, User $member, bool $status): User
    {
        if (! $member->companies()->where('companies.id', $company->id)->exists()) {
            throw ValidationException::withMessages([
                'user' => __('usuario_no_vinculado_empresa'),
            ]);
        }

        return DB::transaction(function () use ($member, $status): User {
            $member->forceFill(['status' => $status])->save();

            return $member->refresh();
        });
    }
}
