<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Actor extends Model
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
        'name',
        'avatar',
        'biography',
        'date_of_birth',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'name' => 'string',
        'avatar' => 'string',
        'biography' => 'string',
        'date_of_birth' => 'date',
    ];

    /**
     * Relationships
     */

    public function movies(): BelongsToMany
    {
        return $this->belongsToMany(Movie::class, 'movie_actors', 'actor_id', 'movie_id')
                    ->withPivot('role_name');
    }
}
