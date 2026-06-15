<?php

namespace Database\Factories;

use App\Models\BookingDetail;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_detail_id' => BookingDetail::factory(),
            'qr_code' => 'QR-' . Str::upper(Str::random(12)),
            'ticket_status' => 'unused',
            'checked_in_at' => null,
        ];
    }
}
