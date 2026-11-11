<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Partners\Models\Company;
use App\Models\User;
use App\Models\UserEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteUserEmail
{
    /**
     * @param  Company|null  $company  Camino company-users: assert perímetro del owner.
     */
    public function handle(UserEmail $email, ?Company $company = null): void
    {
        $owner = $email->user;
        if ($owner === null) {
            throw ValidationException::withMessages([
                'user' => __('usuario_no_vinculado_empresa'),
            ]);
        }

        if ($company !== null) {
            $this->assertBelongsToCompany($company, $owner);
        }

        DB::transaction(function () use ($email): void {
            $email->delete();
        });
    }

    private function assertBelongsToCompany(Company $company, User $member): void
    {
        if (! $member->companies()->where('companies.id', $company->id)->exists()) {
            throw ValidationException::withMessages([
                'user' => __('usuario_no_vinculado_empresa'),
            ]);
        }
    }
}
