<?php

namespace App\Console\Commands;

use App\Domains\HR\Actions\SyncEmployeeStatuses;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('hr:sync-employee-statuses')]
#[Description('Activa periodos scheduled elegibles y caduca actives con end_date pasada')]
class SyncEmployeeStatusesCommand extends Command
{
    public function handle(SyncEmployeeStatuses $action): int
    {
        $result = $action->handle();

        $this->info("Employee statuses — expired: {$result['expired']}, activated: {$result['activated']}");

        return self::SUCCESS;
    }
}
