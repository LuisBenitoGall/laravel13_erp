<?php

namespace Database\Factories;

use App\Domains\Admin\Enums\CompanyAccountStatus;
use App\Domains\Admin\Models\Account;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\CompanyAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CompanyAccount> */
class CompanyAccountFactory extends Factory
{
    protected $model = CompanyAccount::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'account_id' => Account::factory(),
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'price' => fake()->randomFloat(2, 0, 999),
            'payment_date' => null,
            'status' => CompanyAccountStatus::Active,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (CompanyAccount $companyAccount): void {
            if ($companyAccount->status !== CompanyAccountStatus::Active) {
                return;
            }

            CompanyAccount::query()
                ->withoutGlobalScope('company')
                ->where('company_id', $companyAccount->company_id)
                ->whereKeyNot($companyAccount->id)
                ->where('status', CompanyAccountStatus::Active)
                ->update(['status' => CompanyAccountStatus::Cancelled->value]);
        });
    }

    public function expired(): static
    {
        return $this->state([
            'status' => CompanyAccountStatus::Expired,
            'end_date' => now()->subDay()->toDateString(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state([
            'status' => CompanyAccountStatus::Cancelled,
        ]);
    }
}
