<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Services\StaffBookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class StaffComboBookingController extends Controller
{
    public function __construct(protected StaffBookingService $staffBookingService)
    {
    }

    public function index(Request $request): View
    {
        $user = Auth::user();
        $cinema = $user?->cinemas()->first();

        if (!$cinema) {
            abort(Response::HTTP_FORBIDDEN, 'Bạn chưa được phân công làm việc tại rạp nào.');
        }

        $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'booking_code' => ['nullable', 'string', 'max:100'],
            'print_status' => ['nullable', 'in:printed,not_printed'],
        ]);

        $date = $request->input('date', now()->toDateString());
        $selectedDate = $date;
        $filters = $request->only(['booking_code', 'print_status']);
        $bookings = $this->staffBookingService->getDailyComboBookingsByCinema($cinema->id, $date, $filters);

        return view('staff.combo-bookings.index', compact('bookings', 'cinema', 'date', 'selectedDate', 'filters'));
    }

    public function printReceipt(int $bookingId): View
    {
        $user = Auth::user();
        $cinema = $user?->cinemas()->first();

        if (!$cinema) {
            abort(Response::HTTP_FORBIDDEN, 'Bạn chưa được phân công làm việc tại rạp nào.');
        }

        $booking = $this->staffBookingService->getComboBookingForStaff($bookingId, $cinema->id);

        if (is_null($booking->printed_at)) {
            $booking->update(['printed_at' => now()]);
        }

        return view('staff.combo-bookings.print-receipt', compact('booking', 'cinema'));
    }

    public function markPrinted(int $bookingId): JsonResponse
    {
        $user = Auth::user();
        $cinema = $user?->cinemas()->first();

        if (!$cinema) {
            abort(Response::HTTP_FORBIDDEN, 'Bạn chưa được phân công làm việc tại rạp nào.');
        }

        $booking = $this->staffBookingService->getComboBookingForStaff($bookingId, $cinema->id);

        if (is_null($booking->printed_at)) {
            $booking->update(['printed_at' => now()]);
        }

        return response()->json(['status' => 'success']);
    }
}
