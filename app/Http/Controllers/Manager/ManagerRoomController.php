<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManagerRoomController extends Controller
{
    private function getCinemaIds(): array
    {
        return Auth::user()->cinemas()->pluck('cinemas.id')->toArray();
    }

    public function index(Request $request)
    {
        $cinemaIds = $this->getCinemaIds();

        $query = Room::whereIn('cinema_id', $cinemaIds)
            ->with('cinema');

        if ($request->filled('search')) {
            $query->where('room_name', 'like', '%' . $request->search . '%');
        }

        $rooms = $query->orderBy('cinema_id')->orderBy('room_name')->paginate(10);

        return view('manager.rooms.index', compact('rooms'));
    }

    public function create()
    {
        $cinemas = Auth::user()->cinemas()->get();
        return view('manager.rooms.create', compact('cinemas'));
    }

    public function store(Request $request)
    {
        $cinemaIds = $this->getCinemaIds();

        $request->validate([
            'cinema_id' => 'required|integer|in:' . implode(',', $cinemaIds),
            'room_name' => 'required|string|max:100',
            'capacity'  => 'required|integer|min:1|max:500',
            'room_type' => 'required|in:2D,3D,IMAX,4DX',
        ], [
            'cinema_id.required' => 'Vui lòng chọn rạp chiếu.',
            'cinema_id.in'       => 'Rạp chiếu không hợp lệ.',
            'room_name.required' => 'Tên phòng chiếu không được để trống.',
            'capacity.required'  => 'Sức chứa không được để trống.',
            'room_type.required' => 'Loại phòng chiếu không được để trống.',
        ]);

        Room::create([
            'cinema_id' => $request->cinema_id,
            'room_name' => $request->room_name,
            'capacity'  => $request->capacity,
            'room_type' => $request->room_type,
            'status'    => 'active',
        ]);

        return redirect()->route('manager.rooms.index')->with('success', 'Thêm phòng chiếu mới thành công!');
    }

    public function edit($id)
    {
        $cinemaIds = $this->getCinemaIds();
        $room = Room::whereIn('cinema_id', $cinemaIds)->findOrFail($id);
        $cinemas = Auth::user()->cinemas()->get();

        return view('manager.rooms.edit', compact('room', 'cinemas'));
    }

    public function update(Request $request, $id)
    {
        $cinemaIds = $this->getCinemaIds();
        $room = Room::whereIn('cinema_id', $cinemaIds)->findOrFail($id);

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

        $room->update($request->only(['room_name', 'capacity', 'room_type', 'status']));

        return redirect()->route('manager.rooms.index')->with('success', 'Cập nhật phòng chiếu thành công!');
    }

    public function destroy($id)
    {
        $cinemaIds = $this->getCinemaIds();
        $room = Room::whereIn('cinema_id', $cinemaIds)->findOrFail($id);
        $room->delete();

        return redirect()->route('manager.rooms.index')->with('success', 'Đã xóa phòng chiếu thành công.');
    }

    public function seatMap($roomId)
    {
        $cinemaIds = $this->getCinemaIds();
        $room = Room::whereIn('cinema_id', $cinemaIds)->with('seats')->findOrFail($roomId);

        $seats = $room->seats->groupBy('row_name');

        return view('manager.rooms.seat_map', compact('room', 'seats'));
    }
}
