<?php

namespace Database\Factories;

use App\Domains\Partners\Models\Company;
use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserCompany>
 */
class UserCompanyFactory extends Factory
{
    protected $model = UserCompany::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_id' => Company::factory(),
            'position' => null,
            'department' => null,
        ];
    }
}
