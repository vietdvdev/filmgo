<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Genre;
use App\Models\Showtime;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MovieController extends Controller
{
    public function showing(Request $request)
    {
        $query = Movie::where('status', 'showing');

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
        $ageLimits = Movie::where('status', 'showing')->distinct()->pluck('age_limit')->toArray();

        return view(
            'customer.movies.showing',
            compact('movies', 'genres', 'ageLimits')
        );
    }

    public function upcoming(Request $request)
    {
        $query = Movie::where('status', 'upcoming');

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
        $ageLimits = Movie::where('status', 'upcoming')->distinct()->pluck('age_limit')->toArray();

        return view(
            'customer.movies.upcoming',
            compact('movies', 'genres', 'ageLimits')
        );
    }

    public function search(Request $request)
    {
        $keyword = trim($request->keyword);

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
        $movie = Movie::with(['genres', 'actors', 'reviews.user'])->findOrFail($id);

        $selectedDate = $request->input('date', today()->toDateString());

        $showtimes = Showtime::where('movie_id', $movie->id)
            ->whereDate('show_date', $selectedDate)
            ->whereIn('status', ['upcoming', 'showing'])
            ->with(['room', 'room.cinema'])
            ->get();

        $showtimesGrouped = $showtimes->groupBy(function ($showtime) {
            return $showtime->room->cinema->name;
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
