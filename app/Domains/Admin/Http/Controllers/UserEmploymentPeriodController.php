<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Http\Requests\UserEmploymentPeriodTableRequest;
use App\Domains\Admin\Http\Resources\UserEmploymentPeriodResource;
use App\Domains\Admin\Models\Company;
use App\Domains\HR\Models\Employee;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Table\TableBuilder;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Http\JsonResponse;

/**
 * Listado RO de periodos Employee anclado al User de la ficha (R-TEN-10).
 * Authz = acceso a la ficha; no exige employees.view.
 */
class UserEmploymentPeriodController extends Controller
{
    public function tableForProfile(
        UserEmploymentPeriodTableRequest $request,
        TableBuilder $table,
    ): JsonResponse {
        /** @var User $owner */
        $owner = $request->user();

        return $this->periodsTable($table, $request, $owner);
    }

    public function tableForCompanyUser(
        UserEmploymentPeriodTableRequest $request,
        User $user,
        TableBuilder $table,
    ): JsonResponse {
        $this->authorize('updateCompanyUser', $user);
        $company = $this->currentCompany();
        abort_unless(
            $user->companies()->where('companies.id', $company->id)->exists(),
            403,
        );

        return $this->periodsTable($table, $request, $user);
    }

    private function periodsTable(
        TableBuilder $table,
        UserEmploymentPeriodTableRequest $request,
        User $owner,
    ): JsonResponse {
        // Hub User readout (R-TEN-10): all companies of the subject, read-only.
        return $table->for(
            Employee::withoutGlobalScope('company')
                ->select('employees.*')
                ->leftJoin('companies', 'companies.id', '=', 'employees.company_id')
                ->leftJoin('workplaces', 'workplaces.id', '=', 'employees.workplace_id')
                ->leftJoin('contract_types', 'contract_types.id', '=', 'employees.contract_type_id')
                ->where('employees.user_id', $owner->id)
                ->with(['company', 'workplace', 'contractType'])
                ->orderByDesc('employees.start_date')
                ->orderByDesc('employees.id'),
        )
            ->searchable([
                'companies.name',
                'workplaces.name',
                'contract_types.name',
                'employees.job_title',
            ])
            ->request($request)
            ->through(UserEmploymentPeriodResource::class)
            ->json();
    }

    private function currentCompany(): Company
    {
        $companyId = app(CurrentCompany::class)->id();
        abort_if($companyId === null, 404);

        return Company::query()->findOrFail($companyId);
    }
}
