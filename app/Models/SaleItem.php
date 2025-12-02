<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
        'cost_price'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
    ];

    /**
     * Relationship with sale
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Relationship with product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate profit for this item
     */
    public function getProfitAttribute(): float
    {
        if (!$this->cost_price) {
            return 0;
        }
        return $this->total_price - ($this->cost_price * $this->quantity);
    }

    /**
     * Get profit margin percentage
     */
    public function getProfitMarginAttribute(): float
    {
        if ($this->total_price == 0) {
            return 0;
        }
        return ($this->profit / $this->total_price) * 100;
    }
}