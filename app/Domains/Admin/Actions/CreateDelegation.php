<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Workplace;
use App\Models\User;
use App\Support\Strings\UniqueSlug;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

/**
 * Alta de Delegación = Company hija (`parent_id` = matriz de sesión, fijado en servidor).
 * Reutiliza el aprovisionamiento de CreateCompany (roles, vínculo del creador, manager,
 * Basic, sync de permisos) pero SIN cuenta SaaS: nunca llama a AssignDefaultCompanyAccount
 * ni crea `company_accounts`.
 *
 * Opción A: los workplaces viven en la matriz; la Delegación solo se asocia vía
 * `workplace_delegation` (≥1 vínculo al terminar el alta).
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
     * @param  array<string, mixed>|null  $workplaceData  centro nuevo (name, address, cp, town_id); null = no crear
     * @param  list<int>  $workplaceIds  sedes existentes de la matriz a vincular
     */
    public function handle(
        Company $matrix,
        array $data,
        ?array $workplaceData,
        array $workplaceIds,
        User $creator,
    ): Company {
        return DB::transaction(function () use ($matrix, $data, $workplaceData, $workplaceIds, $creator): Company {
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

            $linkedIds = [];

            if ($workplaceData !== null && filled($workplaceData['name'] ?? null)) {
                $workplace = $this->createWorkplace->handle($workplaceData, $matrix);
                $workplace->delegations()->attach($delegation->id, ['is_preferred' => true]);
                $linkedIds[] = $workplace->id;
            }

            $existingIds = array_values(array_unique(array_map('intval', $workplaceIds)));
            if ($existingIds !== []) {
                $validCount = Workplace::query()
                    ->withoutGlobalScope('company')
                    ->where('company_id', $matrix->id)
                    ->whereIn('id', $existingIds)
                    ->count();

                if ($validCount !== count($existingIds)) {
                    throw ValidationException::withMessages([
                        'workplace_ids' => __('delegacion_sede_fuera_de_matriz'),
                    ]);
                }

                foreach ($existingIds as $workplaceId) {
                    if (in_array($workplaceId, $linkedIds, true)) {
                        continue;
                    }

                    $delegation->workplacesViaMap()->attach($workplaceId, ['is_preferred' => false]);
                    $linkedIds[] = $workplaceId;
                }
            }

            if ($linkedIds === []) {
                throw ValidationException::withMessages([
                    'workplace' => __('delegacion_requiere_centro'),
                ]);
            }

            return $delegation->refresh();
        });
    }
}
