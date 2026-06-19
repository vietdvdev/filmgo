<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Showtime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ManagerShowtimeController extends Controller
{
    private function getCinemaId()
    {
        return Auth::user()->cinemas()->first()->id;
    }

    public function index(Request $request)
    {
        // Mock dữ liệu giả lập danh sách suất chiếu
        $showDate = $request->filled('date') ? Carbon::parse($request->date) : Carbon::today();
        
        $mockShowtimes = collect([
            (object)[
                'id' => 1,
                'movie' => (object)['title' => 'Lật Mặt 7: Một Điều Ước'],
                'room' => (object)['room_name' => 'Phòng Chiếu 01'],
                'show_date' => $showDate,
                'start_time' => '10:00:00',
                'end_time' => '12:15:00',
                'base_price' => 80000,
                'status' => 'active'
            ],
            (object)[
                'id' => 2,
                'movie' => (object)['title' => 'Doraemon: Bản Giao Hưởng Địa Cầu'],
                'room' => (object)['room_name' => 'Phòng Chiếu 02'],
                'show_date' => $showDate,
                'start_time' => '13:30:00',
                'end_time' => '15:30:00',
                'base_price' => 80000,
                'status' => 'active'
            ],
            (object)[
                'id' => 3,
                'movie' => (object)['title' => 'Haikyu!!: Trận Chiến Bãi Phế Thải'],
                'room' => (object)['room_name' => 'Phòng Chiếu 03'],
                'show_date' => $showDate,
                'start_time' => '16:00:00',
                'end_time' => '17:45:00',
                'base_price' => 90000,
                'status' => 'canceled'
            ]
        ]);

        $currentPage = 1;
        $perPage = 15;
        $showtimes = new \Illuminate\Pagination\LengthAwarePaginator(
            $mockShowtimes->forPage($currentPage, $perPage),
            $mockShowtimes->count(),
            $perPage,
            $currentPage,
            ['path' => route('manager.showtimes.index')]
        );

        return view('manager.showtimes.index', compact('showtimes'));
    }

    public function create()
    {
        // Mock dữ liệu giả lập cho phim và phòng chiếu
        $movies = collect([
            (object)['id' => 1, 'title' => 'Lật Mặt 7: Một Điều Ước', 'duration' => 120],
            (object)['id' => 2, 'title' => 'Doraemon: Bản Giao Hưởng Địa Cầu', 'duration' => 105],
            (object)['id' => 3, 'title' => 'Haikyu!!: Trận Chiến Bãi Phế Thải', 'duration' => 95]
        ]);
        $rooms = collect([
            (object)['id' => 1, 'room_name' => 'Phòng Chiếu 01', 'room_type' => '2D', 'capacity' => 120],
            (object)['id' => 2, 'room_name' => 'Phòng Chiếu 02', 'room_type' => '3D', 'capacity' => 150],
            (object)['id' => 3, 'room_name' => 'Phòng Chiếu 03', 'room_type' => 'IMAX', 'capacity' => 200]
        ]);

        return view('manager.showtimes.create', compact('movies', 'rooms'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'movie_id'   => 'required',
            'room_id'    => 'required',
            'show_date'  => 'required|date',
            'start_time' => 'required',
            'base_price' => 'required|numeric|min:0',
        ], [
            'movie_id.required'   => 'Vui lòng chọn phim.',
            'room_id.required'    => 'Vui lòng chọn phòng chiếu.',
            'show_date.required'  => 'Vui lòng chọn ngày chiếu.',
            'start_time.required' => 'Vui lòng chọn giờ bắt đầu.',
            'base_price.required' => 'Vui lòng nhập giá vé cơ bản.',
        ]);

        // Mock hành động tạo suất chiếu thành công
        return redirect()->route('manager.showtimes.index')->with('success', 'Tạo suất chiếu mới thành công (dữ liệu giả lập)!');
    }

    public function cancelShowtime($id)
    {
        // Mock hành động hủy suất chiếu thành công
        return redirect()->route('manager.showtimes.index')->with('success', 'Đã hủy suất chiếu thành công (dữ liệu giả lập).');
    }
}
