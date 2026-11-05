<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Role;
use App\Domains\Partners\Enums\CompanyStatus;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

class CopyPlatformRoleToCompanies
{
    /**
     * @param  list<int>  $companyIds
     * @return list<array{company_id: int, role_id: int, name: string}>
     */
    public function handle(Role $source, User $actor, array $companyIds): array
    {
        $teamKey = (string) config('permission.column_names.team_foreign_key');

        if ($source->isSuperAdmin() || $source->getAttribute($teamKey) !== null) {
            throw ValidationException::withMessages([
                'role' => __('rol_super_admin_inmutable'),
            ]);
        }

        if ($source->universal) {
            throw ValidationException::withMessages([
                'role' => __('rol_universal_no_copiable'),
            ]);
        }

        $companyIds = array_values(array_unique(array_map('intval', $companyIds)));

        if ($companyIds === []) {
            throw ValidationException::withMessages([
                'company_ids' => __('rol_copy_companies_requeridas'),
            ]);
        }

        $currentCompanyId = app(CurrentCompany::class)->id();

        if ($currentCompanyId !== null && in_array($currentCompanyId, $companyIds, true)) {
            throw ValidationException::withMessages([
                'company_ids' => __('rol_copy_excluye_empresa_sesion'),
            ]);
        }

        $linkedActiveCount = Company::query()
            ->forUser($actor)
            ->where('status', CompanyStatus::Active)
            ->count();

        if ($linkedActiveCount <= 1) {
            throw ValidationException::withMessages([
                'company_ids' => __('rol_copy_requiere_varias_empresas'),
            ]);
        }

        $allowedIds = Company::query()
            ->forUser($actor)
            ->where('status', CompanyStatus::Active)
            ->when(
                $currentCompanyId !== null,
                fn ($query) => $query->where('id', '!=', $currentCompanyId),
            )
            ->whereIn('id', $companyIds)
            ->pluck('id')
            ->all();

        if (count($allowedIds) !== count($companyIds)) {
            throw ValidationException::withMessages([
                'company_ids' => __('rol_copy_companies_fuera_perimetro'),
            ]);
        }

        $source->loadMissing('permissions');
        /** @var list<string> $permissionNames */
        $permissionNames = $source->permissions->pluck('name')->values()->all();

        return DB::transaction(function () use ($source, $allowedIds, $permissionNames, $teamKey) {
            $created = [];

            foreach ($allowedIds as $companyId) {
                $name = $this->uniqueLocalName($source->name, $companyId, $teamKey);

                app()[PermissionRegistrar::class]->forgetCachedPermissions();
                $previousTeamId = getPermissionsTeamId();
                setPermissionsTeamId($companyId);

                try {
                    /** @var Role $local */
                    $local = Role::query()->create([
                        'name' => $name,
                        'guard_name' => 'web',
                        'universal' => false,
                        'description' => $source->description,
                        $teamKey => $companyId,
                    ]);

                    if ($permissionNames !== []) {
                        $local->syncPermissions($permissionNames);
                    }

                    $created[] = [
                        'company_id' => $companyId,
                        'role_id' => (int) $local->id,
                        'name' => $name,
                    ];
                } finally {
                    setPermissionsTeamId($previousTeamId);
                }
            }

            return $created;
        });
    }

    private function uniqueLocalName(string $base, int $companyId, string $teamKey): string
    {
        $exists = fn (string $candidate): bool => Role::query()
            ->where($teamKey, $companyId)
            ->where('name', $candidate)
            ->exists();

        if (! $exists($base)) {
            return $base;
        }

        $n = 2;
        while ($exists("{$base} ({$n})")) {
            $n++;
        }

        return "{$base} ({$n})";
    }
}
