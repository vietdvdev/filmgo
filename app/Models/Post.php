<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'thumbnail',
        'content',
        'status',
        'published_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'author_id' => 'integer',
        'title' => 'string',
        'slug' => 'string',
        'thumbnail' => 'string',
        'content' => 'string',
        'status' => 'string',
        'published_at' => 'datetime',
    ];

    /**
     * Relationships
     */

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
