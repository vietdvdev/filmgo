<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reward extends Model
{
    use HasFactory;

    /**
     * Các trường có thể gán hàng loạt
     */
    protected $fillable = [
        'name',
        'description',
        'points_required',
        'quantity',
    ];

    /**
     * Quan hệ với UserReward: Một phần thưởng có thể được đổi nhiều lần bởi nhiều người dùng
     */
    public function userRewards(): HasMany
    {
        return $this->hasMany(UserReward::class);
    }
}
