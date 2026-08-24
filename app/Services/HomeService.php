<?php

namespace App\Services;

use App\Models\Movie;
use App\Models\Genre;
use App\Models\Cinema;

class HomeService
{
    /**
     * Lấy dữ liệu cho trang chủ (phim đang chiếu, phim sắp chiếu, thể loại, rạp).
     */
    public function getHomePageData(): array
    {
        // 10 phim đang chiếu mới nhất — eager load genres, formats, reviews
        $showingMovies = Movie::with(['genres', 'formats', 'reviews'])
            ->where('status', 'showing')
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        // 10 phim sắp chiếu mới nhất — eager load genres, formats
        $upcomingMovies = Movie::with(['genres', 'formats'])
            ->where('status', 'upcoming')
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        // Phim tiêu điểm (Featured Movie) dành cho Hero Section
        $featuredMovie = $showingMovies->first() ?? $upcomingMovies->first();

        // Tất cả thể loại phim kèm số lượng phim
        $genres = Genre::withCount('movies')->get();

        // Danh sách các rạp đang hoạt động phục vụ thanh Quick Booking
        $cinemas = Cinema::where('status', 'active')->orderBy('name')->get();

        return compact('showingMovies', 'upcomingMovies', 'featuredMovie', 'genres', 'cinemas');
    }
}
