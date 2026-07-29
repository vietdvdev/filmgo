<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            NotificationSeeder::class,
            ActivityLogSeeder::class,
            PostSeeder::class,
            CinemaSeeder::class,
            SeatTypeSeeder::class,
            RoomSeeder::class,
            SeatSeeder::class,
            UserCinemaSeeder::class,
            GenreSeeder::class,
            ActorSeeder::class,
            MovieSeeder::class,
            FormatSeeder::class,
            ReviewSeeder::class,
            PriceRuleSeeder::class,
            HolidaySeeder::class,
            ShowtimeSeeder::class,
            ShowtimeSeatSeeder::class,
            ComboItemSeeder::class,
            ComboSeeder::class,
            PromotionSeeder::class,
            BookingSeeder::class,
            BookingDetailSeeder::class,
            BookingComboSeeder::class,
            PaymentSeeder::class,
            TicketSeeder::class,
        ]);
    }
}
