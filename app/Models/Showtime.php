<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Showtime extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'movie_id',
        'format_id',
        'room_id',
        'show_date',
        'start_time',
        'end_time',
        'base_price',
        'status',
        'is_auto_generated',
        'publish_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'movie_id'         => 'integer',
        'format_id'        => 'integer',
        'room_id'          => 'integer',
        'show_date'        => 'date',
        'base_price'       => 'integer',
        'status'           => 'string',
        'is_auto_generated' => 'boolean',
        'publish_at'       => 'datetime',
    ];

    // ────────────────────────────────────────────────────────────────
    // Query Scopes — tái sử dụng điều kiện filter thường gặp
    // ────────────────────────────────────────────────────────────────

    /**
     * Lọc các suất chiếu đang mở bán (trạng thái = 'active').
     * Dùng: Showtime::active()->get()
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Lọc các suất chiếu sắp mở bán (trạng thái = 'upcoming').
     * Dùng: Showtime::upcoming()->get()
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('status', 'upcoming');
    }

    /**
     * Lọc suất chiếu không bị hủy.
     * Dùng: Showtime::notCancelled()->get()
     */
    public function scopeNotCancelled(Builder $query): Builder
    {
        return $query->where('status', '!=', 'cancelled');
    }

    /**
     * Lọc suất chiếu theo ngày chiếu cụ thể.
     * Dùng: Showtime::forDate('2026-07-27')->get()
     *
     * @param  string  $date  Định dạng Y-m-d
     */
    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('show_date', $date);
    }

    /**
     * Lọc suất chiếu từ hôm nay trở đi.
     * Dùng: Showtime::fromToday()->get()
     */
    public function scopeFromToday(Builder $query): Builder
    {
        return $query->whereDate('show_date', '>=', today()->toDateString());
    }

    // ────────────────────────────────────────────────────────────────
    // Relationships
    // ────────────────────────────────────────────────────────────────

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class, 'movie_id')->withTrashed();
    }

    public function format(): BelongsTo
    {
        return $this->belongsTo(Format::class, 'format_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function showtimeSeats(): HasMany
    {
        return $this->hasMany(ShowtimeSeat::class, 'showtime_id');
    }

    // Booking is defined in Booking cluster
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'showtime_id');
    }

    /**
     * Kiểm tra xem suất chiếu đã hết hạn / kết thúc hay chưa.
     */
    public function isExpired(): bool
    {
        if (!$this->show_date) {
            return false;
        }

        $dateStr = $this->show_date instanceof \DateTimeInterface 
            ? $this->show_date->format('Y-m-d') 
            : substr((string)$this->show_date, 0, 10);

        if (!empty($this->end_time)) {
            $endDateTime = \Carbon\Carbon::parse($dateStr . ' ' . $this->end_time);
            return $endDateTime->isPast();
        }

        if (!empty($this->start_time)) {
            $duration = $this->movie?->duration ?? 120;
            $endDateTime = \Carbon\Carbon::parse($dateStr . ' ' . $this->start_time)->addMinutes($duration);
            return $endDateTime->isPast();
        }

        return \Carbon\Carbon::parse($dateStr)->endOfDay()->isPast();
    }
}
