<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointsTransaction extends Model
{
    use HasFactory;

    /**
     * Các trường có thể gán hàng loạt (Mass assignable)
     */
    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'description',
    ];

    /**
     * Quan hệ với User: Một giao dịch điểm thuộc về một người dùng
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
