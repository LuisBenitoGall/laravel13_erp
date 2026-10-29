<?php

namespace Database\Seeders;

use App\Domains\Admin\Actions\AssignCompanyRole;
use App\Domains\Admin\Actions\AuthorizeModuleForCompany;
use App\Domains\Admin\Actions\CreateFunctionality;
use App\Domains\Admin\Actions\CreateModule;
use App\Domains\Admin\Actions\EnableModuleForCompany;
use App\Domains\Admin\Actions\EnsureCompanyDefaultRoles;
use App\Domains\Admin\Actions\SyncManagerPermissionsForCompany;
use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
use App\Domains\Partners\Actions\CreateCompany;
use App\Domains\Partners\Enums\CompanyStatus;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class AdminCatalogSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $previousTeamId = getPermissionsTeamId();
        setPermissionsTeamId(null);

        try {
            Role::findOrCreate(Role::SUPER_ADMIN);

            foreach ([
                [Role::MANAGER, 'Default company manager (permissions via sync)'],
                [Role::GUEST, 'Default company guest (empty permissions by default)'],
            ] as [$templateName, $description]) {
                $template = Role::findOrCreate($templateName);
                $template->forceFill([
                    'universal' => true,
                    'description' => $description,
                ])->save();
            }

            $email = (string) config('superadmin.email');
            $password = (string) config('superadmin.password');

            if ($email === '' || $password === '') {
                throw new \RuntimeException('SUPERADMIN_EMAIL/SUPERADMIN_PASSWORD must be set (config/superadmin.php).');
            }

            $user = User::query()->firstOrCreate(
                ['email' => $email],
                [
                    'name' => (string) config('superadmin.name'),
                    'password' => Hash::make($password),
                ],
            );

            if (! $user->hasRole(Role::SUPER_ADMIN)) {
                $user->assignRole(Role::SUPER_ADMIN);
            }

            $module = Module::query()->where('slug', 'admin')->first();

            if ($module === null) {
                $module = app(CreateModule::class)->handle([
                    'name' => 'Admin',
                    'slug' => 'admin',
                    'label' => ['es' => 'Administración', 'en' => 'Administration'],
                    'color' => '#0f766e',
                    'icon' => 'settings',
                    'level' => ModuleLevel::AdminOnly->value,
                    'status' => ModuleStatus::Active->value,
                    'explanation' => [
                        'es' => 'Catálogo de módulos y funcionalidades',
                        'en' => 'Module and functionality catalog',
                    ],
                ]);
            }

            foreach ([
                ['name' => 'Modules', 'slug' => 'modules', 'label' => ['es' => 'Módulos', 'en' => 'Modules', 'ca' => 'Mòduls']],
                ['name' => 'Functionalities', 'slug' => 'functionalities', 'label' => ['es' => 'Funcionalidades', 'en' => 'Functionalities', 'ca' => 'Funcionalitats']],
                ['name' => 'Companies', 'slug' => 'companies', 'label' => ['es' => 'Mis empresas', 'en' => 'My companies', 'ca' => 'Les meves empreses']],
                ['name' => 'Company modules', 'slug' => 'company-modules', 'label' => ['es' => 'Módulos de empresa', 'en' => 'Company modules', 'ca' => 'Mòduls d\'empresa']],
                ['name' => 'Module authorizations', 'slug' => 'company-module-authorizations', 'label' => ['es' => 'Autorización de módulos', 'en' => 'Module authorizations', 'ca' => 'Autorització de mòduls']],
                ['name' => 'Roles', 'slug' => 'roles', 'label' => ['es' => 'Roles', 'en' => 'Roles', 'ca' => 'Rols']],
                ['name' => 'Permissions', 'slug' => 'permissions', 'label' => ['es' => 'Permisos', 'en' => 'Permissions', 'ca' => 'Permisos']],
            ] as $item) {
                $existing = Functionality::query()->where('slug', $item['slug'])->first();

                if ($existing !== null) {
                    $existing->forceFill([
                        'label' => $item['label'],
                        'status' => FunctionalityStatus::Active,
                    ])->save();

                    continue;
                }

                app(CreateFunctionality::class)->handle([
                    'module_id' => $module->id,
                    'name' => $item['name'],
                    'slug' => $item['slug'],
                    'label' => $item['label'],
                    'status' => FunctionalityStatus::Active->value,
                ]);
            }

            $workspace = Module::query()->where('slug', 'workspace')->first();

            if ($workspace === null) {
                $workspace = app(CreateModule::class)->handle([
                    'name' => 'Workspace',
                    'slug' => 'workspace',
                    'label' => [
                        'es' => 'Espacio de trabajo',
                        'en' => 'Workspace',
                        'ca' => 'Espai de treball',
                    ],
                    'color' => '#0369a1',
                    'icon' => 'briefcase',
                    'level' => ModuleLevel::Basic->value,
                    'status' => ModuleStatus::Active->value,
                    'explanation' => [
                        'es' => 'Ajustes y roles de la empresa',
                        'en' => 'Company settings and roles',
                        'ca' => 'Ajustos i rols de l\'empresa',
                    ],
                ]);
            }

            foreach ([
                [
                    'name' => 'Company roles',
                    'slug' => 'company-roles',
                    'label' => [
                        'es' => 'Roles de empresa',
                        'en' => 'Company roles',
                        'ca' => 'Rols d\'empresa',
                    ],
                ],
                [
                    'name' => 'Company users',
                    'slug' => 'company-users',
                    'label' => [
                        'es' => 'Usuarios empresa',
                        'en' => 'Company users',
                        'ca' => 'Usuaris empresa',
                    ],
                ],
            ] as $item) {
                $existing = Functionality::query()->where('slug', $item['slug'])->first();

                if ($existing !== null) {
                    $existing->forceFill([
                        'label' => $item['label'],
                        'status' => FunctionalityStatus::Active,
                    ])->save();

                    continue;
                }

                app(CreateFunctionality::class)->handle([
                    'module_id' => $workspace->id,
                    'name' => $item['name'],
                    'slug' => $item['slug'],
                    'label' => $item['label'],
                    'status' => FunctionalityStatus::Active->value,
                ]);
            }

            $company = Company::query()->where('slug', 'acme')->first()
                ?? Company::query()->where('slug', 'demo')->first();

            if ($company === null) {
                $company = app(CreateCompany::class)->handle([
                    'name' => 'ACME',
                    'slug' => 'acme',
                    'tradename' => 'ACME',
                    'nif' => '00000000A',
                    'status' => CompanyStatus::Active->value,
                    'logo' => null,
                ], $user);
            } else {
                $company->forceFill([
                    'name' => 'ACME',
                    'slug' => 'acme',
                    'tradename' => 'ACME',
                    'nif' => '00000000A',
                    'status' => CompanyStatus::Active,
                ])->save();

                app(EnsureCompanyDefaultRoles::class)->handle($company);

                if (! $user->companies()->where('companies.id', $company->id)->exists()) {
                    $user->companies()->attach($company->id);
                }

                app(AssignCompanyRole::class)->handle($user, $company, 'manager');
                app(SyncManagerPermissionsForCompany::class)->handle($company);
            }

            // AdminOnly: no obligatorio encender `admin` (bypass SA + menú Configuración).
            app(AuthorizeModuleForCompany::class)->handle($company, $workspace);
            app(EnableModuleForCompany::class)->handle($company, $workspace);

            // Backfill: asegurar roles + manager + sync en todas las companies.
            Company::query()->each(function (Company $existing) use ($user, $workspace): void {
                app(EnsureCompanyDefaultRoles::class)->handle($existing);

                $managerCandidate = $existing->users()->first() ?? $user;

                if (! $managerCandidate->companies()->where('companies.id', $existing->id)->exists()) {
                    $managerCandidate->companies()->attach($existing->id);
                }

                app(AssignCompanyRole::class)->handle($managerCandidate, $existing, 'manager');
                app(AuthorizeModuleForCompany::class)->handle($existing, $workspace);
                app(EnableModuleForCompany::class)->handle($existing, $workspace);
                app(SyncManagerPermissionsForCompany::class)->handle($existing);
            });
        } finally {
            setPermissionsTeamId($previousTeamId);
        }
    }
}
