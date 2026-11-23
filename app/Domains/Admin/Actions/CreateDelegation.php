<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Models\Company;
use App\Models\User;
use App\Support\Strings\UniqueSlug;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * Alta de Delegación = Company hija (`parent_id` = matriz de sesión, fijado en servidor).
 * Reutiliza el aprovisionamiento de CreateCompany (roles, vínculo del creador, manager,
 * Basic, sync de permisos) pero SIN cuenta SaaS: nunca llama a AssignDefaultCompanyAccount
 * ni crea `company_accounts`. Exige ≥1 Workplace Active (company_id explícito).
 */
class CreateDelegation
{
    public function __construct(
        private readonly EnsureCompanyDefaultRoles $ensureCompanyDefaultRoles,
        private readonly AuthorizeBasicModulesForCompany $authorizeBasicModulesForCompany,
        private readonly SyncManagerPermissionsForCompany $syncManagerPermissionsForCompany,
        private readonly CreateWorkplace $createWorkplace,
    ) {}

    /**
     * @param  array<string, mixed>  $data  name, tradename, nif, status
     * @param  array<string, mixed>  $workplaceData  primer Workplace (name, address, cp, town_id)
     */
    public function handle(Company $matrix, array $data, array $workplaceData, User $creator): Company
    {
        return DB::transaction(function () use ($matrix, $data, $workplaceData, $creator): Company {
            $payload = [
                'name' => $data['name'],
                'tradename' => $data['tradename'] ?? null,
                'nif' => $data['nif'],
                'status' => $data['status'] ?? CompanyStatus::Active->value,
                'parent_id' => $matrix->id,
                'slug' => UniqueSlug::from(
                    (string) ($data['name'] ?? ''),
                    fn (string $slug): bool => Company::withTrashed()->where('slug', $slug)->exists(),
                ),
            ];

            $delegation = Company::query()->create($payload);

            $this->ensureCompanyDefaultRoles->handle($delegation);

            if (! $creator->companies()->where('companies.id', $delegation->id)->exists()) {
                $creator->companies()->attach($delegation->id);
            }

            app()[PermissionRegistrar::class]->forgetCachedPermissions();
            $previousTeamId = getPermissionsTeamId();
            setPermissionsTeamId($delegation->id);

            try {
                if (! $creator->hasRole('manager')) {
                    $creator->assignRole('manager');
                }
            } finally {
                setPermissionsTeamId($previousTeamId);
                $creator->unsetRelation('roles');
                $creator->unsetRelation('permissions');
            }

            if ($delegation->status === CompanyStatus::Active) {
                $this->authorizeBasicModulesForCompany->handle($delegation);
            }

            $this->syncManagerPermissionsForCompany->handle($delegation);

            $this->createWorkplace->handle($workplaceData, $delegation);

            return $delegation->refresh();
        });
    }
}
