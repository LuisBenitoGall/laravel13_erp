<?php

namespace Database\Factories;

use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Preference;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Preference> */
class PreferenceFactory extends Factory
{
    protected $model = Preference::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'module_id' => null,
            'name' => fake()->words(3, true),
            'url' => '/admin/'.fake()->unique()->slug(),
        ];
    }
}
