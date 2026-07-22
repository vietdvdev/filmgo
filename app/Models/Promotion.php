<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Promotion extends Model
{
    use HasFactory, SoftDeletes;

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
        'code',
        'apply_to',
        'discount_type',
        'discount_value',
        'max_discount_amount',
        'min_order_amount',
        'max_uses_per_user',
        'start_date',
        'end_date',
        'usage_limit',
        'used_count',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'discount_value'      => 'integer',
        'max_discount_amount' => 'integer',
        'min_order_amount'    => 'integer',
        'max_uses_per_user'   => 'integer',
        'usage_limit'         => 'integer',
        'used_count'          => 'integer',
        'start_date'          => 'datetime',
        'end_date'            => 'datetime',
        'status'              => 'string',
    ];

    /**
     * Relationships
     */

    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class, 'booking_promotions', 'promotion_id', 'booking_id');
    }
}
