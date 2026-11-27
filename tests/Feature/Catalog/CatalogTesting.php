<?php

use App\Domains\Admin\Actions\CreateFunctionality;
use App\Domains\Admin\Actions\CreateModule;
use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Models\Module;
use App\Models\User;
use Spatie\Permission\Models\Permission;

/**
 * Helpers compartidos de los tests Feature de Catalog (no es un test).
 */
if (! function_exists('ensureCatalogueCatalog')) {
    /** Module `products` Optional Active + Functionality `catalogue` (+ permisos Spatie). */
    function ensureCatalogueCatalog(): Module
    {
        $module = Module::query()->where('slug', 'products')->first();

        if ($module === null) {
            $module = app(CreateModule::class)->handle([
                'name' => 'Products',
                'label' => ['es' => 'Productos', 'en' => 'Products', 'ca' => 'Productes'],
                'color' => '#c2410c',
                'icon' => 'package',
                'level' => ModuleLevel::Optional->value,
                'status' => ModuleStatus::Active->value,
            ]);
        }

        if (! Functionality::withTrashed()->where('slug', 'catalogue')->exists()) {
            app(CreateFunctionality::class)->handle([
                'module_id' => $module->id,
                'name' => 'Catalogue',
                'slug' => 'catalogue',
                'label' => ['es' => 'Catálogo', 'en' => 'Catalogue', 'ca' => 'Catàleg'],
                'status' => FunctionalityStatus::Active->value,
            ]);
        }

        return $module;
    }
}

if (! function_exists('cataloguePermissions')) {
    /** @return list<string> */
    function cataloguePermissions(): array
    {
        $names = ['products.access'];

        foreach (['view', 'viewAny', 'create', 'update', 'delete'] as $ability) {
            $names[] = "catalogue.{$ability}";
        }

        return $names;
    }
}

if (! function_exists('actingCatalogueUser')) {
    /**
     * Usuario miembro de $company con `products` autorizado/encendido y permisos dados.
     *
     * @param  list<string>|null  $permissions
     */
    function actingCatalogueUser(Company $company, ?array $permissions = null): User
    {
        ensureCatalogueCatalog();
        enableModuleForCompany($company, 'products');

        $user = User::factory()->create(['status' => true]);
        actingWithCompany($user, $company);

        setPermissionsTeamId($company->id);
        $user->givePermissionTo(
            collect($permissions ?? cataloguePermissions())
                ->map(fn (string $name) => Permission::findOrCreate($name))
                ->all(),
        );
        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        return $user;
    }
}
