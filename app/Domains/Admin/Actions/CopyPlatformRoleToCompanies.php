<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Support\CompanyRolePermissionCatalog;
use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

class CopyPlatformRoleToCompanies
{
    public function __construct(
        private readonly CompanyRolePermissionCatalog $catalog,
        private readonly SyncCompanyRoleModuleAccess $syncModuleAccess,
    ) {}

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
        /** @var list<string> $sourcePermissionNames */
        $sourcePermissionNames = $source->permissions->pluck('name')->values()->all();

        return DB::transaction(function () use ($source, $allowedIds, $sourcePermissionNames, $teamKey) {
            $created = [];

            foreach ($allowedIds as $companyId) {
                /** @var Company $destination */
                $destination = Company::query()->findOrFail($companyId);
                $filtered = $this->catalog->filterPermissionNames($destination, $sourcePermissionNames);
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

                    if ($filtered !== []) {
                        $local->syncPermissions($filtered);
                    }

                    foreach ($this->moduleSlugsForPermissions($filtered) as $moduleSlug) {
                        $this->syncModuleAccess->handle($local, $moduleSlug);
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

    /**
     * @param  list<string>  $permissionNames
     * @return list<string>
     */
    private function moduleSlugsForPermissions(array $permissionNames): array
    {
        $slugs = [];

        foreach ($permissionNames as $name) {
            $parts = explode('.', $name, 2);
            if (count($parts) !== 2 || $parts[1] === 'access') {
                continue;
            }

            $functionality = Functionality::query()
                ->where('slug', $parts[0])
                ->with('module:id,slug')
                ->first();

            $moduleSlug = $functionality?->module?->slug;
            if ($moduleSlug !== null) {
                $slugs[$moduleSlug] = true;
            }
        }

        return array_keys($slugs);
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
