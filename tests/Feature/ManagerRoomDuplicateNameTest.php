<?php

namespace Tests\Feature;

use App\Models\Cinema;
use App\Models\Format;
use App\Models\Role;
use App\Models\Room;
use App\Models\User;
use App\Models\UserCinema;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ManagerRoomDuplicateNameTest extends TestCase
{
    use DatabaseTransactions;

    public function test_manager_cannot_create_duplicate_room_name_with_different_case_or_spaces(): void
    {
        $manager = User::factory()->create(['status' => 'active']);
        $role = Role::firstOrCreate(['name' => 'manager']);
        $manager->roles()->attach($role->id);

        $cinema = Cinema::create([
            'name' => 'Rạp Test',
            'address' => '123 Test',
            'city' => 'Hà Nội',
            'status' => 'active',
        ]);

        UserCinema::create(['user_id' => $manager->id, 'cinema_id' => $cinema->id]);

        $format = Format::firstOrCreate(['name' => '2D'], ['surcharge_price' => 0, 'status' => 'active']);

        Room::create([
            'cinema_id' => $cinema->id,
            'room_name' => 'Phòng 1',
            'capacity' => 60,
            'room_type' => '2D',
            'format_id' => $format->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($manager)
            ->from(route('manager.rooms.create'))
            ->post(route('manager.rooms.store'), [
                'cinema_id' => $cinema->id,
                'room_name' => '  phòng   1  ',
                'capacity' => 80,
                'format_id' => $format->id,
            ]);

        $response->assertSessionHasErrors('room_name');
        $this->assertDatabaseHas('rooms', [
            'cinema_id' => $cinema->id,
            'room_name' => 'Phòng 1',
        ]);
        $this->assertSame(1, Room::where('cinema_id', $cinema->id)->count());
    }

    public function test_manager_cannot_change_room_status_while_room_has_showtimes(): void
    {
        $manager = User::factory()->create(['status' => 'active']);
        $role = Role::firstOrCreate(['name' => 'manager']);
        $manager->roles()->attach($role->id);

        $cinema = Cinema::create([
            'name' => 'Rạp Test 2',
            'address' => '456 Test',
            'city' => 'Hà Nội',
            'status' => 'active',
        ]);

        UserCinema::create(['user_id' => $manager->id, 'cinema_id' => $cinema->id]);

        $format = Format::firstOrCreate(['name' => '2D'], ['surcharge_price' => 0, 'status' => 'active']);
        $room = Room::create([
            'cinema_id' => $cinema->id,
            'room_name' => 'Phòng H1',
            'capacity' => 60,
            'room_type' => '2D',
            'format_id' => $format->id,
            'status' => 'active',
        ]);

        \App\Models\Movie::create([
            'title' => 'Test Movie',
            'slug' => 'test-movie',
            'duration' => 120,
            'status' => 'showing',
            'release_date' => now()->toDateString(),
        ]);

        \App\Models\Showtime::create([
            'movie_id' => \App\Models\Movie::query()->latest('id')->first()->id,
            'format_id' => $format->id,
            'room_id' => $room->id,
            'show_date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
            'base_price' => 80000,
            'status' => 'active',
        ]);

        $response = $this->actingAs($manager)
            ->from(route('manager.rooms.edit', $room->id))
            ->put(route('manager.rooms.update', $room->id), [
                'room_name' => 'Phòng H1',
                'capacity' => 60,
                'format_id' => $format->id,
                'status' => 'maintenance',
            ]);

        $response->assertSessionHasErrors('status');
        $this->assertDatabaseHas('rooms', ['id' => $room->id, 'status' => 'active']);
    }
}
