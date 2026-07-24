<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShowtimeRequest extends FormRequest
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
            'movie_id'   => 'required|exists:movies,id',
            'format_id'  => 'required|exists:formats,id',
            'cinema_id'  => 'required|exists:cinemas,id',
            'room_id'    => 'required|exists:rooms,id',
            'show_date'  => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'base_price' => 'required|integer|min:0',
            'publish_at' => 'nullable|date_format:Y-m-d\TH:i',
        ];
    }

    /**
     * Custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'movie_id.required'   => 'Vui lòng chọn phim.',
            'movie_id.exists'     => 'Phim được chọn không tồn tại.',
            'format_id.required'  => 'Vui lòng chọn định dạng chiếu.',
            'format_id.exists'    => 'Định dạng chiếu được chọn không tồn tại.',
            'cinema_id.required'  => 'Vui lòng chọn rạp chiếu.',
            'cinema_id.exists'    => 'Rạp chiếu được chọn không tồn tại.',
            'room_id.required'    => 'Vui lòng chọn phòng chiếu.',
            'room_id.exists'      => 'Phòng chiếu được chọn không tồn tại.',
            'show_date.required'  => 'Vui lòng chọn ngày chiếu.',
            'show_date.date'      => 'Ngày chiếu không hợp lệ.',
            'show_date.after_or_equal' => 'Ngày chiếu phải từ hôm nay trở đi.',
            'start_time.required' => 'Vui lòng chọn giờ bắt đầu.',
            'start_time.date_format' => 'Giờ bắt đầu không đúng định dạng H:i (VD: 14:30).',
            'base_price.required' => 'Vui lòng nhập giá vé cơ bản.',
            'base_price.integer'  => 'Giá vé phải là một số nguyên.',
            'base_price.min'      => 'Giá vé cơ bản không được âm.',
        ];
    }

    /**
     * Configure the validator instance to perform cross-field verification.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $cinemaId = $this->input('cinema_id');
            $roomId   = $this->input('room_id');
            $movieId  = $this->input('movie_id');
            $formatId = $this->input('format_id');
            $showDate  = $this->input('show_date');
            $startTime = $this->input('start_time');

            // 1. Kiểm tra phòng chiếu thuộc rạp đã chọn
            if ($cinemaId && $roomId) {
                $roomExists = \App\Models\Room::where('id', $roomId)
                    ->where('cinema_id', $cinemaId)
                    ->exists();

                if (!$roomExists) {
                    $validator->errors()->add('room_id', 'Phòng chiếu được chọn không thuộc rạp chiếu đã chọn.');
                }
            }

            // 2. Kiểm tra ràng buộc Định dạng chiếu và Tiêu chuẩn Phòng chiếu
            if ($movieId && $formatId && $roomId) {
                $formatService = app(\App\Services\FormatService::class);
                $formatErrors  = $formatService->validateShowtimeFormatAndRoom((int)$movieId, (int)$formatId, (int)$roomId);

                foreach ($formatErrors as $field => $message) {
                    $validator->errors()->add($field, $message);
                }
            }

            // 3. Kiểm tra thời gian suất chiếu không ở trong quá khứ
            if ($showDate && $startTime && !$validator->errors()->has('show_date') && !$validator->errors()->has('start_time')) {
                $startDateTime = \Carbon\Carbon::createFromFormat(
                    'Y-m-d H:i',
                    $showDate . ' ' . $startTime,
                    config('app.timezone')
                );

                if ($startDateTime->isPast()) {
                    $validator->errors()->add(
                        'start_time',
                        'Thời gian bắt đầu suất chiếu không được ở trong quá khứ. Vui lòng chọn thời gian từ hiện tại trở đi.'
                    );
                }
            }
        });
    }
}
