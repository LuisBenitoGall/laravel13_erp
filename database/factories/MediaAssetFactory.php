<?php

namespace Database\Factories;

use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\MediaAsset;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** @extends Factory<MediaAsset> */
class MediaAssetFactory extends Factory
{
    protected $model = MediaAsset::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $uuid = (string) Str::uuid();
        $path = 'gallery/'.$uuid.'.png';

        Storage::disk('local')->put($path, $this->tinyPng());

        return [
            'company_id' => null,
            'name' => fake()->words(2, true),
            'alt' => fake()->sentence(3),
            'path' => $path,
            'mime' => 'image/png',
            'width' => 1,
            'height' => 1,
            'bytes' => 68,
        ];
    }

    public function forCompany(Company $company): static
    {
        return $this->state(fn (): array => [
            'company_id' => $company->id,
        ]);
    }

    private function tinyPng(): string
    {
        return (string) base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==',
            true,
        );
    }
}
