<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Movie;

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

        return view('home', compact(
            'showingMovies',
            'upcomingMovies'
        ));
    }
}
