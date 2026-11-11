<?php

namespace Database\Factories;

use App\Domains\Admin\Enums\PhoneType;
use App\Models\Phone;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Phone>
 */
class PhoneFactory extends Factory
{
    protected $model = Phone::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'phoneable_type' => User::class,
            'phoneable_id' => User::factory(),
            'e164' => '+34'.fake()->unique()->numerify('#########'),
            'country_id' => null,
            'ext' => null,
            'type' => PhoneType::Mobile,
            'label' => fake()->optional()->word(),
            'is_primary' => false,
            'is_whatsapp' => false,
            'is_verified' => false,
            'verified_at' => null,
            'notes' => fake()->optional()->sentence(3),
        ];
    }

    public function primary(): static
    {
        return $this->state(fn (): array => ['is_primary' => true]);
    }

    public function whatsapp(): static
    {
        return $this->state(fn (): array => ['is_whatsapp' => true]);
    }
}
