<?php

namespace Database\Factories;

use App\Domains\Admin\Enums\PaymentMethodStatus;
use App\Domains\Admin\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PaymentMethod> */
class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $label = fake()->unique()->words(2, true);

        return [
            'name' => [
                'es' => ucfirst($label).' ES',
                'en' => ucfirst($label).' EN',
                'ca' => ucfirst($label).' CA',
            ],
            'status' => PaymentMethodStatus::Active,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'status' => PaymentMethodStatus::Inactive,
        ]);
    }
}
