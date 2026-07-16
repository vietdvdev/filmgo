<?php

namespace Tests\Unit;

use App\Services\TicketQrCodeService;
use PHPUnit\Framework\TestCase;

class TicketQrCodeServiceTest extends TestCase
{
    public function test_it_can_round_trip_ticket_payload(): void
    {
        $service = new TicketQrCodeService();

        $payload = [
            'ticket_id' => 42,
            'order_id' => 'ORD-001',
            'movie_name' => 'Dune Part Two',
            'show_time' => '2026-07-14 20:30:00',
            'seat' => 'A-10',
        ];

        $encrypted = $service->encryptPayload($payload);
        $decoded = $service->decryptPayload($encrypted);

        $this->assertNotEmpty($encrypted);
        $this->assertSame($payload, $decoded);
    }
}
