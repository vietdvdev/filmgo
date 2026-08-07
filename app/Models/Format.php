<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Format extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'formats';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'surcharge_price',
        'status',
    ];

    protected $casts = [
        'surcharge_price' => 'integer',
        'status'          => 'string',
    ];

    /**
     * Quan hệ N-N: Định dạng thuộc về nhiều Phim (thông qua bảng movie_formats)
     */
    public function movies(): BelongsToMany
    {
        return $this->belongsToMany(Movie::class, 'movie_formats', 'format_id', 'movie_id');
    }

    /**
     * Quan hệ 1-N: Định dạng có nhiều Suất chiếu
     */
    public function showtimes(): HasMany
    {
        return $this->hasMany(Showtime::class, 'format_id');
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(\App\Models\Room::class, 'format_id');
    }
}
