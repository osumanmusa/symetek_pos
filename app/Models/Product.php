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
    public function getIsLowStockAttribute()
    {
        return $this->stock_quantity <= $this->low_stock_threshold;
    }
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