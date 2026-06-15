<?php

namespace Database\Factories;

use App\Models\Cinema;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cinema>
 */
class CinemaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $brands = ['CGV', 'Lotte Cinema', 'BHD Star', 'Galaxy Cinema', 'Beta Cinemas'];
        $locations = ['Vincom Plaza', 'Aeon Mall', 'Crescent Mall', 'Tràng Tiền', 'Mipec Mall', 'GigaMall'];
        $cities = ['Hà Nội', 'TP. Hồ Chí Minh', 'Đà Nẵng', 'Hải Phòng', 'Cần Thơ'];

        $city = fake()->randomElement($cities);
        $name = fake()->randomElement($brands) . ' ' . fake()->randomElement($locations) . ' ' . $city;

        return [
            'name' => $name,
            'address' => fake()->streetAddress() . ', Quận ' . fake()->numberBetween(1, 10) . ', ' . $city,
            'phone' => '028' . fake()->numberBetween(30000000, 39999999),
            'city' => $city,
            'status' => 'active',
        ];
    }
}
