<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
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
            'action' => fake()->randomElement(['LOGIN', 'LOGOUT', 'BOOK_TICKET', 'CANCEL_BOOKING', 'UPDATE_PROFILE', 'DELETE_MOVIE', 'LOCK_USER']),
            'model_type' => fake()->randomElement([null, 'User', 'Movie', 'Booking']),
            'model_id' => fake()->randomElement([null, fake()->numberBetween(1, 100)]),
            'description' => fake()->sentence(),
            'ip_address' => fake()->ipv4(),
            'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
