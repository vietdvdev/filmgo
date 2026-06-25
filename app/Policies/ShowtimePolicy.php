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
    public function create(User $user, Room $room): bool
    {
        // Admin có toàn quyền truy cập
        if ($user->roles()->where('name', 'admin')->exists()) {
            return true;
        }

        // Manager chỉ được tạo suất chiếu trong rạp mình được phân công
        if ($user->roles()->where('name', 'manager')->exists()) {
            return $user->cinemas()->where('cinemas.id', $room->cinema_id)->exists();
        }

        return false;
    }
}
