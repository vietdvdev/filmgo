<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShowtimeSeat extends Model
{
    use HasFactory;

    /**
     * Indicated if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'showtime_seats';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'showtime_id',
        'seat_id',
        'user_id',
        'employee_id',
        'status',
        'price',
        'locked_at',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'showtime_id' => 'integer',
        'seat_id'     => 'integer',
        'user_id'     => 'integer',
        'employee_id' => 'integer',
        'status'      => 'string',
        'price'       => 'integer',
        'locked_at'   => 'datetime',
        'expires_at'  => 'datetime',
    ];

    // ────────────────────────────────────────────────────────────────
    // Query Scopes — tái sử dụng điều kiện filter thường gặp
    // ────────────────────────────────────────────────────────────────

    /**
     * Lọc các ghế đã hết thời gian giữ (holding/locked quá expires_at).
     * Dùng: ShowtimeSeat::expired()->get()
     * Hỗ trợ câu lệnh giải phóng ghế hết hạn trong Scheduler.
     */
    public function scopeExpired(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query
            ->whereIn('status', ['holding', 'locked'])
            ->where('expires_at', '<', now());
    }

    /**
     * Lọc các ghế đang trống (có thể chọn được).
     * Dùng: ShowtimeSeat::available()->get()
     */
    public function scopeAvailable(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'available');
    }

    /**
     * Lọc các ghế đã được đặt xong (thanh toán thành công).
     * Dùng: ShowtimeSeat::booked()->get()
     */
    public function scopeBooked(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'booked');
    }

    // ────────────────────────────────────────────────────────────────
    // Relationships
    // ────────────────────────────────────────────────────────────────

    public function showtime(): BelongsTo
    {
        return $this->belongsTo(Showtime::class, 'showtime_id')->withTrashed();
    }

    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class, 'seat_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Nhân viên được gán cho ghế đôi.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    // BookingDetail is defined in Booking cluster
    public function bookingDetails(): HasMany
    {
        return $this->hasMany(BookingDetail::class, 'showtime_seat_id');
    }

    /**
     * Kiểm tra suất ghế này có phải là ghế đôi số chẵn không (thông qua Model Seat).
     *
     * @return bool
     */
    public function isEvenCoupleSeat(): bool
    {
        return $this->seat ? $this->seat->isEvenCoupleSeat() : false;
    }
}
