<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Booking extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'showtime_id',
        'booking_code',
        'subtotal',
        'promotion_id',
        'total_amount',
        'discount_amount',
        'final_total',
        'payment_status',
        'booking_status',
        'expired_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'user_id'        => 'integer',
        'showtime_id'    => 'integer',
        'subtotal'       => 'integer',
        'promotion_id'   => 'integer',
        'total_amount'   => 'integer',
        'discount_amount' => 'integer',
        'final_total'    => 'integer',
        'payment_status' => 'string',
        'booking_status' => 'string',
        'expired_at'     => 'datetime',
    ];

    /**
     * Relationships
     */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function showtime(): BelongsTo
    {
        return $this->belongsTo(Showtime::class, 'showtime_id');
    }

    /**
     * Mã khuyến mãi đã được áp dụng (snapshot FK).
     */
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class, 'promotion_id');
    }

    public function bookingDetails(): HasMany
    {
        return $this->hasMany(BookingDetail::class, 'booking_id');
    }

    public function combos(): BelongsToMany
    {
        return $this->belongsToMany(Combo::class, 'booking_combos', 'booking_id', 'combo_id')
                    ->withPivot(['quantity', 'subtotal']);
    }

    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class, 'booking_promotions', 'booking_id', 'promotion_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'booking_id');
    }
}
