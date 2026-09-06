<?php

namespace Database\Factories;

use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Module> */
class ModuleFactory extends Factory
{
    protected $model = Module::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'label' => ['es' => Str::title($name), 'en' => Str::title($name)],
            'color' => '#0f766e',
            'icon' => 'settings',
            'level' => ModuleLevel::Basic,
            'status' => ModuleStatus::Active,
            'explanation' => ['es' => fake()->sentence(), 'en' => fake()->sentence()],
        ];
    }
}
