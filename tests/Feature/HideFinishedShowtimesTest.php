<?php

namespace Tests\Feature;

use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Showtime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HideFinishedShowtimesTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;
    protected Cinema $cinema;
    protected Room $room;
    protected Movie $movie;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Tao tai khoan Staff & Rap duoc phan cong
        $this->staff = User::factory()->create([
            'role_id' => 3, // Staff
        ]);
        $this->staff->roles()->attach(3); // Attach staff role

        $this->cinema = Cinema::create([
            'cinema_name' => 'FilmGo Test Cinema',
            'city'        => 'Hanoi',
            'address'     => '123 Test Street',
        ]);

        $this->staff->cinemas()->attach($this->cinema->id);

        $this->room = Room::create([
            'cinema_id' => $this->cinema->id,
            'room_name' => 'Room 01',
            'room_type' => '2D',
            'status'    => 'active',
        ]);

        $this->movie = Movie::create([
            'title'        => 'Test Movie',
            'duration'     => 120,
            'age_limit'    => 'P',
            'status'       => 'showing',
            'release_date' => today()->subDays(5),
            'end_date'     => today()->addDays(5),
        ]);
    }

    /**
     * TC1: Showtime 09:00 - 11:00, Current time 11:05. Toggle is ON (default).
     * Expected: Showtime is excluded from SQL query and not visible on UI.
     */
    public function test_tc1_past_showtime_excluded_when_toggle_on(): void
    {
        // Showtime đã kết thúc hôm nay (09:00 - 11:00)
        $pastShowtime = Showtime::create([
            'movie_id'   => $this->movie->id,
            'room_id'    => $this->room->id,
            'show_date'  => today(),
            'start_time' => '09:00:00',
            'end_time'   => '11:00:00',
            'base_price' => 80000,
            'status'     => 'active',
        ]);

        // Giả lập thời gian hiện tại là 11:05
        $this->travelTo(today()->setTime(11, 5, 0));

        $response = $this->actingAs($this->staff)
            ->get(route('staff.showtimes.index'));

        $response->assertStatus(200);
        $response->assertDontSee('09:00');
    }

    /**
     * TC2: Showtime 14:00 - 16:00, Current time 13:30. Toggle is ON.
     * Expected: Showtime is visible and active for booking.
     */
    public function test_tc2_upcoming_showtime_visible_when_toggle_on(): void
    {
        // Showtime sắp chiếu hôm nay (14:00 - 16:00)
        $upcomingShowtime = Showtime::create([
            'movie_id'   => $this->movie->id,
            'room_id'    => $this->room->id,
            'show_date'  => today(),
            'start_time' => '14:00:00',
            'end_time'   => '16:00:00',
            'base_price' => 90000,
            'status'     => 'active',
        ]);

        // Giả lập thời gian hiện tại là 13:30
        $this->travelTo(today()->setTime(13, 30, 0));

        $response = $this->actingAs($this->staff)
            ->get(route('staff.showtimes.index'));

        $response->assertStatus(200);
        $response->assertSee('14:00');
        $response->assertSee('Bán vé');
    }

    /**
     * TC3: Showtime 09:00 - 11:00, Current time 11:05. Toggle is OFF (include_ended=true).
     * Expected: Showtime is fetched but rendered on UI with opacity-50 grayscale and disabled badge "Đã kết thúc".
     */
    public function test_tc3_past_showtime_rendered_disabled_when_include_ended_true(): void
    {
        $pastShowtime = Showtime::create([
            'movie_id'   => $this->movie->id,
            'room_id'    => $this->room->id,
            'show_date'  => today(),
            'start_time' => '09:00:00',
            'end_time'   => '11:00:00',
            'base_price' => 80000,
            'status'     => 'active',
        ]);

        // Giả lập thời gian hiện tại là 11:05
        $this->travelTo(today()->setTime(11, 5, 0));

        $response = $this->actingAs($this->staff)
            ->get(route('staff.showtimes.index', ['include_ended' => 'true']));

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('opacity-50 grayscale bg-gray-100 pointer-events-none cursor-not-allowed');
        $response->assertSee('Đã kết thúc');
    }
}
