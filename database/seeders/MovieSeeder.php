<?php

namespace Database\Seeders;

use App\Models\Actor;
use App\Models\Genre;
use App\Models\Movie;
use Illuminate\Database\Seeder;

class MovieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $genres = Genre::all();
        $actors = Actor::all();

        if ($genres->isEmpty() || $actors->isEmpty()) {
            return;
        }

        // Tạo 10 bộ phim mẫu
        Movie::factory()->count(10)->create()->each(function ($movie) use ($genres, $actors) {
            // Gán từ 1 đến 3 thể loại phim ngẫu nhiên
            $randomGenres = $genres->random(rand(1, 3));
            $movie->genres()->attach($randomGenres->pluck('id')->toArray());

            // Gán từ 3 đến 5 diễn viên ngẫu nhiên với vai diễn cụ thể
            $randomActors = $actors->random(rand(3, 5));
            $roles = ['Nam chính', 'Nữ chính', 'Nam phụ', 'Nữ phụ', 'Phản diện', 'Khách mời'];
            
            foreach ($randomActors as $index => $actor) {
                $roleName = isset($roles[$index]) ? $roles[$index] : 'Diễn viên phụ';
                $movie->actors()->attach($actor->id, [
                    'role_name' => $roleName,
                ]);
            }
        });
    }
}
