<?php

namespace Database\Factories;

use App\Domains\Admin\Models\Company;
use App\Domains\Catalog\Enums\TagStatus;
use App\Domains\Catalog\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Tag> */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'company_id' => Company::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'status' => TagStatus::Inactive,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => [
            'status' => TagStatus::Active,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'status' => TagStatus::Inactive,
        ]);
    }
}
