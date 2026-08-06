<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WheelSpin extends Model
{
    use HasFactory;

    /**
     * Các trường có thể gán hàng loạt
     */
    protected $fillable = [
        'user_id',
        'prize_id',
    ];

    /**
     * Quan hệ với User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Quan hệ với LuckyWheelPrize
     */
    public function prize(): BelongsTo
    {
        return $this->belongsTo(LuckyWheelPrize::class, 'prize_id');
    }
}
