<?php

namespace App\Providers;

use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Policies\FunctionalityPolicy;
use App\Domains\Admin\Policies\ModulePolicy;
use App\Support\Modules\AlwaysActiveModuleChecker;
use App\Support\Modules\ModuleActivationChecker;
use App\Support\Table\TableBuilder;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CurrentCompany::class);
        $this->app->bind(ModuleActivationChecker::class, AlwaysActiveModuleChecker::class);
        $this->app->bind(TableBuilder::class);
    }

    public function boot(): void
    {
        Gate::policy(Module::class, ModulePolicy::class);
        Gate::policy(Functionality::class, FunctionalityPolicy::class);

        // Super Admin bypass (R-AUT-03). Con Teams, el rol tiene company_id NULL;
        // setPermissionsTeamId(null) asegura hasRole sin team activo.
        Gate::before(function ($user, string $ability) {
            if (! method_exists($user, 'hasRole')) {
                return null;
            }

            $previousTeamId = getPermissionsTeamId();
            setPermissionsTeamId(null);

            try {
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
