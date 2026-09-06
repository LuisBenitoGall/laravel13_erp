<?php

namespace Database\Factories;

use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Functionality> */
class FunctionalityFactory extends Factory
{
    protected $model = Functionality::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'module_id' => Module::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'label' => ['es' => Str::title($name), 'en' => Str::title($name)],
            'status' => FunctionalityStatus::Active,
        ];
    }
}
