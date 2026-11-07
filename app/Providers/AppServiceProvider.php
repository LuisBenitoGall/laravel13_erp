<?php

namespace App\Providers;

use App\Domains\Admin\Models\AccountingAccountType;
use App\Domains\Admin\Models\Country;
use App\Domains\Admin\Models\Currency;
use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Province;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Models\Town;
use App\Domains\Admin\Models\Unit;
use App\Domains\Admin\Models\VatType;
use App\Domains\Admin\Policies\AccountingAccountTypePolicy;
use App\Domains\Admin\Policies\AllUserPolicy;
use App\Domains\Admin\Policies\CompanyRolePolicy;
use App\Domains\Admin\Policies\CompanyUserPolicy;
use App\Domains\Admin\Policies\CountryPolicy;
use App\Domains\Admin\Policies\CurrencyPolicy;
use App\Domains\Admin\Policies\FunctionalityPolicy;
use App\Domains\Admin\Policies\ModulePolicy;
use App\Domains\Admin\Policies\PermissionPolicy;
use App\Domains\Admin\Policies\PlatformRolePolicy;
use App\Domains\Admin\Policies\ProvincePolicy;
use App\Domains\Admin\Policies\TownPolicy;
use App\Domains\Admin\Policies\UnitPolicy;
use App\Domains\Admin\Policies\VatTypePolicy;
use App\Domains\Admin\Services\CompanyModuleActivationChecker;
use App\Domains\Banks\Models\Bank;
use App\Domains\Banks\Policies\BankPolicy;
use App\Domains\Partners\Models\Company;
use App\Domains\Partners\Policies\CompanyPolicy;
use App\Support\Modules\ModuleActivationChecker;
use App\Support\Table\TableBuilder;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;

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
        // Package default is resources/js/pages. This app resolves pages from
        // resources/js/Pages (resources/js/app.tsx). Linux does not equate them,
        // so assertInertia cannot see the component files in CI.
        config([
            'inertia.pages.paths' => [
                resource_path('js/Pages'),
            ],
        ]);

        Gate::policy(Module::class, ModulePolicy::class);
        Gate::policy(Country::class, CountryPolicy::class);
        Gate::policy(Currency::class, CurrencyPolicy::class);
        Gate::policy(Bank::class, BankPolicy::class);
        Gate::policy(Unit::class, UnitPolicy::class);
        Gate::policy(VatType::class, VatTypePolicy::class);
        Gate::policy(AccountingAccountType::class, AccountingAccountTypePolicy::class);
        Gate::policy(Province::class, ProvincePolicy::class);
        Gate::policy(Town::class, TownPolicy::class);
        Gate::policy(Functionality::class, FunctionalityPolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
        Gate::policy(Role::class, PlatformRolePolicy::class);
        Gate::policy(Company::class, CompanyPolicy::class);

        Gate::define('viewAnyCompanyRole', [CompanyRolePolicy::class, 'viewAny']);
        Gate::define('viewCompanyRole', [CompanyRolePolicy::class, 'view']);
        Gate::define('createCompanyRole', [CompanyRolePolicy::class, 'create']);
        Gate::define('updateCompanyRole', [CompanyRolePolicy::class, 'update']);
        Gate::define('deleteCompanyRole', [CompanyRolePolicy::class, 'delete']);

        Gate::define('viewAnyAllUser', [AllUserPolicy::class, 'viewAny']);
        Gate::define('viewAnyCompanyUser', [CompanyUserPolicy::class, 'viewAny']);
        Gate::define('viewCompanyUser', [CompanyUserPolicy::class, 'view']);
        Gate::define('createCompanyUser', [CompanyUserPolicy::class, 'create']);
        Gate::define('updateCompanyUser', [CompanyUserPolicy::class, 'update']);
        Gate::define('deleteCompanyUser', [CompanyUserPolicy::class, 'delete']);

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
        // Tras restaurar el team hay que volver a vaciar relations: si no, el hasRole
        // bajo team null deja roles=[] cacheados y Spatie niega permisos vía rol (manager).
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

                if (method_exists($user, 'unsetRelation')) {
                    $user->unsetRelation('roles');
                    $user->unsetRelation('permissions');
                }
            }

            return null;
        });
    }
}
