<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Genre;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        /**
         * Sử dụng Cache::remember() để tránh query DB lặp lại mỗi request.
         * TTL = 5 phút (300 giây) — đủ tươi cho dữ liệu phim không thay đổi liên tục.
         *
         * Eager load 'genres' ngay trong query để tránh N+1 query
         * khi View render badge thể loại cho từng phim trong danh sách.
         */
        $homeData = Cache::remember('home_page_data', 300, function () {
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
        });

        return view('home', $homeData);
    }
}
