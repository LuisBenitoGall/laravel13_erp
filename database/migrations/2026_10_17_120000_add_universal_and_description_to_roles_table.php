<?php

use App\Domains\Admin\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $teamKey = config('permission.column_names.team_foreign_key');

        Schema::table($tableNames['roles'], function (Blueprint $table) {
            $table->boolean('universal')->default(false)->after('guard_name');
            $table->text('description')->nullable()->after('universal');
        });

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $previousTeamId = getPermissionsTeamId();
        setPermissionsTeamId(null);

        try {
            foreach (['manager', 'guest'] as $name) {
                $role = Role::findOrCreate($name, 'web');
                $role->forceFill([
                    'universal' => true,
                    'description' => $name === 'manager'
                        ? 'Default company manager (permissions via sync)'
                        : 'Default company guest (empty permissions by default)',
                ])->save();
            }
        } finally {
            setPermissionsTeamId($previousTeamId);
        }

        // Ensure Super Admin stays non-universal.
        DB::table($tableNames['roles'])
            ->whereNull($teamKey)
            ->where('name', 'Super Admin')
            ->update(['universal' => false]);
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');

        Schema::table($tableNames['roles'], function (Blueprint $table) {
            $table->dropColumn(['universal', 'description']);
        });
    }
};
