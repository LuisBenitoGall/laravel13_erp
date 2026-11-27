<?php

namespace Database\Factories;

use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\TableFilterPreset;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TableFilterPreset> */
class TableFilterPresetFactory extends Factory
{
    protected $model = TableFilterPreset::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'table_key' => 'catalog.products',
            'name' => fake()->unique()->words(2, true),
            'filters' => [
                ['field' => 'status', 'type' => 'eq', 'value' => 'active'],
            ],
        ];
    }
}
