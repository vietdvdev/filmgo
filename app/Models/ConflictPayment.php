<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConflictPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'booking_code',
        'transaction_code',
        'amount',
        'payment_method',
        'reason',
        'status',
    ];

    protected $casts = [
        'booking_id' => 'integer',
        'amount' => 'integer',
        'status' => 'string',
    ];

    /**
     * Relationship with Booking
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}
