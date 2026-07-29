<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Genre;
use App\Models\Showtime;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Services\ShowtimeService;

class MovieController extends Controller
{
    protected ShowtimeService $showtimeService;

    public function __construct(ShowtimeService $showtimeService)
    {
        $this->showtimeService = $showtimeService;
    }

    public function showing(Request $request)
    {
        // Eager load 'genres' để tránh N+1 query khi view render badge thể loại
        $query = Movie::with('genres')->where('status', 'showing');

        if ($request->filled('genre_id')) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('genres.id', $request->genre_id);
            });
        }

        if ($request->filled('age_limit')) {
            $query->where('age_limit', $request->age_limit);
        }

        $movies = $query->latest()->paginate(20)->withQueryString();
        $genres = Genre::orderBy('name')->get();

        // Chỉ lấy cột cần thiết, distinct giúp tránh scan toàn bảng
        $ageLimits = Movie::where('status', 'showing')
            ->distinct()
            ->orderBy('age_limit')
            ->pluck('age_limit')
            ->toArray();

        return view(
            'customer.movies.showing',
            compact('movies', 'genres', 'ageLimits')
        );
    }

    public function upcoming(Request $request)
    {
        // Eager load 'genres' để tránh N+1 query khi view render badge thể loại
        $query = Movie::with('genres')->where('status', 'upcoming');

        if ($request->filled('genre_id')) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('genres.id', $request->genre_id);
            });
        }

        if ($request->filled('age_limit')) {
            $query->where('age_limit', $request->age_limit);
        }

        $movies = $query->orderBy('release_date')->paginate(20)->withQueryString();
        $genres = Genre::orderBy('name')->get();

        // Chỉ lấy cột cần thiết, distinct giúp tránh scan toàn bảng
        $ageLimits = Movie::where('status', 'upcoming')
            ->distinct()
            ->orderBy('age_limit')
            ->pluck('age_limit')
            ->toArray();

        return view(
            'customer.movies.upcoming',
            compact('movies', 'genres', 'ageLimits')
        );
    }

    public function search(Request $request)
    {
        $keyword = trim($request->keyword);

        // Eager load 'actors' là cần thiết vì search cũng lọc theo tên diễn viên
        $movies = Movie::with('actors')
            ->where(function ($query) use ($keyword) {
                $query->where('title', 'like', "%{$keyword}%")
                ->orWhereHas('actors', function ($actorQuery) use ($keyword) {
                    $actorQuery->where('name', 'like', "%{$keyword}%");
                });
            })
            ->paginate(20)
            ->withQueryString();

        return view(
            'customer.movies.search',
            compact('movies', 'keyword')
        );
    }

    public function show($id, Request $request)
    {
        // Eager load đầy đủ relations cần thiết cho trang chi tiết phim
        $movie = Movie::with(['genres', 'actors', 'reviews.user'])->findOrFail($id);

        $selectedDate = $request->input('date', today()->toDateString());

        $showtimes = $this->showtimeService->getCustomerShowtimesForMovie($movie->id);

        $showtimesGrouped = $showtimes->groupBy(function ($showtime) {
            return $showtime->show_date ? $showtime->show_date->format('Y-m-d') : '';
        })->map(function ($dateShowtimes) {
            return $dateShowtimes->groupBy(function ($showtime) {
                return $showtime->room && $showtime->room->cinema
                    ? $showtime->room->cinema->name
                    : 'Rạp chiếu';
            });
        });

        $availableDates = [];
        for ($i = 0; $i < 7; $i++) {
            $availableDates[] = today()->addDays($i)->toDateString();
        }

        return view(
            'customer.movies.show',
            compact('movie', 'showtimesGrouped', 'selectedDate', 'availableDates')
        );
    }
}
