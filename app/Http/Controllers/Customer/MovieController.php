<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function showing()
    {
        $movies = Movie::where('status', 'showing')
            ->latest()
            ->paginate(20);

        return view(
            'customer.movies.showing',
            compact('movies')
        );
    }

    public function upcoming()
    {
        $movies = Movie::where('status', 'upcoming')
            ->orderBy('release_date')
            ->paginate(20);

        return view(
            'customer.movies.upcoming',
            compact('movies')
        );
    }

    public function search(Request $request)
{
    $keyword = trim($request->keyword);

    $movies = Movie::with('actors')

        ->where(function ($query) use ($keyword) {

            // Tìm theo tên phim
            $query->where(
                'title',
                'like',
                "%{$keyword}%"
            )

            // Tìm theo tên diễn viên
            ->orWhereHas('actors', function ($actorQuery) use ($keyword) {

                $actorQuery->where(
                    'name',
                    'like',
                    "%{$keyword}%"
                );
            });
        })

        ->paginate(20)
        ->withQueryString();

    return view(
        'customer.movies.search',
        compact(
            'movies',
            'keyword'
        )
    );
}
}
