<?php

namespace App\Console\Commands;

use App\Domains\Admin\Actions\EnsureCompanyDefaultRoles;
use App\Domains\Admin\Actions\SyncManagerPermissionsForCompany;
use App\Domains\Partners\Models\Company;
use Illuminate\Console\Command;

class MaterializeUniversalRolesCommand extends Command
{
    protected $signature = 'roles:materialize-universals';

    protected $description = 'Idempotent backfill: materialize universal platform role templates into every company';

    public function handle(
        EnsureCompanyDefaultRoles $ensureDefaults,
        SyncManagerPermissionsForCompany $syncManager,
    ): int {
        $count = 0;

        Company::query()->orderBy('id')->each(function (Company $company) use ($ensureDefaults, $syncManager, &$count): void {
            $ensureDefaults->handle($company);
            $syncManager->handle($company);
            $count++;
        });

        $this->info("Materialized universal roles for {$count} companies.");

        return self::SUCCESS;
    }
}
