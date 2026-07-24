<?php

namespace Tests\Feature;

use App\Models\Cinema;
use App\Models\Format;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Seat;
use App\Models\User;
use App\Models\Role;
use App\Models\UserCinema;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MovieFormatShowtimeTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected Cinema $cinema;
    protected Format $format2D;
    protected Format $format3D;
    protected Format $formatImax;
    protected Movie $movie;
    protected Room $room2D;
    protected Room $room3D;
    protected Room $roomImax;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Tạo User Manager
        $this->admin = User::factory()->create();
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $this->admin->roles()->attach($managerRole->id);

        // 2. Tạo rạp chiếu & phân công
        $this->cinema = Cinema::create([
            'name'    => 'FilmGo MegaMall',
            'address' => '123 Nguyễn Trãi',
            'city'    => 'Hà Nội',
            'status'  => 'active',
        ]);
        UserCinema::create(['user_id' => $this->admin->id, 'cinema_id' => $this->cinema->id]);

        // 3. Tạo/lấy các định dạng (2D, 3D, IMAX)
        $this->format2D   = Format::firstOrCreate(['name' => '2D'], ['surcharge_price' => 0]);
        $this->format3D   = Format::firstOrCreate(['name' => '3D'], ['surcharge_price' => 30000]);
        $this->formatImax = Format::firstOrCreate(['name' => 'IMAX'], ['surcharge_price' => 60000]);

        // 4. Tạo phòng chiếu với các loại khác nhau & loại ghế
        $seatType = \App\Models\SeatType::firstOrCreate(['name' => 'Ghế Thường'], ['price_multiplier' => 1.0, 'surcharge' => 0]);

        $this->room2D = Room::create([
            'cinema_id' => $this->cinema->id,
            'room_name' => 'Phòng 1 (2D)',
            'capacity'  => 100,
            'room_type' => '2D',
            'status'    => 'active',
        ]);
        Seat::create(['room_id' => $this->room2D->id, 'seat_type_id' => $seatType->id, 'seat_row' => 'A', 'seat_number' => 1]);

        $this->room3D = Room::create([
            'cinema_id' => $this->cinema->id,
            'room_name' => 'Phòng 2 (3D)',
            'capacity'  => 100,
            'room_type' => '3D',
            'status'    => 'active',
        ]);
        Seat::create(['room_id' => $this->room3D->id, 'seat_type_id' => $seatType->id, 'seat_row' => 'A', 'seat_number' => 1]);

        $this->roomImax = Room::create([
            'cinema_id' => $this->cinema->id,
            'room_name' => 'Phòng 3 (IMAX)',
            'capacity'  => 100,
            'room_type' => 'IMAX',
            'status'    => 'active',
        ]);
        Seat::create(['room_id' => $this->roomImax->id, 'seat_type_id' => $seatType->id, 'seat_row' => 'A', 'seat_number' => 1]);

        // 5. Tạo bộ phim và gán định dạng hỗ trợ (chỉ 2D và 3D, không hỗ trợ IMAX)
        $this->movie = Movie::create([
            'title'        => 'Avatar 3',
            'slug'         => 'avatar-3',
            'duration'     => 180,
            'release_date' => now()->toDateString(),
            'status'       => 'showing',
        ]);

        $this->movie->formats()->attach([$this->format2D->id, $this->format3D->id]);
    }

    /**
     * PHẦN 1 & 2: Kiểm tra quan hệ Eloquent Models
     */
    public function test_movie_and_format_relationships(): void
    {
        $this->assertCount(2, $this->movie->formats);
        $this->assertTrue($this->movie->formats->contains($this->format2D));
        $this->assertTrue($this->movie->formats->contains($this->format3D));
        $this->assertFalse($this->movie->formats->contains($this->formatImax));

        $this->assertTrue($this->format2D->movies->contains($this->movie));
    }

    /**
     * PHẦN 3 - BƯỚC 1: Cascading Dropdown (Lấy danh sách định dạng của Phim)
     */
    public function test_api_get_formats_by_movie(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson("/manager/showtimes/api/formats-by-movie/{$this->movie->id}");

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['name' => '2D'])
            ->assertJsonFragment(['name' => '3D'])
            ->assertJsonMissing(['name' => 'IMAX']);
    }

    /**
     * PHẦN 3 - BƯỚC 2: Cascading Dropdown (Lọc phòng chiếu đủ tiêu chuẩn theo định dạng)
     */
    public function test_api_get_compatible_rooms_for_3d_format(): void
    {
        // Chọn định dạng 3D -> chỉ phòng 3D và IMAX thỏa mãn (bỏ qua phòng 2D)
        $response = $this->actingAs($this->admin)
            ->getJson("/manager/showtimes/api/compatible-rooms?cinema_id={$this->cinema->id}&format_id={$this->format3D->id}");

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['room_name' => 'Phòng 2 (3D)'])
            ->assertJsonFragment(['room_name' => 'Phòng 3 (IMAX)'])
            ->assertJsonMissing(['room_name' => 'Phòng 1 (2D)']);
    }

    /**
     * PHẦN 3 - BƯỚC 3: Validation hàm Store từ chối phòng không đủ tiêu chuẩn chiếu 3D
     */
    public function test_store_showtime_fails_when_room_is_incompatible_with_format(): void
    {
        // Chọn định dạng 3D nhưng cố tình chọn Phòng 1 (2D)
        $payload = [
            'movie_id'   => $this->movie->id,
            'format_id'  => $this->format3D->id,
            'cinema_id'  => $this->cinema->id,
            'room_id'    => $this->room2D->id, // Không đủ tiêu chuẩn!
            'show_date'  => now()->addDays(2)->format('Y-m-d'),
            'start_time' => '14:00',
            'base_price' => 90000,
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/manager/showtimes/api/store', $payload);

        $response->assertStatus(422);
        $this->assertArrayHasKey('room_id', $response->json()['errors']);
    }

    /**
     * PHẦN 3 - BƯỚC 3: Tạo suất chiếu thành công với Định dạng và Phòng chiếu hợp lệ
     */
    public function test_store_showtime_success_with_valid_format_and_room(): void
    {
        $payload = [
            'movie_id'   => $this->movie->id,
            'format_id'  => $this->format3D->id,
            'cinema_id'  => $this->cinema->id,
            'room_id'    => $this->room3D->id, // Hợp lệ!
            'show_date'  => now()->addDays(2)->format('Y-m-d'),
            'start_time' => '14:00',
            'base_price' => 120000,
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/manager/showtimes/api/store', $payload);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('showtimes', [
            'movie_id'  => $this->movie->id,
            'format_id' => $this->format3D->id,
            'room_id'   => $this->room3D->id,
        ]);
    }
}
