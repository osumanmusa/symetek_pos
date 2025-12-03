<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'location',
        'contact_person',
        'phone',
        'email',
        'address',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

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
     * Get total inventory value in this warehouse
     */
    public function getTotalInventoryValueAttribute()
    {
        return $this->inventoryLevels()->sum('total_value');
    }

    /**
     * Get number of products in this warehouse
     */
    public function getProductCountAttribute()
    {
        return $this->inventoryLevels()->count();
    }

    /**
     * Get low stock items in this warehouse
     */
    public function lowStockItems($threshold = null)
    {
        return $this->inventoryLevels()
            ->whereHas('product', function ($query) use ($threshold) {
                $query->where('stock_quantity', '<=', $threshold ?? \DB::raw('products.low_stock_threshold'));
            })
            ->with('product')
            ->get();
    }

    /**
     * Generate warehouse code
     */
    public static function generateCode(): string
    {
        $prefix = 'WH';
        $lastWarehouse = self::orderBy('id', 'desc')->first();
        
        if ($lastWarehouse && preg_match('/^WH(\d+)$/', $lastWarehouse->code, $matches)) {
            $nextNumber = str_pad((int)$matches[1] + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '001';
        }
        
        return $prefix . $nextNumber;
    }

    /**
     * Scope active warehouses
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get default warehouse
     */
    public static function getDefault()
    {
        return self::where('is_default', true)->first();
    }

    /**
     * Check if warehouse has stock for a product
     */
    public function hasStock($productId, $quantity = 1): bool
    {
        $level = $this->inventoryLevels()
            ->where('product_id', $productId)
            ->first();
            
        return $level && $level->quantity_available >= $quantity;
    }
}