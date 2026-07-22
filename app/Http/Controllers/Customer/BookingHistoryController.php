<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingHistoryController extends Controller
{
    public function index()
    {
        $bookings = Booking::with([
                'showtime.movie',
                'showtime.room.cinema',
                'bookingDetails.showtimeSeat.seat',
                'payments',
            ])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(8);

        return view('customer.bookings.history', compact('bookings'));
    }

    public function show($id)
    {
        $booking = Booking::with([
                'showtime.movie',
                'showtime.room.cinema',
                'bookingDetails.showtimeSeat.seat.seatType',
                'combos',
                'promotions',
                'payments',
            ])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('customer.bookings.detail', compact('booking'));
    }
}
