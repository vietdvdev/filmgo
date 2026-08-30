<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Services\ManagerBookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManagerBookingController extends Controller
{
    public function __construct(
        protected ManagerBookingService $bookingService
    ) {}

    /**
     * Lấy ID rạp được phân công của Manager.
     */
    private function getCinema()
    {
        return Auth::user()->cinemas()->first();
    }

    /**
     * Hiển thị danh sách vé và đơn hàng của rạp.
     */
    public function index(Request $request)
    {
        $cinema = $this->getCinema();
        if (!$cinema) {
            return redirect()->route('manager.no-cinema');
        }

        $filters = $request->only([
            'search',
            'movie_id',
            'room_id',
            'payment_status',
            'booking_type',
            'channel',
            'print_status',
            'show_date_from',
            'show_date_to',
            'created_from',
            'created_to',
            'sort',
        ]);

        $bookings = $this->bookingService->getList($cinema->id, $filters);
        $stats    = $this->bookingService->getSummaryStats($cinema->id, $filters);
        $movies   = $this->bookingService->getMoviesInCinema($cinema->id);
        $rooms    = $this->bookingService->getRoomsInCinema($cinema->id);

        return view('manager.bookings.index', compact('bookings', 'stats', 'movies', 'rooms', 'cinema', 'filters'));
    }

    /**
     * Hiển thị chi tiết đơn hàng.
     */
    public function show(int $id)
    {
        $cinema = $this->getCinema();
        if (!$cinema) {
            return redirect()->route('manager.no-cinema');
        }

        $booking = $this->bookingService->getDetail($id, $cinema->id);

        return view('manager.bookings.show', compact('booking', 'cinema'));
    }
}
