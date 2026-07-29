<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Combo extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'combo_name',
        'image',
        'price',
        'description',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'integer',
        'status' => 'string',
    ];

    /**
     * Relationships
     */

    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class, 'booking_combos', 'combo_id', 'booking_id')
                    ->withPivot(['quantity', 'subtotal']);
    }

    /**
     * Thành phần chi tiết của combo (Bắp lớn, Bắp nhỏ, Nước lớn, Nước nhỏ...)
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(ComboItem::class, 'combo_combo_item', 'combo_id', 'combo_item_id')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    /**
     * Chuỗi tổng hợp danh sách các thành phần (VD: 1x Bắp lớn, 2x Nước lớn)
     */
    public function getItemsSummaryAttribute(): string
    {
        if ($this->relationLoaded('items') && $this->items->isNotEmpty()) {
            return $this->items->map(function ($item) {
                return $item->pivot->quantity . 'x ' . $item->name;
            })->implode(', ');
        }
        return '';
    }
}
