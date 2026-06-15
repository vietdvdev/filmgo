<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
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
        $title = fake()->sentence(6);
        return [
            'author_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(1000, 9999),
            'thumbnail' => 'posts/thumbnail_' . fake()->numberBetween(1, 5) . '.png',
            'content' => '<p>' . implode('</p><p>', fake()->paragraphs(4)) . '</p>',
            'status' => fake()->randomElement(['draft', 'published', 'archived']),
            'published_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
