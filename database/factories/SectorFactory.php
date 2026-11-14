<?php

namespace Database\Factories;

use App\Domains\Admin\Enums\SectorStatus;
use App\Domains\Admin\Models\Sector;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Sector> */
class SectorFactory extends Factory
{
    protected $model = Sector::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $word = Str::title(fake()->unique()->word());

        return [
            'slug' => Str::slug($word).'-'.fake()->unique()->numerify('###'),
            'name' => [
                'es' => $word,
                'en' => $word.' EN',
                'ca' => $word.' CA',
            ],
            'status' => SectorStatus::Active,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['status' => SectorStatus::Inactive]);
    }
}
