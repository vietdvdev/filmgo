<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Cinema;
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
        'cinema_id',
        'booking_code',
        'subtotal',
        'promotion_id',
        'total_amount',
        'discount_amount',
        'final_total',
        'payment_status',
        'booking_status',
        'channel',
        'booking_type',
        'expired_at',
        'printed_at',
    ];

    protected $casts = [
        'user_id'         => 'integer',
        'staff_id'        => 'integer',
        'showtime_id'     => 'integer',
        'cinema_id'       => 'integer',
        'subtotal'        => 'integer',
        'promotion_id'    => 'integer',
        'total_amount'    => 'integer',
        'discount_amount' => 'integer',
        'final_total'     => 'integer',
        'payment_status'  => 'string',
        'booking_status'  => 'string',
        'channel'         => 'string',
        'booking_type'    => 'string',
        'expired_at'      => 'datetime',
        'printed_at'      => 'datetime',
    ];


    // ────────────────────────────────────────────────────────────────
    // Query Scopes — tái sử dụng điều kiện filter thường gặp
    // ────────────────────────────────────────────────────────────────

    /**
     * Lọc đơn đặt vé đã thanh toán thành công.
     * Dùng: Booking::paid()->get()
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Lọc đơn đặt vé đang chờ thanh toán.
     * Dùng: Booking::pending()->get()
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('payment_status', 'pending');
    }

    /**
     * Lọc đơn đặt vé đã xác nhận (booking_status = confirmed).
     * Dùng: Booking::confirmed()->get()
     */
    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('booking_status', 'confirmed');
    }

    /**
     * Lọc đơn combo-only (không có vé xem phim).
     * Dùng: Booking::comboOnly()->get()
     */
    public function scopeComboOnly(Builder $query): Builder
    {
        return $query->where('booking_type', 'combo_only');
    }

    /**
     * Lọc đơn đặt vé thông thường.
     * Dùng: Booking::ticketOnly()->get()
     */
    public function scopeTicketOnly(Builder $query): Builder
    {
        return $query->where('booking_type', 'ticket');
    }

    /**
     * Lọc lịch sử đặt vé hợp lệ của khách hàng.
     * Chỉ hiển thị các đơn đã thanh toán thành công.
     * Loại trừ toàn bộ các đơn đã bị hủy, thất bại hoặc chờ thanh toán bị bỏ dở.
     *
     * Dùng: Booking::customerHistory()->get()
     */
    public function scopeCustomerHistory(Builder $query): Builder
    {
        return $query->where('payment_status', 'paid')
            ->where('booking_status', '!=', 'cancelled');
    }

    /**
     * Loại trừ các đơn hàng đã hết thời gian giữ ghế chờ thanh toán hoặc đã bị hủy/thất bại.
     *
     * Giữ lại đơn nếu:
     *   - Không bị hủy / thất bại
     *   - VÀ THỎA MỘT TRONG:
     *       - expired_at IS NULL       → đơn không có hạn (ví dụ: đặt tại quầy)
     *       - expired_at > now()       → vẫn còn trong thời gian giữ ghế
     *       - payment_status = 'paid'  → đã thanh toán thành công
     *
     * Dùng: Booking::excludeExpired()->get()
     */
    public function scopeExcludeExpired(Builder $query): Builder
    {
        return $query->where('booking_status', '!=', 'cancelled')
            ->where('payment_status', '!=', 'failed')
            ->where(function (Builder $q) {
                $q->whereNull('expired_at')
                  ->orWhere('expired_at', '>', now())
                  ->orWhere('payment_status', 'paid');
            });
    }

    /**
     * Lọc đơn đặt vé theo kênh (online, counter).
     * Dùng: Booking::byChannel('counter')->get()
     *
     * @param  string  $channel  'online' | 'counter'
     */
    public function scopeByChannel(Builder $query, string $channel): Builder
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

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
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

    /**
     * Danh sách đồ ăn/uống lẻ từng món trong đơn (dành cho đơn combo_only tại POS).
     */
    public function comboItems(): HasMany
    {
        return $this->hasMany(BookingComboItem::class, 'booking_id');
    }

    public function cinema(): BelongsTo
    {
        return $this->belongsTo(Cinema::class, 'cinema_id');
    }
}
