<?php

namespace Database\Factories;

use App\Domains\Admin\Models\Company;
use App\Domains\HR\Enums\EmployeeStatus;
use App\Domains\HR\Models\ContractType;
use App\Domains\HR\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Employee> */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    /**
     * El test decide el tenant con `company_id`; contract_type y user se crean
     * ligados a esa misma company (FKs del mismo tenant).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'contract_type_id' => fn (array $attributes): int => ContractType::factory()
                ->create(['company_id' => $attributes['company_id']])->id,
            'workplace_id' => null,
            'job_title' => fake()->jobTitle(),
            'start_date' => now()->subMonths(3)->toDateString(),
            'end_date' => null,
            'weekly_hours' => 40,
            'time_control' => true,
            'year_salary' => fake()->randomFloat(2, 12000, 60000),
            'status' => EmployeeStatus::Active,
            'observations' => null,
        ];
    }

    public function scheduled(): static
    {
        return $this->state(fn (): array => [
            'start_date' => now()->addMonth()->toDateString(),
            'status' => EmployeeStatus::Scheduled,
        ]);
    }

    public function ended(): static
    {
        return $this->state(fn (): array => [
            'start_date' => now()->subYear()->toDateString(),
            'end_date' => now()->subMonths(4)->toDateString(),
            'status' => EmployeeStatus::Ended,
        ]);
    }
}
