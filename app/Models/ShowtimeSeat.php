<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShowtimeSeat extends Model
{
    use HasFactory;

    /**
     * Indicated if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'showtime_seats';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'showtime_id',
        'seat_id',
        'user_id',
        'status',
        'price',
        'locked_at',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'showtime_id' => 'integer',
        'seat_id'     => 'integer',
        'user_id'     => 'integer',
        'status'      => 'string',
        'price'       => 'integer',
        'locked_at'   => 'datetime',
        'expires_at'  => 'datetime',
    ];

    /**
     * Relationships
     */

    public function showtime(): BelongsTo
    {
        return $this->belongsTo(Showtime::class, 'showtime_id');
    }

    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class, 'seat_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // BookingDetail is defined in Booking cluster
    public function bookingDetails(): HasMany
    {
        return $this->hasMany(BookingDetail::class, 'showtime_seat_id');
    }
}
