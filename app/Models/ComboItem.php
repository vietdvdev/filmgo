<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ComboItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'combo_items';

    protected $fillable = [
        'name',
        'type',
        'unit',
        'price',
        'status',
    ];

    protected $casts = [
        'price'  => 'integer',
        'status' => 'string',
    ];

    /**
     * Danh sách các combo có chứa thành phần này.
     */
    public function combos(): BelongsToMany
    {
        return $this->belongsToMany(Combo::class, 'combo_combo_item', 'combo_item_id', 'combo_id')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }
}
