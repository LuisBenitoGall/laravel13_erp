<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserDoc;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<UserDoc>
 */
class UserDocFactory extends Factory
{
    protected $model = UserDoc::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $uuid = Str::uuid()->toString();

        return [
            'user_id' => User::factory(),
            'path' => 'users/0/docs/'.$uuid.'.pdf',
            'original_name' => fake()->words(2, true).'.pdf',
            'mime' => 'application/pdf',
            'size_bytes' => fake()->numberBetween(1024, 500_000),
        ];
    }
}
