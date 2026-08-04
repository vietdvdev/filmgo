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

    public function formats(): BelongsToMany
    {
        return $this->belongsToMany(Format::class, 'movie_formats', 'movie_id', 'format_id');
    }

    // Showtime is defined in Showtime cluster
    public function showtimes(): HasMany
    {
        return $this->hasMany(Showtime::class, 'movie_id');
    }

    protected static function booted(): void
    {
        static::retrieved(function (Movie $movie) {
            if ($movie->status === 'upcoming' && $movie->release_date !== null && $movie->release_date->lte(now()->startOfDay())) {
                $movie->status = 'showing';
                $movie->saveQuietly();
            }
        });
    }

    public function getPosterUrlAttribute(): ?string
    {
        if (!$this->poster) {
            return null;
        }

        if (str_starts_with($this->poster, 'http://') || str_starts_with($this->poster, 'https://')) {
            return $this->poster;
        }

        if (str_starts_with($this->poster, 'storage/')) {
            return asset($this->poster);
        }

        return asset('storage/' . ltrim($this->poster, '/'));
    }

    public function getTrailerEmbedUrlAttribute(): ?string
    {
        if (!$this->trailer_url) {
            return null;
        }

        $url = $this->trailer_url;
        if (preg_match('/youtu\.be\/([A-Za-z0-9_-]+)/', $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }
        if (preg_match('/youtube\.com\/watch\?v=([A-Za-z0-9_-]+)/', $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }
        if (preg_match('/youtube\.com\/embed\/([A-Za-z0-9_-]+)/', $url, $matches)) {
            return $url;
        }

        return null;
    }

    public function getCurrentStatusAttribute(): string
    {
        if ($this->status === 'upcoming' && $this->release_date !== null && $this->release_date->lte(now()->startOfDay())) {
            return 'showing';
        }

        return $this->status;
    }
}
