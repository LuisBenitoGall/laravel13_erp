<?php

namespace Database\Factories;

use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
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
