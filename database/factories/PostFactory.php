<?php

namespace Database\Factories;

use App\Enums\PostStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement([
            PostStatusEnum::DRAFT,
            PostStatusEnum::PUBLISHED,
            PostStatusEnum::ARCHIVED,
        ]);
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'status' => $status,
            'published_at' => $status->value == 'published' ? now() : null,
            'user_id' => function () {
                return \App\Models\User::factory()->create()->id;
            },
        ];
    }
}
