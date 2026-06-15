<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivityLogSeeder extends Seeder
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
            ActivityLog::factory()->count(rand(2, 5))->create([
                'user_id' => $user->id,
            ]);
        }
    }
}
