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
        'staff_id',
        'showtime_id',
        'booking_code',
        'subtotal',
        'promotion_id',
        'total_amount',
        'discount_amount',
        'final_total',
        'payment_status',
        'booking_status',
        'channel',
        'expired_at',
    ];

    protected $casts = [
        'user_id'         => 'integer',
        'staff_id'        => 'integer',
        'showtime_id'     => 'integer',
        'subtotal'        => 'integer',
        'promotion_id'    => 'integer',
        'total_amount'    => 'integer',
        'discount_amount' => 'integer',
        'final_total'     => 'integer',
        'payment_status'  => 'string',
        'booking_status'  => 'string',
        'channel'         => 'string',
        'expired_at'      => 'datetime',
    ];


    // ────────────────────────────────────────────────────────────────
    // Query Scopes — tái sử dụng điều kiện filter thường gặp
    // ────────────────────────────────────────────────────────────────

    /**
     * Lọc đơn đặt vé đã thanh toán thành công.
     * Dùng: Booking::paid()->get()
     */
    public function scopePaid(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Lọc đơn đặt vé đang chờ thanh toán.
     * Dùng: Booking::pending()->get()
     */
    public function scopePending(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('payment_status', 'pending');
    }

    /**
     * Lọc đơn đặt vé đã xác nhận (booking_status = confirmed).
     * Dùng: Booking::confirmed()->get()
     */
    public function scopeConfirmed(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('booking_status', 'confirmed');
    }

    /**
     * Lọc đơn đặt vé theo kênh (online, counter).
     * Dùng: Booking::byChannel('counter')->get()
     *
     * @param  string  $channel  'online' | 'counter'
     */
    public function scopeByChannel(\Illuminate\Database\Eloquent\Builder $query, string $channel): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('channel', $channel);
    }

    // ────────────────────────────────────────────────────────────────
    // Relationships
    // ────────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function showtime(): BelongsTo
    {
        return $this->belongsTo(Showtime::class, 'showtime_id')->withTrashed();
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
