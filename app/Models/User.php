<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'password',
        'avatar',
        'status',
        'points',
        'membership_tier',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'string',
        ];
    }

    /**
     * Relationships
     */

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'user_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    // Relationships with other clusters (defined here to ensure full scope)
    public function cinemas(): BelongsToMany
    {
        return $this->belongsToMany(Cinema::class, 'user_cinemas', 'user_id', 'cinema_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'user_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'user_id');
    }

    public function showtimeSeats(): HasMany
    {
        return $this->hasMany(ShowtimeSeat::class, 'user_id');
    }

    /**
     * Loyalty Relationships
     */
    public function pointsTransactions(): HasMany
    {
        return $this->hasMany(PointsTransaction::class, 'user_id');
    }

    public function userRewards(): HasMany
    {
        return $this->hasMany(UserReward::class, 'user_id');
    }

    public function wheelSpins(): HasMany
    {
        return $this->hasMany(WheelSpin::class, 'user_id');
    }

    /**
     * Helper: Kiểm tra và cập nhật hạng thành viên
     * Bạc: < 500 điểm, Vàng: 500 - 999 điểm, Kim Cương: >= 1000 điểm
     * (Có thể điều chỉnh theo logic của LoyaltyService)
     */
    public function checkAndUpdateTier(): void
    {
        $newTier = 'Bạc';
        if ($this->points >= 1000) {
            $newTier = 'Kim Cương';
        } elseif ($this->points >= 500) {
            $newTier = 'Vàng';
        }

        if ($this->membership_tier !== $newTier) {
            $this->membership_tier = $newTier;
            $this->save();
        }
    }
}
