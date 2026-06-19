<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Showtime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ManagerRoomController extends Controller
{
    private function getCinemaId()
    {
        return Auth::user()->cinemas()->first()->id;
    }

    public function index(Request $request)
    {
        // Mock dữ liệu giả lập cho danh sách phòng chiếu
        $mockRooms = collect([
            (object)[
                'id' => 1,
                'room_name' => 'Phòng Chiếu 01',
                'capacity' => 120,
                'room_type' => '2D',
                'status' => 'active'
            ],
            (object)[
                'id' => 2,
                'room_name' => 'Phòng Chiếu 02',
                'capacity' => 150,
                'room_type' => '3D',
                'status' => 'active'
            ],
            (object)[
                'id' => 3,
                'room_name' => 'Phòng Chiếu 03',
                'capacity' => 200,
                'room_type' => 'IMAX',
                'status' => 'maintenance'
            ],
            (object)[
                'id' => 4,
                'room_name' => 'Phòng Chiếu 04',
                'capacity' => 80,
                'room_type' => '4DX',
                'status' => 'inactive'
            ]
        ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $mockRooms = $mockRooms->filter(fn($item) => stripos($item->room_name, $search) !== false);
        }

        $currentPage = 1;
        $perPage = 10;
        $rooms = new \Illuminate\Pagination\LengthAwarePaginator(
            $mockRooms->forPage($currentPage, $perPage),
            $mockRooms->count(),
            $perPage,
            $currentPage,
            ['path' => route('manager.rooms.index')]
        );

        return view('manager.rooms.index', compact('rooms'));
    }

    public function create()
    {
        return view('manager.rooms.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'room_name' => 'required|string|max:100',
            'capacity'  => 'required|integer|min:1|max:500',
            'room_type' => 'required|in:2D,3D,IMAX,4DX',
        ], [
            'room_name.required' => 'Tên phòng chiếu không được để trống.',
            'capacity.required'  => 'Sức chứa không được để trống.',
            'room_type.required' => 'Loại phòng chiếu không được để trống.',
        ]);

        // Mock hành động lưu thành công
        return redirect()->route('manager.rooms.index')->with('success', 'Thêm phòng chiếu mới thành công (dữ liệu giả lập)!');
    }

    public function edit($id)
    {
        $room = (object)[
            'id' => $id,
            'room_name' => 'Phòng Chiếu 01',
            'capacity' => 120,
            'room_type' => '2D',
            'status' => 'active'
        ];

        return view('manager.rooms.edit', compact('room'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'room_name' => 'required|string|max:100',
            'capacity'  => 'required|integer|min:1|max:500',
            'room_type' => 'required|in:2D,3D,IMAX,4DX',
            'status'    => 'required|in:active,maintenance,inactive',
        ], [
            'room_name.required' => 'Tên phòng chiếu không được để trống.',
            'capacity.required'  => 'Sức chứa không được để trống.',
            'room_type.required' => 'Loại phòng chiếu không được để trống.',
        ]);

        // Mock hành động cập nhật thành công
        return redirect()->route('manager.rooms.index')->with('success', 'Cập nhật phòng chiếu thành công (dữ liệu giả lập)!');
    }

    public function destroy($id)
    {
        // Mock hành động xóa thành công
        return redirect()->route('manager.rooms.index')->with('success', 'Đã xóa phòng chiếu thành công (dữ liệu giả lập).');
    }

    public function seatMap($roomId)
    {
        $room = (object)[
            'id' => $roomId,
            'room_name' => 'Phòng Chiếu ' . sprintf("%02d", $roomId),
            'capacity' => 120,
            'room_type' => '2D'
        ];
        
        // Mock sơ đồ ghế hàng A-J, cột 1-12
        $seats = collect();
        foreach (range('A', 'J') as $row) {
            $rowSeats = collect();
            for ($num = 1; $num <= 12; $num++) {
                $rowSeats->push((object)[
                    'id' => $row . $num,
                    'row_name' => $row,
                    'seat_number' => $num,
                    'type' => ($row == 'I' || $row == 'J') ? 'vip' : 'standard',
                    'status' => 'active'
                ]);
            }
            $seats->put($row, $rowSeats);
        }

        return view('manager.rooms.seat_map', compact('room', 'seats'));
    }
}
