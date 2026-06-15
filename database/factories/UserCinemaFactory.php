<?php

namespace Database\Factories;

use App\Models\Cinema;
use App\Models\User;
use App\Models\UserCinema;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserCinema>
 */
class UserCinemaFactory extends Factory
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
            'cinema_id' => Cinema::factory(),
            'created_at' => now(),
        ];
    }
}
