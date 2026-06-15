<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $authors = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['admin', 'manager', 'staff']);
        })->get();

        if ($authors->isEmpty()) {
            $authors = User::all();
        }

        if ($authors->isEmpty()) {
            return;
        }

        foreach ($authors as $author) {
            Post::factory()->count(rand(1, 3))->create([
                'author_id' => $author->id,
            ]);
        }
    }
}
