<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\DisableModuleForCompany;
use App\Domains\Admin\Actions\EnableModuleForCompany;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\CompanyModule;
use App\Domains\Admin\Models\CompanyModuleAuthorization;
use App\Domains\Admin\Models\Module;
use App\Domains\Partners\Models\Company;
use App\Http\Controllers\Controller;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class CompanyModuleController extends Controller
{
    public function index(CurrentCompany $currentCompany): Response
    {
        abort_unless($this->userCanManageCompanyModules(), 403);

        $companyId = $currentCompany->id();
        abort_if($companyId === null, 403);

        $company = Company::query()->findOrFail($companyId);

        $authorizedIds = CompanyModuleAuthorization::query()->withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->pluck('module_id')
            ->all();

        $enabledIds = CompanyModule::query()->withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->pluck('module_id')
            ->all();

        $modules = Module::query()
            ->where('status', ModuleStatus::Active)
            ->orderBy('slug')
            ->get()
            ->map(function (Module $module) use ($authorizedIds, $enabledIds) {
                $authorized = in_array($module->id, $authorizedIds, true);

                return [
                    'id' => $module->id,
                    'slug' => $module->slug,
                    'name' => $module->name,
                    'label' => (string) $module->label,
                    'color' => $module->color,
                    'icon' => $module->icon,
                    'level' => $module->level->value,
                    'authorized' => $authorized,
                    'enabled' => $authorized && in_array($module->id, $enabledIds, true),
                ];
            })
            ->values()
            ->all();

        return Inertia::render('Admin/CompanyModules/Index', [
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
            ],
            'modules' => $modules,
        ]);
    }

    public function enable(
        Request $request,
        Module $module,
        CurrentCompany $currentCompany,
        EnableModuleForCompany $action,
    ): RedirectResponse {
        abort_unless($this->userCanManageCompanyModules(), 403);

        $company = Company::query()->findOrFail($currentCompany->id());

        try {
            $action->handle($company, $module);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('admin.company_modules.enabled'));
    }

    public function disable(
        Module $module,
        CurrentCompany $currentCompany,
        DisableModuleForCompany $action,
    ): RedirectResponse {
        abort_unless($this->userCanManageCompanyModules(), 403);

        $company = Company::query()->findOrFail($currentCompany->id());
        $action->handle($company, $module);

        return back()->with('success', __('admin.company_modules.disabled'));
    }

    private function userCanManageCompanyModules(): bool
    {
        $user = request()->user();

        if ($user === null) {
            return false;
        }

        if ($user->can('company-modules.update') || $user->can('company-modules.viewAny')) {
            return true;
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
