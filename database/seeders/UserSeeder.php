<?php

namespace Database\Seeders;

use App\Domains\Admin\Models\Role;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $previousTeamId = getPermissionsTeamId();
        setPermissionsTeamId(null);

        try {
            Role::findOrCreate(Role::SUPER_ADMIN);

            $company = Company::query()->find(1);

            if ($company === null) {
                throw new \RuntimeException('UserSeeder requires company_id=1. Run AdminCatalogSeeder first.');
            }

            $user = User::query()->firstOrCreate(
                ['email' => 'admin@erp.com'],
                [
                    'name' => 'Admin',
                    'surname' => 'Default',
                    'password' => '12345678',
                ],
            );

            $user->forceFill([
                'name' => 'Admin',
                'surname' => 'Default',
                'password' => '12345678',
                'is_admin' => true,
                'status' => true,
            ])->save();

            if (! $user->hasRole(Role::SUPER_ADMIN)) {
                $user->assignRole(Role::SUPER_ADMIN);
            }

            if (! $user->companies()->where('companies.id', $company->id)->exists()) {
                $user->companies()->attach($company->id);
            }
        } finally {
            setPermissionsTeamId($previousTeamId);
        }
    }
}
