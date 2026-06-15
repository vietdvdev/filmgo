<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BookingDetail extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'booking_details';

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
        'booking_id',
        'showtime_seat_id',
        'price',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'booking_id' => 'integer',
        'showtime_seat_id' => 'integer',
        'price' => 'integer',
    ];

    /**
     * Relationships
     */

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function showtimeSeat(): BelongsTo
    {
        return $this->belongsTo(ShowtimeSeat::class, 'showtime_seat_id');
    }

    public function ticket(): HasOne
    {
        return $this->hasOne(Ticket::class, 'booking_detail_id');
    }
}
