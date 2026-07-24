<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;

class ShowtimePolicy
{
    /**
     * Determine if the user can create showtimes.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Room  $room  Phòng chiếu muốn tạo suất chiếu
     */
    public function create(User $user, mixed $room = null): bool
    {
        if (is_array($room)) {
            $room = $room[0] ?? null;
        }

        // Admin có toàn quyền truy cập
        if ($user->roles()->where('name', 'admin')->exists()) {
            return true;
        }

        // Manager chỉ được tạo suất chiếu trong rạp mình được phân công
        if ($user->roles()->where('name', 'manager')->exists() && $room instanceof Room) {
            return $user->cinemas()->where('cinemas.id', $room->cinema_id)->exists();
        }

        return false;
    }
}
