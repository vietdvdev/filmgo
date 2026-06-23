<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Showtime;
use App\Services\RoomSeatSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

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

    /**
     * Hiển thị trang Thiết Lập Sơ Đồ Ghế tương tác (Vue component).
     * Thay thế hoàn toàn hàm mock cũ.
     */
    public function seatMap(int $roomId, RoomSeatSyncService $syncService): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        try {
            // Xác thực quyền: phòng phải thuộc rạp của Manager đang đăng nhập
            $room = $syncService->authorizeAndFetchRoom($roomId, Auth::id());

            // Load thông tin rạp và các ghế hiện có của phòng
            $room->load(['cinema', 'seats']);

            // Lấy toàn bộ loại ghế để truyền vào Vue component qua data attribute
            $seatTypes = \App\Models\SeatType::orderBy('id')->get(['id', 'name', 'surcharge_price']);

            return view('manager.rooms.seat_map_vue', compact('room', 'seatTypes'));

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('manager.rooms.index')
                ->with('error', collect($e->errors())->flatten()->first());
        } catch (\Exception $e) {
            return redirect()->route('manager.rooms.index')
                ->with('error', 'Không thể truy cập sơ đồ ghế: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // syncSeats — Đồng bộ toàn bộ sơ đồ ghế của một phòng chiếu
    // =========================================================================
    /**
     * Nhận danh sách ghế từ client (JSON), xác thực quyền, xóa ghế cũ và
     * chèn lại toàn bộ trong một DB Transaction.
     *
     * Request body (JSON):
     * {
     *   "seats": [
     *     { "seat_row": "A", "seat_number": 1, "seat_type_id": 1, "status": "active" },
     *     ...
     *   ]
     * }
     *
     * Success Response (200):
     * {
     *   "success": true,
     *   "message": "Đồng bộ sơ đồ ghế thành công.",
     *   "data": { "room_id": 1, "room_name": "...", "seat_count": 120 }
     * }
     *
     * Error Response (422 / 403 / 500):
     * {
     *   "success": false,
     *   "message": "...",
     *   "errors": { ... }   // chỉ xuất hiện khi là lỗi validation
     * }
     *
     * @param  Request              $request
     * @param  int                  $roomId   Route parameter
     * @param  RoomSeatSyncService  $syncService  Auto-injected bởi Laravel Service Container
     * @return JsonResponse
     */
    public function syncSeats(
        Request $request,
        int $roomId,
        RoomSeatSyncService $syncService
    ): JsonResponse {
        // ── Bước 1: Validate cấu trúc Request ────────────────────────────────
        // Dùng dot-notation để validate từng phần tử trong mảng seats[*].
        $request->validate(
            [
                'seats'                  => ['required', 'array', 'max:500'],
                'seats.*.seat_row'       => ['required', 'string', 'max:10', 'regex:/^[A-Z]+$/i'],
                'seats.*.seat_number'    => ['required', 'integer', 'min:1', 'max:99'],
                'seats.*.seat_type_id'   => ['required', 'integer', 'exists:seat_types,id'],
                'seats.*.status'         => ['required', 'in:active,maintenance'],
            ],
            [
                'seats.required'                 => 'Danh sách ghế không được để trống.',
                'seats.array'                    => 'Dữ liệu ghế phải là một mảng.',
                'seats.max'                      => 'Mỗi phòng tối đa 500 ghế.',
                'seats.*.seat_row.required'      => 'Hàng ghế không được để trống.',
                'seats.*.seat_row.regex'         => 'Hàng ghế chỉ được chứa chữ cái (A-Z).',
                'seats.*.seat_number.required'   => 'Số thứ tự ghế không được để trống.',
                'seats.*.seat_number.min'        => 'Số thứ tự ghế phải >= 1.',
                'seats.*.seat_number.max'        => 'Số thứ tự ghế phải <= 99.',
                'seats.*.seat_type_id.required'  => 'Loại ghế không được để trống.',
                'seats.*.seat_type_id.exists'    => 'Loại ghế không hợp lệ.',
                'seats.*.status.required'        => 'Trạng thái ghế không được để trống.',
                'seats.*.status.in'              => 'Trạng thái ghế chỉ được là active hoặc maintenance.',
            ]
        );

        $seats = $request->input('seats', []);

        try {
            // ── Bước 2: Xác thực quyền sở hữu phòng theo user_cinemas ───────
            // Chỉ Manager đang đăng nhập mới được thao tác với phòng thuộc rạp
            // được phân công cho họ. Tham chiếu bảng user_cinemas.
            $room = $syncService->authorizeAndFetchRoom($roomId, Auth::id());

            // ── Bước 3: Kiểm tra ghế đang có đặt vé/holding ─────────────────
            // Không cho phép đồng bộ khi còn giao dịch chưa hoàn tất để đảm
            // bảo tính toàn vẹn dữ liệu đặt vé.
            $syncService->guardAgainstActiveBookings($roomId);

            // ── Bước 4: Kiểm tra trùng lặp trong chính payload ───────────────
            // Tránh vi phạm UNIQUE(room_id, seat_row, seat_number) khi insert.
            $syncService->guardAgainstDuplicatesInPayload($seats);

            // ── Bước 5: Thực thi đồng bộ trong DB::transaction() ─────────────
            // Xóa ghế cũ → insert hàng loạt → cập nhật capacity.
            $result = $syncService->sync($room, $seats);

            return response()->json([
                'success' => true,
                'message' => "Đồng bộ sơ đồ ghế thành công. Đã tạo {$result['seat_count']} ghế.",
                'data'    => $result,
            ], 200);

        } catch (ValidationException $e) {
            // Lỗi từ validate() hoặc do guardAgainst*() ném ra
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ. Vui lòng kiểm tra lại.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            // Lỗi không xác định (DB, network, v.v.) — log để debug
            \Log::error('[syncSeats] Lỗi không xác định', [
                'room_id'   => $roomId,
                'user_id'   => Auth::id(),
                'exception' => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi hệ thống. Vui lòng thử lại sau.',
            ], 500);
        }
    }
}

