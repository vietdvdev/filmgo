<?php

namespace App\Services;

use App\Models\Movie;
use App\Models\Genre;

class HomeService
{
    /**
     * Lấy dữ liệu cho trang chủ (phim đang chiếu, phim sắp chiếu, thể loại).
     */
    public function getHomePageData(): array
    {
        // 5 phim đang chiếu mới nhất — eager load genres để tránh N+1
        $showingMovies = Movie::with('genres')
            ->where('status', 'showing')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // 5 phim sắp chiếu mới nhất — eager load genres để tránh N+1
        $upcomingMovies = Movie::with('genres')
            ->where('status', 'upcoming')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // Tất cả thể loại phim kèm số lượng phim (withCount tạo sub-query tối ưu)
        $genres = Genre::withCount('movies')->get();

        return compact('showingMovies', 'upcomingMovies', 'genres');
    }
}
