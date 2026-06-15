<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) {
            return;
        }

        foreach ($users as $user) {
            Notification::factory()->count(rand(1, 3))->create([
                'user_id' => $user->id,
            ]);
        }
    }
}
