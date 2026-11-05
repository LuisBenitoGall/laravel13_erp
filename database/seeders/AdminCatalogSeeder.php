<?php

namespace Database\Seeders;

use App\Domains\Admin\Actions\AssignCompanyRole;
use App\Domains\Admin\Actions\AuthorizeModuleForCompany;
use App\Domains\Admin\Actions\CreateFunctionality;
use App\Domains\Admin\Actions\EnableModuleForCompany;
use App\Domains\Admin\Actions\EnsureCompanyDefaultRoles;
use App\Domains\Admin\Actions\RealignMenuCatalog;
use App\Domains\Admin\Actions\SyncManagerPermissionsForCompany;
use App\Domains\Admin\Enums\FunctionalityStatus;
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

            app(RealignMenuCatalog::class)->handle();

            $bySlug = Module::query()
                ->whereIn('slug', ['dashboard', 'cuenta', 'usuarios', 'empresas', 'configuracion'])
                ->get()
                ->keyBy('slug');

            foreach ([
                ['module' => 'configuracion', 'name' => 'Modules', 'slug' => 'modules', 'label' => ['es' => 'Módulos', 'en' => 'Modules', 'ca' => 'Mòduls']],
                ['module' => 'configuracion', 'name' => 'Functionalities', 'slug' => 'functionalities', 'label' => ['es' => 'Funcionalidades', 'en' => 'Functionalities', 'ca' => 'Funcionalitats'], 'status' => FunctionalityStatus::Inactive],
                ['module' => 'configuracion', 'name' => 'Module authorizations', 'slug' => 'company-module-authorizations', 'label' => ['es' => 'Autorización de módulos', 'en' => 'Module authorizations', 'ca' => 'Autorització de mòduls']],
                ['module' => 'configuracion', 'name' => 'Roles', 'slug' => 'roles', 'label' => ['es' => 'Roles', 'en' => 'Roles', 'ca' => 'Rols']],
                ['module' => 'configuracion', 'name' => 'Permissions', 'slug' => 'permissions', 'label' => ['es' => 'Permisos', 'en' => 'Permissions', 'ca' => 'Permisos']],
                ['module' => 'configuracion', 'name' => 'Countries', 'slug' => 'countries', 'label' => ['es' => 'Países', 'en' => 'Countries', 'ca' => 'Països']],
                ['module' => 'configuracion', 'name' => 'Currencies', 'slug' => 'currencies', 'label' => ['es' => 'Monedas', 'en' => 'Currencies', 'ca' => 'Monedes']],
                ['module' => 'configuracion', 'name' => 'Banks', 'slug' => 'banks', 'label' => ['es' => 'Bancos', 'en' => 'Banks', 'ca' => 'Bancs']],
                ['module' => 'configuracion', 'name' => 'Units', 'slug' => 'units', 'label' => ['es' => 'Unidades', 'en' => 'Units', 'ca' => 'Unitats']],
                ['module' => 'configuracion', 'name' => 'VAT types', 'slug' => 'vat-types', 'label' => ['es' => 'Tipos de IVA', 'en' => 'VAT types', 'ca' => 'Tipus d\'IVA']],
                ['module' => 'configuracion', 'name' => 'Accounting account types', 'slug' => 'accounting-account-types', 'label' => ['es' => 'Grupos contables', 'en' => 'Account groups', 'ca' => 'Grups comptables']],
                ['module' => 'empresas', 'name' => 'Companies', 'slug' => 'companies', 'label' => ['es' => 'Mis empresas', 'en' => 'My companies', 'ca' => 'Les meves empreses']],
                ['module' => 'cuenta', 'name' => 'Company modules', 'slug' => 'company-modules', 'label' => ['es' => 'Mis módulos', 'en' => 'My modules', 'ca' => 'Els meus mòduls']],
                ['module' => 'usuarios', 'name' => 'Company users', 'slug' => 'company-users', 'label' => ['es' => 'Usuarios empresa', 'en' => 'Company users', 'ca' => 'Usuaris empresa']],
                ['module' => 'usuarios', 'name' => 'All users', 'slug' => 'all-users', 'label' => ['es' => 'Todos los usuarios', 'en' => 'All users', 'ca' => 'Tots els usuaris']],
                ['module' => 'usuarios', 'name' => 'Company roles', 'slug' => 'company-roles', 'label' => ['es' => 'Roles de empresa', 'en' => 'Company roles', 'ca' => 'Rols d\'empresa']],
            ] as $item) {
                $module = $bySlug[$item['module']];
                $status = $item['status'] ?? FunctionalityStatus::Active;
                $existing = Functionality::query()->where('slug', $item['slug'])->first();

                if ($existing !== null) {
                    $existing->forceFill([
                        'module_id' => $module->id,
                        'label' => $item['label'],
                        'status' => $status,
                    ])->save();

                    continue;
                }

                app(CreateFunctionality::class)->handle([
                    'module_id' => $module->id,
                    'name' => $item['name'],
                    'slug' => $item['slug'],
                    'label' => $item['label'],
                    'status' => $status->value,
                ]);
            }

            // Re-run realign to reassign any leftovers + ensure profile.
            app(RealignMenuCatalog::class)->handle();

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
            }

            $basicModules = Module::query()
                ->whereIn('slug', ['dashboard', 'cuenta', 'usuarios', 'empresas'])
                ->get();

            Company::query()->each(function (Company $existing) use ($user, $basicModules): void {
                app(EnsureCompanyDefaultRoles::class)->handle($existing);

                $managerCandidate = $existing->users()->first() ?? $user;

                if (! $managerCandidate->companies()->where('companies.id', $existing->id)->exists()) {
                    $managerCandidate->companies()->attach($existing->id);
                }

                app(AssignCompanyRole::class)->handle($managerCandidate, $existing, 'manager');

                foreach ($basicModules as $module) {
                    app(AuthorizeModuleForCompany::class)->handle($existing, $module);
                    app(EnableModuleForCompany::class)->handle($existing, $module);
                }

                app(SyncManagerPermissionsForCompany::class)->handle($existing);
            });
        } finally {
            setPermissionsTeamId($previousTeamId);
        }
    }
}
