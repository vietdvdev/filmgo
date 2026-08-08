<?php

namespace Tests\Feature;

use App\Models\Cinema;
use App\Models\Room;
use App\Models\Seat;
use App\Models\SeatType;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RoomSeatGeneratorTest extends TestCase
{
    use DatabaseTransactions;

    protected User $manager;
    protected Cinema $cinema;
    protected Room $room;
    protected SeatType $coupleSeatType;
    protected SeatType $standardSeatType;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Tao nguoi dung Manager & Rap duoc phan cong
        $this->manager = User::factory()->create();
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $this->manager->roles()->attach($managerRole->id);

        $this->cinema = Cinema::create([
            'name'        => 'FilmGo Test Cinema',
            'city'        => 'Hanoi',
            'address'     => '123 Test Street',
        ]);

        $this->manager->cinemas()->attach($this->cinema->id);

        $this->room = Room::create([
            'cinema_id' => $this->cinema->id,
            'room_name' => 'Room 01',
            'room_type' => '2D',
            'capacity'  => 100,
            'status'    => 'active',
        ]);

        // 2. Tao loai ghe Sweetbox/Couple va loai ghe thuong
        $this->coupleSeatType = SeatType::firstOrCreate(
            ['name' => 'Ghế Sweetbox / Đôi'],
            ['surcharge_price' => 30000]
        );

        $this->standardSeatType = SeatType::firstOrCreate(
            ['name' => 'Ghế Thường'],
            ['surcharge_price' => 0]
        );
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
        $this->assertEquals(0, Seat::where('room_id', $this->room->id)->count());
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
        $this->assertEquals(4, Seat::where('room_id', $this->room->id)->count());

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
                'seat_type_id'       => $this->standardSeatType->id, // Standard seat type
                'couple_seats_count' => 0, // Zero -> MUST SUCCEED
            ]);

        $response->assertSessionHas('success');
        $this->assertEquals(6, Seat::where('room_id', $this->room->id)->count());
    }

    /**
     * TC4: Couple seat type with odd range (e.g. start=1, end=3) and no couple_seats_count specified MUST fail.
     */
    public function test_tc4_couple_seat_type_odd_range_fails_validation(): void
    {
        $response = $this->actingAs($this->manager)
            ->post(route('manager.rooms.seats.bulk', $this->room->id), [
                'seat_row'     => 'D',
                'start_number' => 1,
                'end_number'   => 3, // 3 seats (odd) -> MUST FAIL
                'seat_type_id' => $this->coupleSeatType->id,
            ]);

        $response->assertSessionHasErrors(['couple_seats_count']);
        $this->assertEquals(0, Seat::where('room_id', $this->room->id)->count());
    }

    /**
     * TC5: Deleting 1 Sweetbox seat automatically deletes both seats in the pair.
     */
    public function test_tc5_deleting_sweetbox_seat_deletes_both_seats_in_pair(): void
    {
        $seat1 = Seat::create([
            'room_id'      => $this->room->id,
            'seat_type_id' => $this->coupleSeatType->id,
            'seat_row'     => 'E',
            'seat_number'  => 1,
            'status'       => 'active',
        ]);

        $seat2 = Seat::create([
            'room_id'      => $this->room->id,
            'seat_type_id' => $this->coupleSeatType->id,
            'seat_row'     => 'E',
            'seat_number'  => 2,
            'status'       => 'active',
        ]);

        $this->assertEquals(2, Seat::where('room_id', $this->room->id)->count());

        $seatService = app(\App\Services\ManagerSeatService::class);
        $result = $seatService->deleteSeat($this->room->id, $seat1->id, $this->cinema->id);

        $this->assertTrue($result);
        $this->assertEquals(0, Seat::where('room_id', $this->room->id)->count());
    }

    /**
     * TC6: Syncing unpaired Sweetbox seat fails validation.
     */
    public function test_tc6_syncing_unpaired_sweetbox_seat_fails(): void
    {
        $syncService = app(\App\Services\RoomSeatSyncService::class);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $syncService->sync($this->room, [
            [
                'seat_row'     => 'F',
                'seat_number'  => 1,
                'seat_type_id' => $this->coupleSeatType->id, // Single unpaired Sweetbox
                'status'       => 'active',
            ]
        ]);
    }

    /**
     * TC7: Syncing seat map IS ALLOWED when showtime is upcoming or in future.
     */
    public function test_tc7_syncing_seat_map_allowed_when_showtime_is_upcoming(): void
    {
        $movie = \App\Models\Movie::factory()->create(['duration' => 120]);
        $format = \App\Models\Format::firstOrCreate(['name' => '2D']);

        // Create upcoming showtime tomorrow
        $showtime = \App\Models\Showtime::create([
            'movie_id'   => $movie->id,
            'format_id'  => $format->id,
            'room_id'    => $this->room->id,
            'show_date'  => now()->addDays(2)->toDateString(),
            'start_time' => '18:00:00',
            'end_time'   => '20:00:00',
            'base_price' => 80000,
            'status'     => 'upcoming',
        ]);

        $syncService = app(\App\Services\RoomSeatSyncService::class);

        // Guard against currently showing should pass without throwing exception
        $syncService->guardAgainstActiveBookingsOrCurrentlyShowing($this->room->id);

        $result = $syncService->sync($this->room, [
            [
                'seat_row'     => 'A',
                'seat_number'  => 1,
                'seat_type_id' => $this->standardSeatType->id,
                'status'       => 'active',
            ]
        ]);

        $this->assertEquals(1, $result['seat_count']);
    }

    /**
     * TC8: Syncing seat map IS BLOCKED when showtime is currently showing in progress.
     */
    public function test_tc8_syncing_seat_map_blocked_when_showtime_is_currently_showing(): void
    {
        $movie = \App\Models\Movie::factory()->create(['duration' => 120]);
        $format = \App\Models\Format::firstOrCreate(['name' => '2D']);

        // Create showtime currently showing right now
        $now = now();
        $startTime = $now->copy()->subMinutes(30)->format('H:i:s');
        $endTime   = $now->copy()->addMinutes(90)->format('H:i:s');

        \App\Models\Showtime::create([
            'movie_id'   => $movie->id,
            'format_id'  => $format->id,
            'room_id'    => $this->room->id,
            'show_date'  => $now->toDateString(),
            'start_time' => $startTime,
            'end_time'   => $endTime,
            'base_price' => 80000,
            'status'     => 'showing',
        ]);

        $syncService = app(\App\Services\RoomSeatSyncService::class);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        // Should throw ValidationException blocking seat map modification
        $syncService->guardAgainstActiveBookingsOrCurrentlyShowing($this->room->id);
    }
}
