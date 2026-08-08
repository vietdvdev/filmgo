<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignEmployeeToSeatRequest;
use App\Models\ShowtimeSeat;
use Illuminate\Http\JsonResponse;

class AssignEmployeeController extends Controller
{
    /**
     * Gán nhân viên cho ghế đôi có số ghế chẵn.
     * Dữ liệu đầu vào đã được validate qua AssignEmployeeToSeatRequest.
     *
     * @param AssignEmployeeToSeatRequest $request
     * @return JsonResponse
     */
    public function assign(AssignEmployeeToSeatRequest $request): JsonResponse
    {
        $showtimeSeat = ShowtimeSeat::find($request->showtime_seat_id);
        
        // Edge Case: Kiểm tra xung đột lịch làm việc
        // Nhân viên không được phục vụ ghế khác trong cùng khung giờ suất chiếu
        // Sẽ được chi tiết hơn ở Commit 8, ở đây tạm thời cập nhật
        
        $showtimeSeat->update([
            'employee_id' => $request->employee_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Gán nhân viên phục vụ ghế đôi thành công.',
            'data' => $showtimeSeat->load('employee')
        ]);
    }
}
