<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomSeatRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
            'seat_row'     => 'required|string|max:5',
            'start_number' => 'required|integer|min:1',
            'end_number'   => 'required|integer|min:1|gte:start_number',
            'seat_type_id' => 'required|integer|exists:seat_types,id',
            'couple_seats_count' => [
                'nullable',
                'integer',
                'min:0',
                function ($attribute, $value, $fail) {
                    // Modulo logic: So luong ghe doi (Sweetbox/Couple) bat buoc phai la so chan (% 2 !== 0 thi fail)
                    if (!is_null($value) && (int)$value % 2 !== 0) {
                        $fail('Số lượng ghế đôi bắt buộc phải là số chẵn.');
                    }
                },
            ],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'seat_row.required'       => 'Hàng ghế không được để trống.',
            'start_number.required'   => 'Số bắt đầu không được để trống.',
            'end_number.required'     => 'Số kết thúc không được để trống.',
            'end_number.gte'          => 'Số kết thúc phải lớn hơn hoặc bằng số bắt đầu.',
            'seat_type_id.required'   => 'Vui lòng chọn loại ghế.',
            'seat_type_id.exists'     => 'Loại ghế không hợp lệ.',
            'couple_seats_count.min'  => 'Số lượng ghế đôi phải lớn hơn hoặc bằng 0.',
        ];
    }
}
