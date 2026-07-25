<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Services\StaffBookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class StaffBookingController extends Controller
{
    /**
     * Khởi tạo Controller và tiêm phụ thuộc (Dependency Injection) StaffBookingService.
     *
     * @param StaffBookingService $staffBookingService
     */
    public function __construct(
        protected StaffBookingService $staffBookingService
    ) {}

    /**
     * Hiển thị danh sách vé đặt trong ngày dành cho Nhân viên rạp.
     * 
     * Các bước xử lý:
     * 1. Xác định Rạp làm việc của nhân viên qua $user->cinemas (Báo 403 nếu chưa phân công).
     * 2. Validate và chuẩn hóa tham số date đầu vào.
     * 3. Gọi Service lấy danh sách Booking đã Eager Load đầy đủ dữ liệu truyền ra View.
     *
     * @param Request $request
     * @return View|JsonResponse
     */
    public function index(Request $request): View|JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // ------------------------------------------------------------------
        // BƯỚC 1: XÁC ĐỊNH RẠP CỦA NHÂN VIÊN
        // ------------------------------------------------------------------
        $cinema = $user?->cinemas()->first();

        // Nếu nhân viên chưa được phân công làm việc tại rạp nào -> Báo lỗi 403 Forbidden
        if (!$cinema) {
            abort(Response::HTTP_FORBIDDEN, 'Bạn chưa được phân công làm việc tại rạp nào.');
        }

        // ------------------------------------------------------------------
        // BƯỚC 2: VALIDATE VÀ CHUẨN HÓA THAM SỐ ĐẦU VÀO
        // ------------------------------------------------------------------
        $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        // Mặc định lấy ngày hôm nay nếu không có tham số 'date' trên Request
        $date = $request->input('date', now()->toDateString());

        // ------------------------------------------------------------------
        // BƯỚC 3: GỌI SERVICE LẤY DANH SÁCH BOOKING ĐÃ TỐI ƯU TRUY VẤN
        // ------------------------------------------------------------------
        $bookings = $this->staffBookingService->getDailyBookingsByCinema($cinema->id, $date);

        // ------------------------------------------------------------------
        // BƯỚC 4: TRẢ VỀ VIEW HOẶC DỮ LIỆU JSON
        // ------------------------------------------------------------------
        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'cinema'  => [
                    'id'   => $cinema->id,
                    'name' => $cinema->name,
                ],
                'date'    => $date,
                'data'    => $bookings,
            ]);
        }

        $selectedDate = $date;

        return view('staff.bookings.index', compact('bookings', 'cinema', 'date', 'selectedDate'));
    }
}
