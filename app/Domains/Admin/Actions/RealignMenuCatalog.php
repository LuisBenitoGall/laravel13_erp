<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\CompanyModule;
use App\Domains\Admin\Models\CompanyModuleAuthorization;
use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Support\MenuCatalogCache;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Idempotente: Modules PDF mínimos, reassign Functionalities, retire admin/workspace,
 * migrar comodines Spatie.
 */
class RealignMenuCatalog
{
    /** @var array<string, list<string>> */
    private const FUNCTIONALITY_MAP = [
        'configuracion' => [
            'modules',
            'functionalities',
            'roles',
            'permissions',
            'countries',
            'currencies',
            'banks',
            'units',
            'vat-types',
            'accounting-account-types',
            'company-module-authorizations',
        ],
        'empresas' => ['companies'],
        'usuarios' => ['company-users', 'company-roles'],
        'cuenta' => ['company-modules', 'profile'],
    ];

    public function __construct(
        private readonly CreateModule $createModule,
        private readonly CreateFunctionality $createFunctionality,
    ) {}

    public function handle(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        DB::transaction(function (): void {
            $modules = $this->ensureModules();
            $this->ensureProfile($modules['cuenta']);
            $this->reassignFunctionalities($modules);
            $this->migrateAccessPermissions();
            $this->retireLegacyModules($modules);
            $this->migrateCompanyLayers($modules);
        });

        MenuCatalogCache::forget();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * @return array<string, Module>
     */
    private function ensureModules(): array
    {
        $defs = [
            'dashboard' => [
                'name' => 'Dashboard',
                'label' => ['es' => 'Dashboard', 'en' => 'Dashboard', 'ca' => 'Dashboard'],
                'color' => '#0f172a',
                'icon' => 'home',
                'level' => ModuleLevel::Basic,
                'explanation' => [
                    'es' => 'Inicio del ERP',
                    'en' => 'ERP home',
                    'ca' => 'Inici de l\'ERP',
                ],
            ],
            'cuenta' => [
                'name' => 'Cuenta',
                'label' => ['es' => 'Cuenta', 'en' => 'Account', 'ca' => 'Compte'],
                'color' => '#7c3aed',
                'icon' => 'user',
                'level' => ModuleLevel::Basic,
                'explanation' => [
                    'es' => 'Mi cuenta y módulos de la empresa',
                    'en' => 'My account and company modules',
                    'ca' => 'El meu compte i mòduls de l\'empresa',
                ],
            ],
            'usuarios' => [
                'name' => 'Usuarios',
                'label' => ['es' => 'Usuarios', 'en' => 'Users', 'ca' => 'Usuaris'],
                'color' => '#0369a1',
                'icon' => 'users',
                'level' => ModuleLevel::Basic,
                'explanation' => [
                    'es' => 'Usuarios y roles de empresa',
                    'en' => 'Company users and roles',
                    'ca' => 'Usuaris i rols d\'empresa',
                ],
            ],
            'empresas' => [
                'name' => 'Empresas',
                'label' => ['es' => 'Empresas', 'en' => 'Companies', 'ca' => 'Empreses'],
                'color' => '#0369a1',
                'icon' => 'briefcase',
                'level' => ModuleLevel::Basic,
                'explanation' => [
                    'es' => 'Mis empresas',
                    'en' => 'My companies',
                    'ca' => 'Les meves empreses',
                ],
            ],
            'configuracion' => [
                'name' => 'Configuracion',
                'label' => ['es' => 'Configuración', 'en' => 'Settings', 'ca' => 'Configuració'],
                'color' => '#0f766e',
                'icon' => 'settings',
                'level' => ModuleLevel::AdminOnly,
                'explanation' => [
                    'es' => 'Catálogo y herramientas de plataforma',
                    'en' => 'Platform catalog and tools',
                    'ca' => 'Catàleg i eines de plataforma',
                ],
            ],
        ];

        $out = [];

        foreach ($defs as $slug => $def) {
            $module = Module::withTrashed()->where('slug', $slug)->first();

            if ($module === null) {
                $module = $this->createModule->handle([
                    'name' => $def['name'],
                    'label' => $def['label'],
                    'color' => $def['color'],
                    'icon' => $def['icon'],
                    'level' => $def['level']->value,
                    'status' => ModuleStatus::Active->value,
                    'explanation' => $def['explanation'],
                ]);
            } else {
                if ($module->trashed()) {
                    $module->restore();
                }

                $module->forceFill([
                    'label' => $def['label'],
                    'color' => $def['color'],
                    'icon' => $def['icon'],
                    'level' => $def['level'],
                    'status' => ModuleStatus::Active,
                    'explanation' => $def['explanation'],
                ])->save();

                Permission::findOrCreate($slug.'.access');
            }

            $out[$slug] = $module->fresh();
        }

        return $out;
    }

    private function ensureProfile(Module $cuenta): void
    {
        $existing = Functionality::withTrashed()->where('slug', 'profile')->first();

        if ($existing !== null) {
            if ($existing->trashed()) {
                $existing->restore();
            }

            $existing->forceFill([
                'module_id' => $cuenta->id,
                'label' => ['es' => 'Mi cuenta', 'en' => 'My account', 'ca' => 'El meu compte'],
                'status' => FunctionalityStatus::Active,
            ])->save();

            return;
        }

        $this->createFunctionality->handle([
            'module_id' => $cuenta->id,
            'name' => 'Profile',
            'slug' => 'profile',
            'label' => ['es' => 'Mi cuenta', 'en' => 'My account', 'ca' => 'El meu compte'],
            'status' => FunctionalityStatus::Active->value,
        ]);
    }

    /**
     * @param  array<string, Module>  $modules
     */
    private function reassignFunctionalities(array $modules): void
    {
        foreach (self::FUNCTIONALITY_MAP as $moduleSlug => $functionalitySlugs) {
            $module = $modules[$moduleSlug];

            foreach ($functionalitySlugs as $slug) {
                $functionality = Functionality::withTrashed()->where('slug', $slug)->first();

                if ($functionality === null) {
                    continue;
                }

                if ($functionality->trashed()) {
                    $functionality->restore();
                }

                $status = $slug === 'functionalities'
                    ? FunctionalityStatus::Inactive
                    : FunctionalityStatus::Active;

                $payload = [
                    'module_id' => $module->id,
                    'status' => $status,
                ];

                if ($slug === 'company-modules') {
                    $payload['label'] = [
                        'es' => 'Mis módulos',
                        'en' => 'My modules',
                        'ca' => 'Els meus mòduls',
                    ];
                }

                $functionality->forceFill($payload)->save();
            }
        }
    }

    private function migrateAccessPermissions(): void
    {
        $this->renamePermission('admin.access', 'configuracion.access');

        $workspace = Permission::query()->where('name', 'workspace.access')->first();

        if ($workspace !== null) {
            foreach (['dashboard', 'cuenta', 'usuarios', 'empresas'] as $slug) {
                $this->copyPermissionGrants($workspace, $this->ensurePermission($slug.'.access'));
            }

            $workspace->delete();
        }

        foreach (['dashboard', 'cuenta', 'usuarios', 'empresas', 'configuracion'] as $slug) {
            $this->ensurePermission($slug.'.access');
        }
    }

    private function renamePermission(string $from, string $to): void
    {
        $source = Permission::query()->where('name', $from)->first();
        $target = $this->ensurePermission($to);

        if ($source === null) {
            return;
        }

        if ($source->id !== $target->id) {
            $this->copyPermissionGrants($source, $target);
            $source->delete();
        }
    }

    private function ensurePermission(string $name): Permission
    {
        Permission::findOrCreate($name);

        return Permission::query()->where('name', $name)->firstOrFail();
    }

    private function copyPermissionGrants(Permission $from, Permission $to): void
    {
        $roleIds = DB::table('role_has_permissions')
            ->where('permission_id', $from->id)
            ->pluck('role_id');

        foreach ($roleIds as $roleId) {
            DB::table('role_has_permissions')->insertOrIgnore([
                'permission_id' => $to->id,
                'role_id' => $roleId,
            ]);
        }

        $modelRows = DB::table('model_has_permissions')
            ->where('permission_id', $from->id)
            ->get();

        foreach ($modelRows as $row) {
            DB::table('model_has_permissions')->insertOrIgnore([
                'permission_id' => $to->id,
                'model_type' => $row->model_type,
                'model_id' => $row->model_id,
            ]);
        }
    }

    /**
     * @param  array<string, Module>  $modules
     */
    private function retireLegacyModules(array $modules): void
    {
        foreach (['admin', 'workspace'] as $slug) {
            $legacy = Module::withTrashed()->where('slug', $slug)->first();

            if ($legacy === null) {
                continue;
            }

            // Orphans should already be reassigned; force leftover to configuracion.
            Functionality::withTrashed()
                ->where('module_id', $legacy->id)
                ->update(['module_id' => $modules['configuracion']->id]);

            if (! $legacy->trashed()) {
                $legacy->delete();
            }

            $access = Permission::query()->where('name', $slug.'.access')->first();
            $access?->delete();
        }
    }

    /**
     * Companies that had workspace authorized/enabled get Basic modules layers.
     *
     * @param  array<string, Module>  $modules
     */
    private function migrateCompanyLayers(array $modules): void
    {
        $basics = [
            $modules['dashboard'],
            $modules['cuenta'],
            $modules['usuarios'],
            $modules['empresas'],
        ];

        $companyIds = CompanyModuleAuthorization::query()
            ->withoutGlobalScope('company')
            ->pluck('company_id')
            ->merge(
                CompanyModule::query()->withoutGlobalScope('company')->pluck('company_id')
            )
            ->unique()
            ->values();

        foreach ($companyIds as $companyId) {
            foreach ($basics as $module) {
                CompanyModuleAuthorization::query()->withoutGlobalScope('company')->firstOrCreate([
                    'company_id' => $companyId,
                    'module_id' => $module->id,
                ]);

                CompanyModule::query()->withoutGlobalScope('company')->firstOrCreate([
                    'company_id' => $companyId,
                    'module_id' => $module->id,
                ]);
            }
        }
    }
}
