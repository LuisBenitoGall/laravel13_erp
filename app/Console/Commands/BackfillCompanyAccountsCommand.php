<?php

namespace App\Console\Commands;

use App\Domains\Admin\Actions\AssignDefaultCompanyAccount;
use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\CompanyAccount;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

#[Signature('company-accounts:backfill')]
#[Description('Asigna Account default a companies Active sin company_accounts')]
class BackfillCompanyAccountsCommand extends Command
{
    public function handle(AssignDefaultCompanyAccount $assignDefault): int
    {
        $assigned = 0;
        $skipped = 0;
        $failed = 0;

        Company::query()
            ->where('status', CompanyStatus::Active)
            ->orderBy('id')
            ->each(function (Company $company) use ($assignDefault, &$assigned, &$skipped, &$failed): void {
                $hasRow = CompanyAccount::query()
                    ->withoutGlobalScope('company')
                    ->where('company_id', $company->id)
                    ->exists();

                if ($hasRow) {
                    $skipped++;

                    return;
                }

                try {
                    $assignDefault->handle($company);
                    $assigned++;
                } catch (ValidationException $exception) {
                    $failed++;
                    $this->warn("Company {$company->id}: ".$exception->getMessage());
                }
            });

        $this->info("Backfill company accounts — assigned: {$assigned}, skipped: {$skipped}, failed: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
