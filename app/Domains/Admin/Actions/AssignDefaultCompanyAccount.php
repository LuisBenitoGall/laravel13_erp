<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\AccountStatus;
use App\Domains\Admin\Enums\CompanyAccountStatus;
use App\Domains\Admin\Models\Account;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\CompanyAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssignDefaultCompanyAccount
{
    public function handle(Company $company): CompanyAccount
    {
        return DB::transaction(function () use ($company): CompanyAccount {
            $account = Account::query()
                ->where('is_default', true)
                ->where('status', AccountStatus::Active)
                ->first();

            if ($account === null) {
                throw ValidationException::withMessages([
                    'account' => __('cuenta_default_inexistente'),
                ]);
            }

            $existingActive = CompanyAccount::query()
                ->withoutGlobalScope('company')
                ->where('company_id', $company->id)
                ->where('status', CompanyAccountStatus::Active)
                ->exists();

            if ($existingActive) {
                throw ValidationException::withMessages([
                    'company_account' => __('cuenta_empresa_ya_activa'),
                ]);
            }

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
