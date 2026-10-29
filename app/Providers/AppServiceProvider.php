<?php

namespace App\Providers;

use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Policies\CompanyRolePolicy;
use App\Domains\Admin\Policies\FunctionalityPolicy;
use App\Domains\Admin\Policies\ModulePolicy;
use App\Domains\Admin\Policies\PlatformRolePolicy;
use App\Domains\Admin\Services\CompanyModuleActivationChecker;
use App\Domains\Partners\Models\Company;
use App\Domains\Partners\Policies\CompanyPolicy;
use App\Support\Modules\ModuleActivationChecker;
use App\Support\Table\TableBuilder;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CurrentCompany::class);
        $this->app->bind(ModuleActivationChecker::class, CompanyModuleActivationChecker::class);
        $this->app->bind(TableBuilder::class);
    }

    public function boot(): void
    {
        Gate::policy(Module::class, ModulePolicy::class);
        Gate::policy(Functionality::class, FunctionalityPolicy::class);
        Gate::policy(Role::class, PlatformRolePolicy::class);
        Gate::policy(Company::class, CompanyPolicy::class);

        Gate::define('viewAnyCompanyRole', [CompanyRolePolicy::class, 'viewAny']);
        Gate::define('viewCompanyRole', [CompanyRolePolicy::class, 'view']);
        Gate::define('createCompanyRole', [CompanyRolePolicy::class, 'create']);
        Gate::define('updateCompanyRole', [CompanyRolePolicy::class, 'update']);
        Gate::define('deleteCompanyRole', [CompanyRolePolicy::class, 'delete']);

        Route::bind('role', function (string $value): Role {
            return Role::query()->platformManaged()->whereKey($value)->firstOrFail();
        });

        Route::bind('companyRole', function (string $value): Role {
            $companyId = app(CurrentCompany::class)->id();
            abort_if($companyId === null, 404);

            return Role::query()
                ->forCompany($companyId)
                ->whereKey($value)
                ->firstOrFail();
        });

        // Super Admin bypass (R-AUT-03). Con Teams, el rol tiene company_id NULL;
        // setPermissionsTeamId(null) + unset relations evita cache de roles del team tenant.
        Gate::before(function ($user, string $ability) {
            if (! method_exists($user, 'hasRole')) {
                return null;
            }

            $previousTeamId = getPermissionsTeamId();
            setPermissionsTeamId(null);

            try {
                if (method_exists($user, 'unsetRelation')) {
                    $user->unsetRelation('roles');
                    $user->unsetRelation('permissions');
                }

                if ($user->hasRole('Super Admin')) {
                    return true;
                }
            } finally {
                setPermissionsTeamId($previousTeamId);
            }

            return null;
        });
    }
}
