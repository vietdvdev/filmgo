<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LuckyWheelPrize extends Model
{
    use HasFactory;

    /**
     * Các trường có thể gán hàng loạt
     */
    protected $fillable = [
        'name',
        'type',
        'value',
        'probability',
        'quantity',
    ];

    /**
     * Quan hệ với WheelSpin: Một giải thưởng có thể có nhiều lượt quay trúng
     */
    public function wheelSpins(): HasMany
    {
        return $this->hasMany(WheelSpin::class, 'prize_id');
    }
}
