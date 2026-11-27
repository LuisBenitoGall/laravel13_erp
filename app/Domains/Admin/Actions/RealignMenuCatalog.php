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
    /**
     * Legacy Spanish / interim slugs → English Module slug (name+slug EN = route segment).
     *
     * @var array<string, string>
     */
    private const MODULE_SLUG_RENAMES = [
        'cuenta' => 'account',
        'usuarios' => 'users',
        'empresas' => 'companies',
        'configuracion' => 'settings',
        'catalogue' => 'products',
        'productos' => 'products',
        'albaranes' => 'deliveries',
    ];

    /** @var array<string, list<string>> */
    private const FUNCTIONALITY_MAP = [
        'settings' => [
            'modules',
            'functionalities',
            'roles',
            'permissions',
            'countries',
            'currencies',
            'exchange-rates',
            'banks',
            'units',
            'sectors',
            'payment-methods',
            'contents',
            'gallery',
            'accounts',
            'vat-types',
            'accounting-account-types',
            'company-module-authorizations',
        ],
        'companies' => ['companies', 'delegations', 'company-directory', 'workplaces', 'company-settings', 'company-sectors'],
        'users' => ['company-users', 'all-users', 'company-roles'],
        'account' => ['company-modules', 'profile'],
        'rrhh' => ['contract-types', 'employees', 'calendars', 'day-types'],
        'products' => ['catalogue'],
    ];

    public function __construct(
        private readonly CreateModule $createModule,
        private readonly CreateFunctionality $createFunctionality,
    ) {}

    public function handle(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        DB::transaction(function (): void {
            $this->renameLegacyModuleSlugs();
            $modules = $this->ensureModules();
            $this->ensureProfile($modules['account']);
            $this->ensureAllUsers($modules['users']);
            $this->ensureWorkplaces($modules['companies']);
            $this->ensureDelegations($modules['companies']);
            $this->ensureCompanySettings($modules['companies']);
            $this->ensureExchangeRates($modules['settings']);
            $this->ensurePaymentMethods($modules['settings']);
            $this->ensureContents($modules['settings']);
            $this->ensureGallery($modules['settings']);
            $this->ensureAccounts($modules['settings']);
            $this->ensureHrFunctionalities($modules['rrhh']);
            $this->ensureProductsFunctionalities($modules['products']);
            $this->reassignFunctionalities($modules);
            $this->applyModuleSort($modules);
            $this->applySettingsFunctionalitySort();
            $this->applyHrFunctionalitySort();
            $this->applyProductsFunctionalitySort();
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
            'account' => [
                'name' => 'Account',
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
            'users' => [
                'name' => 'Users',
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
            'companies' => [
                'name' => 'Companies',
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
            'rrhh' => [
                'name' => 'RRHH',
                'label' => ['es' => 'RRHH', 'en' => 'HR', 'ca' => 'RRHH'],
                'color' => '#be185d',
                'icon' => 'user-cog',
                'level' => ModuleLevel::Optional,
                'explanation' => [
                    'es' => 'Tipos de contrato y empleados',
                    'en' => 'Contract types and employees',
                    'ca' => 'Tipus de contracte i empleats',
                ],
            ],
            'products' => [
                'name' => 'Products',
                'label' => ['es' => 'Productos', 'en' => 'Products', 'ca' => 'Productes'],
                'color' => '#c2410c',
                'icon' => 'package',
                'level' => ModuleLevel::Optional,
                'explanation' => [
                    'es' => 'Productos y servicios de la empresa',
                    'en' => 'Company products and services',
                    'ca' => 'Productes i serveis de l\'empresa',
                ],
            ],
            'settings' => [
                'name' => 'Settings',
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
                    'name' => $def['name'],
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

    /** Rename Spanish/interim Module slugs to English before ensureModules. */
    private function renameLegacyModuleSlugs(): void
    {
        foreach (self::MODULE_SLUG_RENAMES as $from => $to) {
            $legacy = Module::withTrashed()->where('slug', $from)->first();

            if ($legacy === null) {
                continue;
            }

            $target = Module::withTrashed()->where('slug', $to)->first();

            if ($target !== null && $target->id !== $legacy->id) {
                Functionality::withTrashed()
                    ->where('module_id', $legacy->id)
                    ->update(['module_id' => $target->id]);

                CompanyModuleAuthorization::query()->withoutGlobalScope('company')
                    ->where('module_id', $legacy->id)
                    ->update(['module_id' => $target->id]);

                CompanyModule::query()->withoutGlobalScope('company')
                    ->where('module_id', $legacy->id)
                    ->update(['module_id' => $target->id]);

                if (! $legacy->trashed()) {
                    $legacy->delete();
                }

                $this->renamePermission($from.'.access', $to.'.access');

                continue;
            }

            $legacy->forceFill([
                'slug' => $to,
                'name' => match ($to) {
                    'account' => 'Account',
                    'users' => 'Users',
                    'companies' => 'Companies',
                    'settings' => 'Settings',
                    'products' => 'Products',
                    'deliveries' => 'Deliveries',
                    default => $legacy->name,
                },
            ])->save();

            $this->renamePermission($from.'.access', $to.'.access');
        }
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

    private function ensureAllUsers(Module $usuarios): void
    {
        $existing = Functionality::withTrashed()->where('slug', 'all-users')->first();

        if ($existing !== null) {
            if ($existing->trashed()) {
                $existing->restore();
            }

            $existing->forceFill([
                'module_id' => $usuarios->id,
                'label' => [
                    'es' => 'Todos los usuarios',
                    'en' => 'All users',
                    'ca' => 'Tots els usuaris',
                ],
                'status' => FunctionalityStatus::Active,
            ])->save();

            return;
        }

        $this->createFunctionality->handle([
            'module_id' => $usuarios->id,
            'name' => 'All users',
            'slug' => 'all-users',
            'label' => [
                'es' => 'Todos los usuarios',
                'en' => 'All users',
                'ca' => 'Tots els usuaris',
            ],
            'status' => FunctionalityStatus::Active->value,
        ]);
    }

    private function ensureWorkplaces(Module $empresas): void
    {
        $existing = Functionality::withTrashed()->where('slug', 'workplaces')->first();

        if ($existing !== null) {
            if ($existing->trashed()) {
                $existing->restore();
            }

            $existing->forceFill([
                'module_id' => $empresas->id,
                'label' => [
                    'es' => 'Centros de trabajo',
                    'en' => 'Workplaces',
                    'ca' => 'Centres de treball',
                ],
                'status' => FunctionalityStatus::Active,
            ])->save();

            return;
        }

        $this->createFunctionality->handle([
            'module_id' => $empresas->id,
            'name' => 'Workplaces',
            'slug' => 'workplaces',
            'label' => [
                'es' => 'Centros de trabajo',
                'en' => 'Workplaces',
                'ca' => 'Centres de treball',
            ],
            'status' => FunctionalityStatus::Active->value,
        ]);
    }

    private function ensureDelegations(Module $empresas): void
    {
        $existing = Functionality::withTrashed()->where('slug', 'delegations')->first();

        if ($existing !== null) {
            if ($existing->trashed()) {
                $existing->restore();
            }

            $existing->forceFill([
                'module_id' => $empresas->id,
                'label' => [
                    'es' => 'Delegaciones',
                    'en' => 'Delegations',
                    'ca' => 'Delegacions',
                ],
                'status' => FunctionalityStatus::Active,
            ])->save();

            return;
        }

        $this->createFunctionality->handle([
            'module_id' => $empresas->id,
            'name' => 'Delegations',
            'slug' => 'delegations',
            'label' => [
                'es' => 'Delegaciones',
                'en' => 'Delegations',
                'ca' => 'Delegacions',
            ],
            'status' => FunctionalityStatus::Active->value,
        ]);
    }

    private function ensureCompanySettings(Module $empresas): void
    {
        $existing = Functionality::withTrashed()->where('slug', 'company-settings')->first();

        if ($existing !== null) {
            if ($existing->trashed()) {
                $existing->restore();
            }

            $existing->forceFill([
                'module_id' => $empresas->id,
                'label' => [
                    'es' => 'Configuración de la empresa',
                    'en' => 'Company settings',
                    'ca' => 'Configuració de l\'empresa',
                ],
                'status' => FunctionalityStatus::Active,
            ])->save();

            return;
        }

        $this->createFunctionality->handle([
            'module_id' => $empresas->id,
            'name' => 'Company settings',
            'slug' => 'company-settings',
            'label' => [
                'es' => 'Configuración de la empresa',
                'en' => 'Company settings',
                'ca' => 'Configuració de l\'empresa',
            ],
            'status' => FunctionalityStatus::Active->value,
        ]);
    }

    private function ensureExchangeRates(Module $configuracion): void
    {
        $existing = Functionality::withTrashed()->where('slug', 'exchange-rates')->first();

        if ($existing !== null) {
            if ($existing->trashed()) {
                $existing->restore();
            }

            $existing->forceFill([
                'module_id' => $configuracion->id,
                'label' => [
                    'es' => 'Conversión monedas',
                    'en' => 'Currency conversion',
                    'ca' => 'Conversió de monedes',
                ],
                'status' => FunctionalityStatus::Active,
            ])->save();

            return;
        }

        $this->createFunctionality->handle([
            'module_id' => $configuracion->id,
            'name' => 'Exchange rates',
            'slug' => 'exchange-rates',
            'label' => [
                'es' => 'Conversión monedas',
                'en' => 'Currency conversion',
                'ca' => 'Conversió de monedes',
            ],
            'status' => FunctionalityStatus::Active->value,
        ]);
    }

    private function ensurePaymentMethods(Module $configuracion): void
    {
        $existing = Functionality::withTrashed()->where('slug', 'payment-methods')->first();

        if ($existing !== null) {
            if ($existing->trashed()) {
                $existing->restore();
            }

            $existing->forceFill([
                'module_id' => $configuracion->id,
                'label' => [
                    'es' => 'Métodos de pago',
                    'en' => 'Payment methods',
                    'ca' => 'Mètodes de pagament',
                ],
                'status' => FunctionalityStatus::Active,
            ])->save();

            return;
        }

        $this->createFunctionality->handle([
            'module_id' => $configuracion->id,
            'name' => 'Payment methods',
            'slug' => 'payment-methods',
            'label' => [
                'es' => 'Métodos de pago',
                'en' => 'Payment methods',
                'ca' => 'Mètodes de pagament',
            ],
            'status' => FunctionalityStatus::Active->value,
        ]);
    }

    private function ensureContents(Module $configuracion): void
    {
        $existing = Functionality::withTrashed()->where('slug', 'contents')->first();

        if ($existing !== null) {
            if ($existing->trashed()) {
                $existing->restore();
            }

            $existing->forceFill([
                'module_id' => $configuracion->id,
                'label' => [
                    'es' => 'Contenidos',
                    'en' => 'Contents',
                    'ca' => 'Continguts',
                ],
                'status' => FunctionalityStatus::Active,
            ])->save();

            return;
        }

        $this->createFunctionality->handle([
            'module_id' => $configuracion->id,
            'name' => 'Contents',
            'slug' => 'contents',
            'label' => [
                'es' => 'Contenidos',
                'en' => 'Contents',
                'ca' => 'Continguts',
            ],
            'status' => FunctionalityStatus::Active->value,
        ]);
    }

    private function ensureGallery(Module $configuracion): void
    {
        $existing = Functionality::withTrashed()->where('slug', 'gallery')->first();

        if ($existing !== null) {
            if ($existing->trashed()) {
                $existing->restore();
            }

            $existing->forceFill([
                'module_id' => $configuracion->id,
                'label' => [
                    'es' => 'Galería',
                    'en' => 'Gallery',
                    'ca' => 'Galeria',
                ],
                'status' => FunctionalityStatus::Active,
            ])->save();

            return;
        }

        $this->createFunctionality->handle([
            'module_id' => $configuracion->id,
            'name' => 'Gallery',
            'slug' => 'gallery',
            'label' => [
                'es' => 'Galería',
                'en' => 'Gallery',
                'ca' => 'Galeria',
            ],
            'status' => FunctionalityStatus::Active->value,
        ]);
    }

    private function ensureAccounts(Module $configuracion): void
    {
        $existing = Functionality::withTrashed()->where('slug', 'accounts')->first();

        if ($existing !== null) {
            if ($existing->trashed()) {
                $existing->restore();
            }

            $existing->forceFill([
                'module_id' => $configuracion->id,
                'label' => [
                    'es' => 'Cuentas',
                    'en' => 'Accounts',
                    'ca' => 'Comptes',
                ],
                'status' => FunctionalityStatus::Active,
            ])->save();

            return;
        }

        $this->createFunctionality->handle([
            'module_id' => $configuracion->id,
            'name' => 'Accounts',
            'slug' => 'accounts',
            'label' => [
                'es' => 'Cuentas',
                'en' => 'Accounts',
                'ca' => 'Comptes',
            ],
            'status' => FunctionalityStatus::Active->value,
        ]);
    }

    /** Functionalities de oleada 1 de RRHH (Module Optional `rrhh`). */
    private function ensureHrFunctionalities(Module $rrhh): void
    {
        $definitions = [
            'contract-types' => [
                'name' => 'Contract types',
                'label' => ['es' => 'Tipos de contrato', 'en' => 'Contract types', 'ca' => 'Tipus de contracte'],
            ],
            'employees' => [
                'name' => 'Employees',
                'label' => ['es' => 'Empleados', 'en' => 'Employees', 'ca' => 'Empleats'],
            ],
            'calendars' => [
                'name' => 'Calendars',
                'label' => ['es' => 'Calendarios', 'en' => 'Calendars', 'ca' => 'Calendaris'],
            ],
            'day-types' => [
                'name' => 'Day types',
                'label' => ['es' => 'Tipos de jornada', 'en' => 'Day types', 'ca' => 'Tipus de jornada'],
            ],
        ];

        foreach ($definitions as $slug => $definition) {
            $existing = Functionality::withTrashed()->where('slug', $slug)->first();

            if ($existing !== null) {
                if ($existing->trashed()) {
                    $existing->restore();
                }

                $existing->forceFill([
                    'module_id' => $rrhh->id,
                    'label' => $definition['label'],
                    'status' => FunctionalityStatus::Active,
                ])->save();

                continue;
            }

            $this->createFunctionality->handle([
                'module_id' => $rrhh->id,
                'name' => $definition['name'],
                'slug' => $slug,
                'label' => $definition['label'],
                'status' => FunctionalityStatus::Active->value,
            ]);
        }
    }

    private function applySettingsFunctionalitySort(): void
    {
        foreach (self::FUNCTIONALITY_MAP['settings'] as $index => $slug) {
            Functionality::withTrashed()
                ->where('slug', $slug)
                ->update(['sort' => ($index + 1) * 10]);
        }
    }

    private function applyHrFunctionalitySort(): void
    {
        foreach (self::FUNCTIONALITY_MAP['rrhh'] as $index => $slug) {
            Functionality::withTrashed()
                ->where('slug', $slug)
                ->update(['sort' => ($index + 1) * 10]);
        }
    }

    private function applyProductsFunctionalitySort(): void
    {
        foreach (self::FUNCTIONALITY_MAP['products'] as $index => $slug) {
            Functionality::withTrashed()
                ->where('slug', $slug)
                ->update(['sort' => ($index + 1) * 10]);
        }
    }

    /**
     * @param  array<string, Module>  $modules
     */
    private function applyModuleSort(array $modules): void
    {
        $order = [
            'dashboard',
            'account',
            'users',
            'companies',
            'products',
            'rrhh',
            'deliveries',
            'settings',
        ];

        foreach ($order as $index => $slug) {
            if (! isset($modules[$slug])) {
                $module = Module::withTrashed()->where('slug', $slug)->first();
                if ($module === null) {
                    continue;
                }
            } else {
                $module = $modules[$slug];
            }

            $module->forceFill(['sort' => ($index + 1) * 10])->save();
        }
    }

    /** Functionality `catalogue` bajo Module Optional `products` (UI: Productos → Catálogo). */
    private function ensureProductsFunctionalities(Module $products): void
    {
        foreach (['productos', 'products'] as $legacySlug) {
            $legacy = Functionality::withTrashed()->where('slug', $legacySlug)->first();

            if ($legacy === null) {
                continue;
            }

            $target = Functionality::withTrashed()->where('slug', 'catalogue')->first();

            if ($target !== null && $target->id !== $legacy->id) {
                if (! $legacy->trashed()) {
                    $legacy->delete();
                }
            } else {
                $legacy->forceFill([
                    'slug' => 'catalogue',
                    'name' => 'Catalogue',
                    'module_id' => $products->id,
                    'label' => ['es' => 'Catálogo', 'en' => 'Catalogue', 'ca' => 'Catàleg'],
                    'status' => FunctionalityStatus::Active,
                ])->save();
            }

            foreach (['view', 'viewAny', 'create', 'update', 'delete'] as $ability) {
                $this->renamePermission($legacySlug.'.'.$ability, 'catalogue.'.$ability);
            }
        }

        $calendarios = Functionality::withTrashed()->where('slug', 'calendarios')->first();
        if ($calendarios !== null && ! $calendarios->trashed()) {
            $calendarios->delete();
        }

        $existing = Functionality::withTrashed()->where('slug', 'catalogue')->first();

        if ($existing !== null) {
            if ($existing->trashed()) {
                $existing->restore();
            }

            $existing->forceFill([
                'module_id' => $products->id,
                'name' => 'Catalogue',
                'label' => ['es' => 'Catálogo', 'en' => 'Catalogue', 'ca' => 'Catàleg'],
                'status' => FunctionalityStatus::Active,
            ])->save();

            return;
        }

        $this->createFunctionality->handle([
            'module_id' => $products->id,
            'name' => 'Catalogue',
            'slug' => 'catalogue',
            'label' => ['es' => 'Catálogo', 'en' => 'Catalogue', 'ca' => 'Catàleg'],
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
        $this->renamePermission('admin.access', 'settings.access');
        $this->renamePermission('configuracion.access', 'settings.access');
        $this->renamePermission('cuenta.access', 'account.access');
        $this->renamePermission('usuarios.access', 'users.access');
        $this->renamePermission('empresas.access', 'companies.access');
        $this->renamePermission('catalogue.access', 'products.access');
        $this->renamePermission('productos.access', 'products.access');

        $workspace = Permission::query()->where('name', 'workspace.access')->first();

        if ($workspace !== null) {
            foreach (['dashboard', 'account', 'users', 'companies'] as $slug) {
                $this->copyPermissionGrants($workspace, $this->ensurePermission($slug.'.access'));
            }

            $workspace->delete();
        }

        foreach (['dashboard', 'account', 'users', 'companies', 'products', 'settings', 'rrhh'] as $slug) {
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

            // Orphans should already be reassigned; force leftover to settings.
            Functionality::withTrashed()
                ->where('module_id', $legacy->id)
                ->update(['module_id' => $modules['settings']->id]);

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
            $modules['account'],
            $modules['users'],
            $modules['companies'],
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
