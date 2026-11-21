<?php

namespace App\Console\Commands;

use App\Domains\Admin\Actions\ExpireCompanyAccounts;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('company-accounts:expire')]
#[Description('Marca CompanyAccount Active con end_date pasada como Expired')]
class ExpireCompanyAccountsCommand extends Command
{
    public function handle(ExpireCompanyAccounts $action): int
    {
        $count = $action->handle();

        $this->info("Company accounts expired: {$count}");

        return self::SUCCESS;
    }
}
