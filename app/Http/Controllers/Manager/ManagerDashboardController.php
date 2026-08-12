<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Showtime;
use App\Services\ManagerDashboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ManagerDashboardController extends Controller
{
    public function __construct(protected ManagerDashboardService $service) {}

    private function getCinemaId(): ?int
    {
        return Auth::user()->cinemas()->first()?->id;
    }

    public function index()
    {
        $user   = Auth::user();
        $cinema = $user->cinemas()->first();

        if (!$cinema) {
            return redirect()->route('manager.no-cinema');
        }

        $roomCount          = Room::where('cinema_id', $cinema->id)->count();
        $staffCount         = $cinema->users()->whereHas('roles', fn($q) => $q->where('name', 'staff'))->count();
        $showtimeTodayCount = Showtime::whereHas('room', fn($q) => $q->where('cinema_id', $cinema->id))
            ->whereDate('show_date', today())
            ->where('status', '!=', 'cancelled')
            ->count();

        return view('manager.dashboard', compact('cinema', 'roomCount', 'staffCount', 'showtimeTodayCount'));
    }

    public function kpis(Request $request)
    {
        $cinemaId = $this->getCinemaId();
        if (!$cinemaId) return response()->json(['error' => 'No cinema'], 403);

        $startDate = Carbon::parse($request->query('start_date', today()))->startOfDay();
        $endDate   = Carbon::parse($request->query('end_date', today()))->endOfDay();

        $diffInDays    = $startDate->diffInDays($endDate) + 1;
        $prevStartDate = $startDate->copy()->subDays($diffInDays)->startOfDay();
        $prevEndDate   = $startDate->copy()->subDay()->endOfDay();

        $cacheKey = "manager_{$cinemaId}_kpis_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}";

        $data = Cache::remember($cacheKey, 300, fn() =>
            $this->service->getKpiData($cinemaId, $startDate, $endDate, $prevStartDate, $prevEndDate)
        );

        return response()->json($data);
    }

    public function chartsRevenue(Request $request)
    {
        $cinemaId = $this->getCinemaId();
        if (!$cinemaId) return response()->json(['error' => 'No cinema'], 403);

        $startDate = Carbon::parse($request->query('start_date', now()->subDays(6)))->startOfDay();
        $endDate   = Carbon::parse($request->query('end_date', now()))->endOfDay();

        $cacheKey = "manager_{$cinemaId}_revenue_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}";

        $result = Cache::remember($cacheKey, 300, fn() =>
            $this->service->getRevenueChartData($cinemaId, $startDate, $endDate)
        );

        return response()->json([
            'labels'         => $result['labels'],
            'ticket_revenue' => $result['ticketData'],
            'combo_revenue'  => $result['comboData'],
        ]);
    }

    public function chartsTopMovies(Request $request)
    {
        $cinemaId = $this->getCinemaId();
        if (!$cinemaId) return response()->json(['error' => 'No cinema'], 403);

        $startDate = Carbon::parse($request->query('start_date', now()->subDays(29)))->startOfDay();
        $endDate   = Carbon::parse($request->query('end_date', now()))->endOfDay();

        $cacheKey = "manager_{$cinemaId}_top_movies_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}";

        $data = Cache::remember($cacheKey, 300, fn() =>
            $this->service->getTopMoviesData($cinemaId, $startDate, $endDate)
        );

        return response()->json($data);
    }

    public function opsTodayShowtimes()
    {
        $cinemaId = $this->getCinemaId();
        if (!$cinemaId) return response()->json([]);

        return response()->json($this->service->getTodayShowtimes($cinemaId));
    }

    public function recentBookings(Request $request)
    {
        $cinemaId = $this->getCinemaId();
        if (!$cinemaId) return response()->json([]);

        $startDate = Carbon::parse($request->query('start_date', today()))->startOfDay();
        $endDate   = Carbon::parse($request->query('end_date', today()))->endOfDay();

        return response()->json($this->service->getRecentBookings($cinemaId, $startDate, $endDate));
    }

    public function recentComboBookings(Request $request)
    {
        $cinemaId = $this->getCinemaId();
        if (!$cinemaId) return response()->json([]);

        $startDate = Carbon::parse($request->query('start_date', today()))->startOfDay();
        $endDate   = Carbon::parse($request->query('end_date', today()))->endOfDay();

        return response()->json($this->service->getRecentComboBookings($cinemaId, $startDate, $endDate));
    }
}
