<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Support\UserProfileAuthorization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateUserProfile
{
    /**
     * @param  array{
     *     name: string,
     *     surname?: string|null,
     *     email: string,
     *     nif?: string|null,
     *     birth_date?: string|null,
     *     status?: bool,
     *     is_public?: bool
     * }  $data
     * @param  Company|null  $company  Si se pasa (camino company-users), el target
     *                                 MUST pertenecer a esa company — assert
     *                                 independiente de Gate::before / Super Admin.
     */
    public function handle(User $actor, User $target, array $data, ?Company $company = null): User
    {
        if ($company !== null) {
            $this->assertBelongsToCompany($company, $target);
        }

        return DB::transaction(function () use ($actor, $target, $data): User {
            $payload = [
                'name' => $data['name'],
                'surname' => $data['surname'] ?? null,
                'email' => $data['email'],
                'nif' => $data['nif'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
            ];

            if (UserProfileAuthorization::canChangeVisibilityFlags($actor, $target)) {
                if (array_key_exists('status', $data)) {
                    $payload['status'] = (bool) $data['status'];
                }
                if (array_key_exists('is_public', $data)) {
                    $payload['is_public'] = (bool) $data['is_public'];
                }
            }

            $target->forceFill($payload)->save();

            return $target->fresh() ?? $target;
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
