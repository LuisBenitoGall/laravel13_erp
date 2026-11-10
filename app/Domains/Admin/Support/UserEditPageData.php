<?php

namespace App\Domains\Admin\Support;

use App\Domains\Partners\Models\Company;
use App\Models\User;
use Illuminate\Support\Carbon;
use Spatie\Permission\PermissionRegistrar;

final class UserEditPageData
{
    /**
     * @return array{
     *     context: 'profile'|'company',
     *     user: array<string, mixed>,
     *     company_role: string|null,
     *     emails: list<array{value: string, label: string|null}>,
     *     phones: list<array{value: string, label: string|null}>,
     *     can: array{update_profile: bool, change_password: bool, change_visibility_flags: bool}
     * }
     */
    public function build(User $user, User $actor, string $context, ?Company $company = null): array
    {
        $isSelf = $actor->id === $user->id;

        return [
            'context' => $context === 'company' ? 'company' : 'profile',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'surname' => $user->surname,
                'email' => $user->email,
                'nif' => $user->nif,
                'birth_date' => $user->birth_date !== null
                    ? Carbon::parse($user->birth_date)->format('Y-m-d')
                    : null,
                'status' => (bool) $user->status,
                'is_public' => (bool) $user->is_public,
                'created_at' => $user->created_at?->toIso8601String(),
                'updated_at' => $user->updated_at?->toIso8601String(),
            ],
            'company_role' => $context === 'company' && $company !== null
                ? $this->companyRoleName($user, $company)
                : null,
            'emails' => [],
            'phones' => [],
            'can' => [
                'update_profile' => true,
                'change_password' => $isSelf,
                'change_visibility_flags' => UserProfileAuthorization::canChangeVisibilityFlags($actor, $user),
            ],
        ];
    }

    private function companyRoleName(User $user, Company $company): ?string
    {
        $previousTeamId = getPermissionsTeamId();
        setPermissionsTeamId($company->id);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        try {
            $user->unsetRelation('roles');
            $teamKey = (string) config('permission.column_names.team_foreign_key');
            $rolesTable = (string) config('permission.table_names.roles');

            $name = $user->roles()
                ->where("{$rolesTable}.{$teamKey}", $company->id)
                ->orderBy('name')
                ->value('name');

            return is_string($name) ? $name : null;
        } finally {
            setPermissionsTeamId($previousTeamId);
            $user->unsetRelation('roles');
        }
    }
}
