<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'transaction_type',
        'quantity',
        'unit_cost',
        'total_cost',
        'reference_type',
        'reference_id',
        'reference_number',
        'warehouse_id',
        'user_id',
        'notes',
        'metadata',
        'transaction_date',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'metadata' => 'array',
        'transaction_date' => 'datetime',
    ];

    // Transaction types
    const TYPE_PURCHASE = 'purchase';
    const TYPE_SALE = 'sale';
    const TYPE_ADJUSTMENT = 'adjustment';
    const TYPE_RETURN = 'return';
    const TYPE_DAMAGE = 'damage';
    const TYPE_TRANSFER_IN = 'transfer_in';
    const TYPE_TRANSFER_OUT = 'transfer_out';
    const TYPE_PRODUCTION = 'production';
    const TYPE_CONSUMPTION = 'consumption';

    public static function getTransactionTypes(): array
    {
        return [
            self::TYPE_PURCHASE => 'Purchase',
            self::TYPE_SALE => 'Sale',
            self::TYPE_ADJUSTMENT => 'Adjustment',
            self::TYPE_RETURN => 'Return',
            self::TYPE_DAMAGE => 'Damage/Loss',
            self::TYPE_TRANSFER_IN => 'Transfer In',
            self::TYPE_TRANSFER_OUT => 'Transfer Out',
            self::TYPE_PRODUCTION => 'Production',
            self::TYPE_CONSUMPTION => 'Consumption',
        ];
    }

    /**
     * Relationship with product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relationship with warehouse
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Relationship with user who made the transaction
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Polymorphic relationship with reference (purchase order, sale, etc.)
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get transaction description
     */
    public function getDescriptionAttribute(): string
    {
        $types = self::getTransactionTypes();
        $type = $types[$this->transaction_type] ?? $this->transaction_type;
        
        if ($this->reference_number) {
            return "{$type} - {$this->reference_number}";
        }
        
        return $type;
    }

    /**
     * Check if transaction increases inventory
     */
    public function isIncrement(): bool
    {
        return in_array($this->transaction_type, [
            self::TYPE_PURCHASE,
            self::TYPE_RETURN,
            self::TYPE_TRANSFER_IN,
            self::TYPE_PRODUCTION,
            self::TYPE_ADJUSTMENT, // Can be positive or negative
        ]);
    }

    /**
     * Check if transaction decreases inventory
     */
    public function isDecrement(): bool
    {
        return in_array($this->transaction_type, [
            self::TYPE_SALE,
            self::TYPE_DAMAGE,
            self::TYPE_TRANSFER_OUT,
            self::TYPE_CONSUMPTION,
            self::TYPE_ADJUSTMENT, // Can be positive or negative
        ]);
    }

    /**
     * Get effective quantity (positive for increments, negative for decrements)
     */
    public function getEffectiveQuantityAttribute(): float
    {
        if ($this->transaction_type === self::TYPE_ADJUSTMENT) {
            // Adjustments can be positive or negative based on quantity
            return $this->quantity;
        }
        
        return $this->isIncrement() ? abs($this->quantity) : -abs($this->quantity);
    }

    /**
     * Scope for specific product
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope for specific warehouse
     */
    public function scopeForWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope for transaction type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }
}