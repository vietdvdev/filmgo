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
        'format_id',
        'status',
    ];

    protected $casts = [
        'cinema_id' => 'integer',
        'room_name' => 'string',
        'capacity'  => 'integer',
        'room_type' => 'string',
        'format_id' => 'integer',
        'status'    => 'string',
    ];

    /**
     * Relationships
     */

    public function cinema(): BelongsTo
    {
        return $this->belongsTo(Cinema::class, 'cinema_id');
    }

    public function format(): BelongsTo
    {
        return $this->belongsTo(Format::class, 'format_id');
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
