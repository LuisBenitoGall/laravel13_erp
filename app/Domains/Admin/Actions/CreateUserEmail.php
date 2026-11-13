<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Company;
use App\Models\User;
use App\Models\UserEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateUserEmail
{
    /**
     * @param  array{email: string, description?: string|null}  $data
     * @param  Company|null  $company  Camino company-users: assert perímetro.
     */
    public function handle(User $owner, array $data, ?Company $company = null): UserEmail
    {
        if ($company !== null) {
            $this->assertBelongsToCompany($company, $owner);
        }

        return DB::transaction(function () use ($owner, $data): UserEmail {
            return UserEmail::query()->create([
                'user_id' => $owner->id,
                'email' => $data['email'],
                'description' => $data['description'] ?? null,
            ]);
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
