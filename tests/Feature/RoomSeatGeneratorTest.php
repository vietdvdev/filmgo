<?php

namespace Tests\Feature;

use App\Models\Cinema;
use App\Models\Room;
use App\Models\Seat;
use App\Models\SeatType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomSeatGeneratorTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;
    protected Cinema $cinema;
    protected Room $room;
    protected SeatType $coupleSeatType;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Tao nguoi dung Manager & Rap duoc phan cong
        $this->manager = User::factory()->create([
            'role_id' => 2, // Manager
        ]);

        $this->cinema = Cinema::create([
            'cinema_name' => 'FilmGo Test Cinema',
            'city'        => 'Hanoi',
            'address'     => '123 Test Street',
        ]);

        $this->manager->cinemas()->attach($this->cinema->id);

        $this->room = Room::create([
            'cinema_id' => $this->cinema->id,
            'room_name' => 'Room 01',
            'room_type' => '2D',
            'status'    => 'active',
        ]);

        // 2. Tao loai ghe Sweetbox/Couple (id = 3)
        $this->coupleSeatType = SeatType::create([
            'id'              => 3,
            'name'            => 'Ghế Sweetbox / Đôi',
            'surcharge_price' => 30000,
        ]);
    }

    /**
     * TC1: Validation rule catches odd couple_seats_count (e.g. 3) and fails.
     */
    public function test_tc1_couple_seats_count_odd_number_fails_validation(): void
    {
        $response = $this->actingAs($this->manager)
            ->post(route('manager.rooms.seats.bulk', $this->room->id), [
                'seat_row'           => 'C',
                'start_number'       => 1,
                'end_number'         => 3,
                'seat_type_id'       => $this->coupleSeatType->id,
                'couple_seats_count' => 3, // Odd number -> MUST FAIL
            ]);

        $response->assertSessionHasErrors(['couple_seats_count']);
        $this->assertDatabaseCount('seats', 0);
    }

    /**
     * TC2: Even couple_seats_count (e.g. 4) succeeds and creates 4 individual seat rows forming 2 pairs.
     */
    public function test_tc2_couple_seats_count_even_number_succeeds(): void
    {
        $response = $this->actingAs($this->manager)
            ->post(route('manager.rooms.seats.bulk', $this->room->id), [
                'seat_row'           => 'C',
                'start_number'       => 1,
                'end_number'         => 4,
                'seat_type_id'       => $this->coupleSeatType->id,
                'couple_seats_count' => 4, // Even number -> MUST SUCCEED
            ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseCount('seats', 4);

        // Check 4 individual rows created in DB
        $seats = Seat::where('room_id', $this->room->id)->where('seat_row', 'C')->orderBy('seat_number')->get();
        $this->assertCount(4, $seats);
        $this->assertEquals(1, $seats[0]->seat_number);
        $this->assertEquals(2, $seats[1]->seat_number);
        $this->assertEquals(3, $seats[2]->seat_number);
        $this->assertEquals(4, $seats[3]->seat_number);
    }

    /**
     * TC3: couple_seats_count = 0 succeeds (no couple seats generated).
     */
    public function test_tc3_couple_seats_count_zero_succeeds(): void
    {
        $response = $this->actingAs($this->manager)
            ->post(route('manager.rooms.seats.bulk', $this->room->id), [
                'seat_row'           => 'A',
                'start_number'       => 1,
                'end_number'         => 6,
                'seat_type_id'       => 1, // Standard seat type
                'couple_seats_count' => 0, // Zero -> MUST SUCCEED
            ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseCount('seats', 6);
    }
}
