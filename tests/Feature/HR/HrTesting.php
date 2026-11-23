<?php

use App\Domains\Admin\Actions\CreateFunctionality;
use App\Domains\Admin\Actions\CreateModule;
use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Module;
use App\Models\User;
use Spatie\Permission\Models\Permission;

/**
 * Helpers compartidos de los tests Feature de HR (no es un test: sin sufijo Test).
 */
if (! function_exists('ensureHrCatalog')) {
    /** Module `rrhh` Optional Active + Functionalities de oleada 1 (+ permisos Spatie). */
    function ensureHrCatalog(): Module
    {
        $module = Module::query()->where('slug', 'rrhh')->first();

        if ($module !== null) {
            return $module;
        }

        $module = app(CreateModule::class)->handle([
            'name' => 'rrhh',
            'label' => ['es' => 'RRHH', 'en' => 'HR', 'ca' => 'RRHH'],
            'color' => '#be185d',
            'icon' => 'user-cog',
            'level' => ModuleLevel::Optional->value,
            'status' => ModuleStatus::Active->value,
        ]);

        foreach ([
            'contract-types' => 'Tipos de contrato',
            'employees' => 'Empleados',
        ] as $slug => $label) {
            app(CreateFunctionality::class)->handle([
                'module_id' => $module->id,
                'name' => ucfirst(str_replace('-', ' ', $slug)),
                'slug' => $slug,
                'label' => ['es' => $label],
                'status' => FunctionalityStatus::Active->value,
            ]);
        }

        return $module;
    }
}

if (! function_exists('hrPermissions')) {
    /** @return list<string> */
    function hrPermissions(): array
    {
        $names = ['rrhh.access'];

        foreach (['contract-types', 'employees'] as $slug) {
            foreach (['view', 'viewAny', 'create', 'update', 'delete'] as $ability) {
                $names[] = "{$slug}.{$ability}";
            }
        }

        return $names;
    }
}

if (! function_exists('actingHrUser')) {
    /**
     * Usuario miembro de $company con `rrhh` autorizado+encendido y permisos dados
     * (por defecto todos los de HR).
     *
     * @param  list<string>|null  $permissions
     */
    function actingHrUser(Company $company, ?array $permissions = null): User
    {
        ensureHrCatalog();
        enableModuleForCompany($company, 'rrhh');

        $user = User::factory()->create(['status' => true]);
        actingWithCompany($user, $company);

        setPermissionsTeamId($company->id);
        $user->givePermissionTo(
            collect($permissions ?? hrPermissions())
                ->map(fn (string $name) => Permission::findOrCreate($name))
                ->all(),
        );
        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        return $user;
    }
}

if (! function_exists('companyMember')) {
    /** User vinculado a $company (sin permisos HR; para ser sujeto del empleo). */
    function companyMember(Company $company): User
    {
        $member = User::factory()->create(['status' => true]);
        $member->companies()->attach($company->id);

        return $member;
    }
}
