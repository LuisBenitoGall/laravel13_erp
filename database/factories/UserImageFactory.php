<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserImage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<UserImage>
 */
class UserImageFactory extends Factory
{
    protected $model = UserImage::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory();

        return [
            'user_id' => $user,
            'image' => 'users/0/images/'.Str::uuid()->toString().'.jpg',
            'featured' => false,
            'public' => false,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn (): array => ['featured' => true]);
    }

    public function publiclyVisible(): static
    {
        return $this->state(fn (): array => ['public' => true]);
    }
}
