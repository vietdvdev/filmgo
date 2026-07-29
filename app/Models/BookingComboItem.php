<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingComboItem extends Model
{
    use HasFactory;

    protected $table = 'booking_combo_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_id',
        'combo_item_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'booking_id'    => 'integer',
        'combo_item_id' => 'integer',
        'quantity'      => 'integer',
        'unit_price'    => 'integer',
        'subtotal'      => 'integer',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function comboItem(): BelongsTo
    {
        return $this->belongsTo(ComboItem::class, 'combo_item_id');
    }
}
