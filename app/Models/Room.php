<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cinema_id',
        'room_name',
        'capacity',
        'room_type',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cinema_id' => 'integer',
        'room_name' => 'string',
        'capacity' => 'integer',
        'room_type' => 'string',
        'status' => 'string',
    ];

    /**
     * Relationships
     */

    public function cinema(): BelongsTo
    {
        return $this->belongsTo(Cinema::class, 'cinema_id');
    }

    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class, 'room_id');
    }

    // Showtime is defined in Showtime cluster
    public function showtimes(): HasMany
    {
        return $this->hasMany(Showtime::class, 'room_id');
    }
}
