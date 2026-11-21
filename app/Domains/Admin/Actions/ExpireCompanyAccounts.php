<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\CompanyAccountStatus;
use App\Domains\Admin\Models\CompanyAccount;
use Illuminate\Support\Facades\DB;

class ExpireCompanyAccounts
{
    public function handle(): int
    {
        return DB::transaction(function (): int {
            return CompanyAccount::query()
                ->withoutGlobalScope('company')
                ->where('status', CompanyAccountStatus::Active)
                ->whereNotNull('end_date')
                ->whereDate('end_date', '<', now()->toDateString())
                ->update(['status' => CompanyAccountStatus::Expired->value]);
        });
    }
}
