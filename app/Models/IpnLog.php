<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpnLog extends Model
{
    use HasFactory;

    /**
     * Tên bảng ghi log callback/IPN.
     *
     * @var string
     */
    protected $table = 'ipn_logs';

    /**
     * Các cột được phép mass assign.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'provider',
        'event_type',
        'booking_code',
        'booking_id',
        'transaction_code',
        'gateway_reference',
        'signature_status',
        'processing_status',
        'response_code',
        'payload',
        'signature',
        'message',
    ];

    /**
     * Ép kiểu payload sang array để dễ xử lý.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'payload' => 'array',
    ];

    /**
     * Liên kết với đơn đặt vé nếu có.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}
