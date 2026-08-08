<?php

namespace App\Http\Requests;

use App\Models\SeatType;
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
                    if (!is_null($value) && (int)$value > 0 && (int)$value % 2 !== 0) {
                        $fail('Số lượng ghế đôi bắt buộc phải là số chẵn. Không thể tạo số lượng ghế đôi lẻ.');
                    }
                },
            ],
        ];
    }

    /**
     * Additional validation after basic rules.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $seatTypeId = (int)$this->input('seat_type_id');
            $startNum   = (int)$this->input('start_number');
            $endNum     = (int)$this->input('end_number');
            $coupleCnt  = $this->input('couple_seats_count');

            if ($seatTypeId > 0) {
                $seatType = SeatType::find($seatTypeId);
                if ($seatType) {
                    $nameLower = mb_strtolower($seatType->name);
                    $isCoupleType = str_contains($nameLower, 'sweetbox')
                        || str_contains($nameLower, 'couple')
                        || str_contains($nameLower, 'đôi')
                        || str_contains($nameLower, 'doi');

                    if ($isCoupleType) {
                        $totalCount = ($startNum > 0 && $endNum >= $startNum) ? ($endNum - $startNum + 1) : 0;
                        $effectiveCount = (!is_null($coupleCnt) && (int)$coupleCnt > 0) ? (int)$coupleCnt : $totalCount;

                        if ($effectiveCount > 0 && $effectiveCount % 2 !== 0) {
                            $validator->errors()->add(
                                'couple_seats_count',
                                'Loại ghế đôi (Sweetbox) chỉ được tạo với số lượng chẵn. Vui lòng chọn số lượng chẵn (2, 4, 6...).'
                            );
                        }
                    }
                }
            }
        });
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

