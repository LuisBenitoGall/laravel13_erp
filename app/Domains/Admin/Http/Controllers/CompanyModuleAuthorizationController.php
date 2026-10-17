<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\AuthorizeModuleForCompany;
use App\Domains\Admin\Actions\RevokeModuleAuthorizationForCompany;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\CompanyModuleAuthorization;
use App\Domains\Admin\Models\Module;
use App\Domains\Partners\Enums\CompanyStatus;
use App\Domains\Partners\Models\Company;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanyModuleAuthorizationController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($this->isSuperAdmin(), 403);

        $companyId = (int) $request->query('company_id', 0);
        $companies = Company::query()
            ->where('status', CompanyStatus::Active)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $selected = $companyId > 0
            ? $companies->firstWhere('id', $companyId)
            : $companies->first();

        $authorizedIds = [];
        $modules = [];

        if ($selected !== null) {
            $authorizedIds = CompanyModuleAuthorization::query()->withoutGlobalScope('company')
                ->where('company_id', $selected->id)
                ->pluck('module_id')
                ->all();

            $modules = Module::query()
                ->where('status', ModuleStatus::Active)
                ->orderBy('slug')
                ->get()
                ->map(fn (Module $module) => [
                    'id' => $module->id,
                    'slug' => $module->slug,
                    'label' => (string) $module->label,
                    'level' => $module->level->value,
                    'color' => $module->color,
                    'icon' => $module->icon,
                    'authorized' => in_array($module->id, $authorizedIds, true),
                ])
                ->values()
                ->all();
        }

        return Inertia::render('Admin/CompanyModuleAuthorizations/Index', [
            'companies' => $companies,
            'selected_company_id' => $selected?->id,
            'modules' => $modules,
        ]);
    }

    public function authorizeModule(
        Company $company,
        Module $module,
        AuthorizeModuleForCompany $action,
    ): RedirectResponse {
        abort_unless($this->isSuperAdmin(), 403);
        $action->handle($company, $module);

        return back()->with('success', __('modulo_autorizado'));
    }

    public function revoke(
        Company $company,
        Module $module,
        RevokeModuleAuthorizationForCompany $action,
    ): RedirectResponse {
        abort_unless($this->isSuperAdmin(), 403);
        $action->handle($company, $module);

        return back()->with('success', __('autorizacion_revocada'));
    }

    private function isSuperAdmin(): bool
    {
        $user = request()->user();

        if ($user === null) {
            return false;
        }

        $previous = getPermissionsTeamId();
        setPermissionsTeamId(null);

        try {
            return $user->hasRole('Super Admin');
        } finally {
            setPermissionsTeamId($previous);
        }
    }
}
