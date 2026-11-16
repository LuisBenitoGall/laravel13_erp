<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Enums\WorkplaceStatus;
use App\Domains\Admin\Http\Resources\CompanyResource;
use App\Domains\Admin\Http\Resources\WorkplaceResource;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Workplace;
use App\Domains\Admin\Services\CompanySetupReadiness;
use App\Http\Controllers\Controller;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanySettingsController extends Controller
{
    public function index(Request $request, CompanySetupReadiness $readiness): Response
    {
        $this->authorize('viewAnyCompanySettings');

        $company = $this->currentCompany();
        $evaluation = $readiness->evaluate($company);

        $workplaces = Workplace::query()
            ->where('company_id', $company->id)
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [WorkplaceStatus::Active->value])
            ->orderBy('name')
            ->get();

        $wizard = $request->boolean('wizard');

        return Inertia::render('Admin/CompanySettings/Index', [
            'company' => CompanyResource::make($company)->resolve(),
            'workplaces' => WorkplaceResource::collection($workplaces)->resolve(),
            'setup' => $evaluation,
            'wizard' => $wizard,
            'can' => [
                'update_company' => $request->user()?->can('update', $company) ?? false,
                'create_workplace' => $request->user()?->can('create', Workplace::class) ?? false,
            ],
        ]);
    }

    public function dismissModal(Request $request): RedirectResponse
    {
        $this->authorize('viewAnyCompanySettings');

        $request->session()->forget('company_setup.show_modal');

        return redirect()
            ->route('dashboard')
            ->with('success', __('configuracion_empresa_pospuesta'));
    }

    private function currentCompany(): Company
    {
        $companyId = app(CurrentCompany::class)->id();
        abort_if($companyId === null, 403);

        $company = Company::query()->find($companyId);
        abort_if($company === null, 403);

        return $company;
    }
}
