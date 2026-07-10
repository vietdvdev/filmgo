<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BookingAdminService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(private BookingAdminService $service) {}

    public function index(Request $request)
    {
        $filters  = $request->only([
            'search', 'cinema_id', 'movie_id', 'payment_status',
            'show_date_from', 'show_date_to', 'created_from', 'created_to', 'sort',
        ]);

        $bookings = $this->service->getList($filters);
        $cinemas  = $this->service->getCinemas();
        $movies   = $this->service->getMovies();

        return view('admin.bookings.index', compact('bookings', 'cinemas', 'movies', 'filters'));
    }

    public function show(int $id)
    {
        $booking = $this->service->getDetail($id);
        return view('admin.bookings.show', compact('booking'));
    }
}
