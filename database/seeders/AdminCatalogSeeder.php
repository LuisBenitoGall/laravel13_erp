<?php

namespace Database\Seeders;

use App\Domains\Admin\Actions\AuthorizeModuleForCompany;
use App\Domains\Admin\Actions\CreateFunctionality;
use App\Domains\Admin\Actions\CreateModule;
use App\Domains\Admin\Actions\EnableModuleForCompany;
use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Models\Module;
use App\Domains\Partners\Actions\CreateCompany;
use App\Domains\Partners\Enums\CompanyStatus;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminCatalogSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $previousTeamId = getPermissionsTeamId();
        setPermissionsTeamId(null);

        try {
            Role::findOrCreate('Super Admin');

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

            if (! $user->hasRole('Super Admin')) {
                $user->assignRole('Super Admin');
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
                ['name' => 'Modules', 'slug' => 'modules', 'label' => ['es' => 'Módulos', 'en' => 'Modules']],
                ['name' => 'Functionalities', 'slug' => 'functionalities', 'label' => ['es' => 'Funcionalidades', 'en' => 'Functionalities']],
                ['name' => 'Companies', 'slug' => 'companies', 'label' => ['es' => 'Empresas', 'en' => 'Companies']],
                ['name' => 'Company modules', 'slug' => 'company-modules', 'label' => ['es' => 'Módulos de empresa', 'en' => 'Company modules']],
                ['name' => 'Module authorizations', 'slug' => 'company-module-authorizations', 'label' => ['es' => 'Autorización de módulos', 'en' => 'Module authorizations']],
            ] as $item) {
                if (Functionality::query()->where('slug', $item['slug'])->exists()) {
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

            $company = Company::query()->where('slug', 'demo')->first();

            if ($company === null) {
                $company = app(CreateCompany::class)->handle([
                    'name' => 'Demo Company',
                    'slug' => 'demo',
                    'tradename' => 'Demo',
                    'nif' => 'B00000000',
                    'status' => CompanyStatus::Active->value,
                    'logo' => null,
                ]);
            }

            if (! $user->companies()->where('companies.id', $company->id)->exists()) {
                $user->companies()->attach($company->id);
            }

            app(AuthorizeModuleForCompany::class)->handle($company, $module);
            app(EnableModuleForCompany::class)->handle($company, $module);
        } finally {
            setPermissionsTeamId($previousTeamId);
        }
    }
}
