<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '0' . fake()->numberBetween(900000000, 999999999), // Sinh số điện thoại VN dạng 09xxxxxxxx
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password123'),
            'avatar' => 'avatars/avatar_' . fake()->numberBetween(1, 10) . '.png',
            'status' => fake()->randomElement(['active', 'locked']),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
