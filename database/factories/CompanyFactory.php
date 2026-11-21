<?php

namespace Database\Factories;

use App\Domains\Admin\Enums\AccountStatus;
use App\Domains\Admin\Enums\CompanyAccountStatus;
use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Models\Account;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\CompanyAccount;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/** @extends Factory<Company> */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'tradename' => $name,
            'nif' => strtoupper(fake()->bothify('?########?')),
            'status' => CompanyStatus::Active,
            'logo' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Company $company): void {
            if (! Schema::hasTable('company_accounts') || ! Schema::hasTable('accounts')) {
                return;
            }

            $hasRow = CompanyAccount::query()
                ->withoutGlobalScope('company')
                ->where('company_id', $company->id)
                ->exists();

            if ($hasRow) {
                return;
            }

            $account = Account::query()->where('is_default', true)->first()
                ?? Account::factory()->default()->create(['status' => AccountStatus::Active]);

            CompanyAccount::query()->withoutGlobalScope('company')->create([
                'company_id' => $company->id,
                'account_id' => $account->id,
                'start_date' => now()->toDateString(),
                'end_date' => $account->duration !== null
                    ? now()->addDays((int) $account->duration)->toDateString()
                    : null,
                'price' => $account->rate,
                'payment_date' => null,
                'status' => CompanyAccountStatus::Active,
            ]);
        });
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'status' => CompanyStatus::Draft,
        ]);
    }

    /** Textos multi-idioma para tests de Edit Info. */
    public function withTexts(): static
    {
        return $this->state(fn (): array => [
            'excerpt' => [
                'es' => 'Extracto ES',
                'en' => 'Excerpt EN',
                'ca' => 'Extracte CA',
            ],
            'description' => [
                'es' => '<p>Descripción ES</p>',
                'en' => '<p>Description EN</p>',
                'ca' => '<p>Descripció CA</p>',
            ],
        ]);
    }
}
