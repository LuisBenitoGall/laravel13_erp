<?php

namespace Database\Factories;

use App\Domains\Admin\Enums\ContentKind;
use App\Domains\Admin\Enums\ContentStatus;
use App\Domains\Admin\Models\Content;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Content> */
class ContentFactory extends Factory
{
    protected $model = Content::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        $code = Str::slug($name);

        return [
            'name' => $name,
            'code' => $code !== '' ? $code : 'help-'.Str::lower(Str::random(6)),
            'kind' => ContentKind::Help,
            'company_id' => null,
            'title' => [],
            'excerpt' => [],
            'content' => [],
            'referrer' => [],
            'video' => [],
            'status' => ContentStatus::Inactive,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => [
            'status' => ContentStatus::Active,
            'title' => [
                'es' => fake()->sentence(3),
                'en' => fake()->sentence(3),
                'ca' => fake()->sentence(3),
            ],
            'excerpt' => [
                'es' => fake()->sentence(8),
                'en' => fake()->sentence(8),
                'ca' => fake()->sentence(8),
            ],
            'content' => [
                'es' => '<p>'.fake()->paragraph().'</p>',
                'en' => '<p>'.fake()->paragraph().'</p>',
                'ca' => '<p>'.fake()->paragraph().'</p>',
            ],
        ]);
    }

    public function article(): static
    {
        return $this->state(fn (): array => [
            'kind' => ContentKind::Article,
        ]);
    }
}
