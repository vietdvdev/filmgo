<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowtimeSeatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'showtime_id' => $this->showtime_id,
            'seat_id' => $this->seat_id,
            'status' => $this->status,
            'price' => $this->price,
            'is_even_couple_seat' => $this->isEvenCoupleSeat(),
            'employee' => $this->employee ? [
                'id' => $this->employee->id,
                'full_name' => $this->employee->full_name,
                'email' => $this->employee->email,
            ] : null,
        ];
    }
}
