<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Genre;

class HomeController extends Controller
{
    public function index()
    {
        // 5 phim đang chiếu ngẫu nhiên
        $showingMovies = Movie::where('status', 'showing')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // 5 phim sắp chiếu ngẫu nhiên
        $upcomingMovies = Movie::where('status', 'upcoming')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // Tất cả thể loại phim có đếm số lượng phim
        $genres = Genre::withCount('movies')->get();

        return view('home', compact(
            'showingMovies',
            'upcomingMovies',
            'genres'
        ));
    }
}
