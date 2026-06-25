<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Services\ManagerSeatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use InvalidArgumentException;

class ManagerSeatController extends Controller
{
    protected ManagerSeatService $seatService;

    public function __construct(ManagerSeatService $seatService)
    {
        $this->seatService = $seatService;
    }

    /**
     * Lấy ID rạp được phân công cho manager hiện tại.
     *
     * @return int
     */
    private function getCinemaId(): int
    {
    private function getCinemaId(): int
    {
        $user = Auth::user();
        if ($user->roles()->where('name', 'admin')->exists()) {
            $cinema = $user->cinemas()->first() ?? \App\Models\Cinema::first();
        } else {
            $cinema = $user->cinemas()->first();
        }

        if (!$cinema) {
            abort(403, 'Tài khoản chưa được phân công quản lý rạp nào.');
        }

        return $cinema->id;
    }

    /**
     * Hiển thị giao diện sơ đồ ghế của phòng chiếu.
     */
    public function index($roomId)
    {
        try {
            $cinemaId = $this->getCinemaId();
            $data = $this->seatService->getRoomSeatsAndTypes((int)$roomId, $cinemaId);

            return view('manager.rooms.seats.index', $data);
        } catch (AuthorizationException $e) {
            return redirect()->route('manager.rooms.index')->with('error', $e->getMessage());
        } catch (NotFoundHttpException $e) {
            return redirect()->route('manager.rooms.index')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->route('manager.rooms.index')->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Tạo hàng loạt ghế cho một hàng.
     */
    public function bulkStore(Request $request, $roomId)
    {
        $request->validate([
            'seat_row'     => 'required|string|max:5',
            'start_number' => 'required|integer|min:1',
            'end_number'   => 'required|integer|min:1|gte:start_number',
            'seat_type_id' => 'required|integer|exists:seat_types,id',
        ], [
            'seat_row.required'       => 'Hàng ghế không được để trống.',
            'start_number.required'   => 'Số bắt đầu không được để trống.',
            'end_number.required'     => 'Số kết thúc không được để trống.',
            'end_number.gte'          => 'Số kết thúc phải lớn hơn hoặc bằng số bắt đầu.',
            'seat_type_id.required'   => 'Vui lòng chọn loại ghế.',
            'seat_type_id.exists'     => 'Loại ghế không hợp lệ.',
        ]);

        try {
            $cinemaId     = $this->getCinemaId();
            $createdCount = $this->seatService->bulkStoreSeats((int)$roomId, $cinemaId, $request->all());

            return redirect()->back()->with('success', "Đã tạo thành công {$createdCount} ghế mới.");
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            return redirect()->back()->withInput()->with('error', 'Trùng lặp dữ liệu: Một số ghế trong khoảng này đã tồn tại.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Cập nhật thông tin một ghế (Loại ghế hoặc trạng thái).
     */
    public function update(Request $request, $roomId, $seatId)
    {
        // Hỗ trợ cả JSON body (AJAX) và form data
        $isJson   = $request->isJson() || $request->expectsJson();
        $payload  = $isJson ? $request->json()->all() : $request->all();

        try {
            $cinemaId = $this->getCinemaId();
            $seat     = $this->seatService->updateSeat(
                (int)$roomId,
                (int)$seatId,
                $cinemaId,
                array_intersect_key($payload, array_flip(['seat_type_id', 'status']))
            );

            if ($isJson) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật ghế thành công.',
                    'seat'    => [
                        'id'           => $seat->id,
                        'seat_type_id' => (int)$seat->seat_type_id,
                        'status'       => $seat->status,
                    ]
                ]);
            }

            return redirect()->back()->with('success', 'Cập nhật ghế thành công.');
        } catch (\Exception $e) {
            $statusCode = $e instanceof NotFoundHttpException ? 404 : 400;
            if ($isJson) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], $statusCode);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Xóa một ghế vật lý cụ thể.
     */
    public function destroy(Request $request, $roomId, $seatId)
    {
        $isJson = $request->isJson() || $request->expectsJson();

        try {
            $cinemaId = $this->getCinemaId();
            $this->seatService->deleteSeat((int)$roomId, (int)$seatId, $cinemaId);

            if ($isJson) {
                return response()->json([
                    'success' => true,
                    'message' => 'Xóa ghế thành công.'
                ]);
            }

            return redirect()->back()->with('success', 'Xóa ghế thành công.');
        } catch (\Exception $e) {
            $statusCode = $e instanceof NotFoundHttpException ? 404 : 400;
            if ($isJson) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], $statusCode);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
