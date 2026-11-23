<?php

namespace App\Domains\Admin\Support;

use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Workplace;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Database\Eloquent\Builder;

/**
 * Perímetro del CRUD de Delegaciones: hijas (`parent_id`) de la matriz de sesión.
 * La sesión debe ser matriz; Super Admin no bypasea (Gate::before no llega aquí).
 */
final class DelegationPerimeter
{
    public function __construct(
        private readonly CurrentCompany $currentCompany,
    ) {}

    /** Matriz de sesión; 403 si no hay sesión o la sesión es una Delegación. */
    public function matrix(): Company
    {
        $id = $this->currentCompany->id();
        $company = $id !== null ? Company::query()->find($id) : null;

        abort_unless($company !== null && $company->parent_id === null, 403, __('delegacion_requiere_matriz'));

        return $company;
    }

    /** @return Builder<Company> */
    public function query(): Builder
    {
        return Company::query()->where('parent_id', $this->matrix()->id);
    }

    /** 404 si la company no es hija de la matriz de sesión. */
    public function assertOwns(Company $delegation): void
    {
        abort_unless($delegation->parent_id === $this->matrix()->id, 404);
    }

    /** Workplace de la delegación con company_id explícito (sin cambiar CurrentCompany). */
    public function workplace(Company $delegation, int|string $workplaceId): Workplace
    {
        $this->assertOwns($delegation);

        return Workplace::query()
            ->withoutGlobalScope('company')
            ->where('company_id', $delegation->id)
            ->findOrFail($workplaceId);
    }
}
