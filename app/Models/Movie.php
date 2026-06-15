<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Movie extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'poster',
        'trailer_url',
        'duration',
        'release_date',
        'director',
        'country',
        'age_limit',
        'description',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'duration' => 'integer',
        'release_date' => 'date',
        'age_limit' => 'string',
        'status' => 'string',
    ];

    /**
     * Relationships
     */

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'movie_genres', 'movie_id', 'genre_id');
    }

    public function actors(): BelongsToMany
    {
        return $this->belongsToMany(Actor::class, 'movie_actors', 'movie_id', 'actor_id')
                    ->withPivot('role_name');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'movie_id');
    }

    // Showtime is defined in Showtime cluster
    public function showtimes(): HasMany
    {
        return $this->hasMany(Showtime::class, 'movie_id');
    }
}
