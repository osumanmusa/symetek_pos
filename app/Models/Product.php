<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Product extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'selling_price', 'cost_price', 'stock_quantity', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
    protected $fillable = [
        'sku',
        'name',
        'description',
        'category_id',
        'cost_price',
        'selling_price',
        'stock_quantity',
        'low_stock_threshold',
        'barcode',
        'unit',
        'weight',
        'images',
        'is_taxable',
        'is_active',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'weight' => 'decimal:2',
        'images' => 'array',
        'is_taxable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
        public function getUnitAttribute()
    {
        return $this->attributes['unit'] ?? 'pcs'; // Default to 'pcs' if not set
    }

    // Calculate profit margin
    public function getProfitMarginAttribute()
    {
        if ($this->cost_price == 0) return 100;
        return (($this->selling_price - $this->cost_price) / $this->cost_price) * 100;
    }

    // Check if product is low in stock
    // public function getIsLowStockAttribute()
    // {
    //     return $this->stock_quantity <= $this->low_stock_threshold;
    // }
// Add this to your existing Product model
public function saleItems(): HasMany
{
    return $this->hasMany(SaleItem::class);
}
/**
 * Relationship with purchase order items
 */
public function purchaseOrderItems()
{
    return $this->hasMany(PurchaseOrderItem::class);
}
public function sales()
{
    return $this->hasManyThrough(Sale::class, SaleItem::class);
}


// In Product.php model
/**
 * Relationship with inventory levels
 */
public function inventoryLevels()
{
    return $this->hasMany(InventoryLevel::class);
}

/**
 * Relationship with inventory transactions
 */
public function inventoryTransactions()
{
    return $this->hasMany(InventoryTransaction::class);
}

/**
 * Get total quantity across all warehouses
 */
public function getTotalQuantityAttribute()
{
    return $this->inventoryLevels()->sum('quantity_on_hand');
}

/**
 * Get available quantity across all warehouses
 */
public function getTotalAvailableAttribute()
{
    return $this->inventoryLevels()->sum(\DB::raw('quantity_on_hand - quantity_committed'));
}

/**
 * Get total committed quantity across all warehouses
 */
public function getTotalCommittedAttribute()
{
    return $this->inventoryLevels()->sum('quantity_committed');
}

/**
 * Get average cost across all warehouses
 */
public function getWeightedAverageCostAttribute()
{
    $totalValue = $this->inventoryLevels()->sum(\DB::raw('quantity_on_hand * average_cost'));
    $totalQuantity = $this->total_quantity;
    
    return $totalQuantity > 0 ? $totalValue / $totalQuantity : $this->cost_price;
}

/**
 * Get total inventory value across all warehouses
 */
public function getTotalInventoryValueAttribute()
{
    return $this->inventoryLevels()->sum('total_value');
}

/**
 * Check if product is in stock in any warehouse
 */
public function getIsInStockAttribute(): bool
{
    return $this->total_available > 0;
}

/**
 * Check if product is low in stock
 */
public function getIsLowStockAttribute(): bool
{
    return $this->total_available <= $this->low_stock_threshold;
}

/**
 * Get warehouses where this product is available
 */
public function availableWarehouses()
{
    return $this->inventoryLevels()
        ->whereRaw('quantity_on_hand - quantity_committed > 0')
        ->with('warehouse')
        ->get()
        ->pluck('warehouse');
}

/**
 * Get stock levels by warehouse
 */
public function getStockByWarehouse()
{
    return $this->inventoryLevels()
        ->with('warehouse')
        ->get()
        ->map(function ($level) {
            return [
                'warehouse' => $level->warehouse,
                'quantity_on_hand' => $level->quantity_on_hand,
                'quantity_committed' => $level->quantity_committed,
                'quantity_available' => $level->quantity_available,
                'average_cost' => $level->average_cost,
                'total_value' => $level->total_value,
                'is_low_stock' => $level->is_low_stock,
            ];
        });
}

/**
 * Update main stock quantity from inventory levels
 */
public function updateMainStockQuantity(): void
{
    $this->stock_quantity = $this->total_quantity;
    $this->save();
}
    // Generate SKU if not provided
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->sku)) {
                $product->sku = 'PROD-' . strtoupper(uniqid());
            }
        });
    }
}