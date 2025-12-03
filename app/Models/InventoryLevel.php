<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryLevel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'quantity_on_hand',
        'quantity_committed',
        'average_cost',
        'last_count_date',
    ];

    protected $casts = [
        'quantity_on_hand' => 'decimal:2',
        'quantity_committed' => 'decimal:2',
        'average_cost' => 'decimal:2',
        'last_count_date' => 'date',
    ];

    protected $appends = [
        'quantity_available',
        'total_value',
    ];

    /**
     * Relationship with product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relationship with warehouse
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get available quantity (on hand - committed)
     */
    public function getQuantityAvailableAttribute(): float
    {
        return $this->quantity_on_hand - $this->quantity_committed;
    }

    /**
     * Get total inventory value
     */
    public function getTotalValueAttribute(): float
    {
        return $this->quantity_on_hand * $this->average_cost;
    }

    /**
     * Check if stock is low
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->quantity_available <= ($this->product->low_stock_threshold ?? 10);
    }

    /**
     * Get stock status
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->quantity_available <= 0) {
            return 'out_of_stock';
        } elseif ($this->is_low_stock) {
            return 'low_stock';
        } else {
            return 'in_stock';
        }
    }

    /**
     * Get stock status color
     */
    public function getStockStatusColorAttribute(): string
    {
        return match($this->stock_status) {
            'out_of_stock' => 'red',
            'low_stock' => 'yellow',
            'in_stock' => 'green',
            default => 'gray',
        };
    }

    /**
     * Get stock status text
     */
    public function getStockStatusTextAttribute(): string
    {
        return match($this->stock_status) {
            'out_of_stock' => 'Out of Stock',
            'low_stock' => 'Low Stock',
            'in_stock' => 'In Stock',
            default => 'Unknown',
        };
    }

    /**
     * Update average cost using weighted average method
     */
    public function updateAverageCost($newQuantity, $newUnitCost): void
    {
        if ($newQuantity <= 0) {
            return;
        }

        $currentTotalValue = $this->quantity_on_hand * $this->average_cost;
        $newTotalValue = $newQuantity * $newUnitCost;
        $totalQuantity = $this->quantity_on_hand + $newQuantity;
        
        if ($totalQuantity > 0) {
            $this->average_cost = ($currentTotalValue + $newTotalValue) / $totalQuantity;
        }
    }

    /**
     * Increase quantity on hand
     */
    public function increaseOnHand($quantity, $unitCost = null): void
    {
        if ($unitCost !== null) {
            $this->updateAverageCost($quantity, $unitCost);
        }
        
        $this->quantity_on_hand += $quantity;
        $this->save();
    }

    /**
     * Decrease quantity on hand
     */
    public function decreaseOnHand($quantity): void
    {
        $this->quantity_on_hand = max(0, $this->quantity_on_hand - $quantity);
        $this->save();
    }

    /**
     * Increase committed quantity
     */
    public function increaseCommitted($quantity): void
    {
        $this->quantity_committed += $quantity;
        $this->save();
    }

    /**
     * Decrease committed quantity
     */
    public function decreaseCommitted($quantity): void
    {
        $this->quantity_committed = max(0, $this->quantity_committed - $quantity);
        $this->save();
    }

    /**
     * Commit available quantity (move from on-hand to committed)
     */
    public function commit($quantity): bool
    {
        if ($this->quantity_available >= $quantity) {
            $this->quantity_committed += $quantity;
            $this->save();
            return true;
        }
        
        return false;
    }

    /**
     * Release committed quantity (move from committed to on-hand)
     */
    public function release($quantity): void
    {
        $this->quantity_committed = max(0, $this->quantity_committed - $quantity);
        $this->save();
    }

    /**
     * Scope for low stock items
     */
    public function scopeLowStock($query, $threshold = null)
    {
        return $query->whereHas('product', function ($query) use ($threshold) {
            $query->where('stock_quantity', '<=', $threshold ?? \DB::raw('products.low_stock_threshold'));
        });
    }

    /**
     * Scope for out of stock items
     */
    public function scopeOutOfStock($query)
    {
        return $query->whereRaw('quantity_on_hand - quantity_committed <= 0');
    }

    
}