<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\AccountStatus;
use App\Domains\Admin\Enums\CompanyAccountStatus;
use App\Domains\Admin\Models\Account;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\CompanyAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RenewCompanyAccount
{
    public function handle(Company $company, Account $account): CompanyAccount
    {
        return DB::transaction(function () use ($company, $account): CompanyAccount {
            if ($account->status !== AccountStatus::Active) {
                throw ValidationException::withMessages([
                    'account_id' => __('cuenta_catalogo_inactiva'),
                ]);
            }

            CompanyAccount::query()
                ->withoutGlobalScope('company')
                ->where('company_id', $company->id)
                ->where('status', CompanyAccountStatus::Active)
                ->update(['status' => CompanyAccountStatus::Cancelled->value]);

            $start = now()->startOfDay();
            $end = $account->duration !== null
                ? $start->copy()->addDays((int) $account->duration)
                : null;

            return CompanyAccount::query()->withoutGlobalScope('company')->create([
                'company_id' => $company->id,
                'account_id' => $account->id,
                'start_date' => $start->toDateString(),
                'end_date' => $end?->toDateString(),
                'price' => $account->rate,
                'payment_date' => null,
                'status' => CompanyAccountStatus::Active,
            ]);
        });
    }
}
