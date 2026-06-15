<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Showtime extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'movie_id',
        'room_id',
        'show_date',
        'start_time',
        'end_time',
        'base_price',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'movie_id' => 'integer',
        'room_id' => 'integer',
        'show_date' => 'date',
        'base_price' => 'integer',
        'status' => 'string',
    ];

    /**
     * Relationships
     */

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class, 'movie_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function showtimeSeats(): HasMany
    {
        return $this->hasMany(ShowtimeSeat::class, 'showtime_id');
    }

    // Booking is defined in Booking cluster
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'showtime_id');
    }
}
