<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seat extends Model
{
    use HasFactory;

    /**
     * Indicated if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'room_id',
        'seat_type_id',
        'seat_row',
        'seat_number',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'room_id' => 'integer',
        'seat_type_id' => 'integer',
        'seat_row' => 'string',
        'seat_number' => 'integer',
        'status' => 'string',
    ];

    /**
     * Relationships
     */

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function seatType(): BelongsTo
    {
        return $this->belongsTo(SeatType::class, 'seat_type_id');
    }

    // ShowtimeSeat is defined in Showtime cluster
    public function showtimeSeats(): HasMany
    {
        return $this->hasMany(ShowtimeSeat::class, 'seat_id');
    }

    /**
     * Kiểm tra xem ghế hiện tại có phải là ghế đôi (Sweetbox) và có số ghế chẵn hay không.
     *
     * @return bool
     */
    public function isEvenCoupleSeat(): bool
    {
        // Kiểm tra số ghế chẵn và loại ghế là 'Sweetbox'
        // Chú ý: Cần load quan hệ seatType nếu chưa load để tránh N+1 query trong vòng lặp.
        return $this->seat_number % 2 === 0 && 
               $this->seatType && 
               $this->seatType->name === 'Sweetbox';
    }
}
