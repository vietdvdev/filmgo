<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['booking_confirmed', 'check_in_reminder', 'promotion']),
            'title' => fake()->sentence(4),
            'content' => fake()->paragraph(),
            'is_read' => fake()->randomElement([0, 1]),
            'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
