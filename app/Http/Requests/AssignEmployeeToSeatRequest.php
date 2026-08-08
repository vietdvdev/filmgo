<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ShowtimeSeat;
use App\Models\User;

class AssignEmployeeToSeatRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Giả sử chỉ quản lý (admin/manager) mới có quyền gán
        // Nếu cần có thể kiểm tra auth()->user()->hasRole('admin')
        return true; 
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'showtime_seat_id' => ['required', 'integer', 'exists:showtime_seats,id'],
            'employee_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    /**
     * Add custom logic after validation.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $showtimeSeat = ShowtimeSeat::with(['seat.seatType', 'showtime'])->find($this->showtime_seat_id);
            if ($showtimeSeat && !$showtimeSeat->isEvenCoupleSeat()) {
                $validator->errors()->add('showtime_seat_id', 'Ghế này không phải là ghế đôi (Sweetbox) hoặc không có số ghế chẵn.');
            }

            $employeeId = $this->employee_id;
            if ($employeeId) {
                // Sử dụng scope đã định nghĩa để kiểm tra nhân viên hợp lệ
                $isValidEmployee = User::availableForCoupleSeat()->where('id', $employeeId)->exists();
                if (!$isValidEmployee) {
                    $validator->errors()->add('employee_id', 'Nhân viên không hợp lệ hoặc không có quyền phục vụ ghế đôi.');
                }

                // Edge case: Kiểm tra trùng lịch
                // Nhân viên đã được gán cho 1 ghế khác trong CÙNG SUẤT CHIẾU này
                if ($showtimeSeat) {
                    $isAlreadyAssignedInSameShowtime = ShowtimeSeat::where('showtime_id', $showtimeSeat->showtime_id)
                        ->where('employee_id', $employeeId)
                        ->where('id', '!=', $showtimeSeat->id)
                        ->exists();

                    if ($isAlreadyAssignedInSameShowtime) {
                        $validator->errors()->add('employee_id', 'Nhân viên này đã được phân công phục vụ một ghế đôi khác trong cùng suất chiếu.');
                    }
                }
            }
        });
    }

    /**
     * Custom error messages (Vietnamese).
     */
    public function messages(): array
    {
        return [
            'showtime_seat_id.required' => 'Vui lòng chọn suất chiếu ghế.',
            'showtime_seat_id.exists' => 'Suất chiếu ghế không tồn tại.',
            'employee_id.required' => 'Vui lòng chọn nhân viên.',
            'employee_id.exists' => 'Nhân viên không tồn tại.',
        ];
    }
}
