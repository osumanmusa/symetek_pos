<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'received_quantity',
        'damaged_quantity',
        'returned_quantity',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'damaged_quantity' => 'decimal:2',
        'returned_quantity' => 'decimal:2',
    ];

    /**
     * Relationship with purchase order
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Relationship with product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get remaining quantity to receive
     */
    public function getRemainingQuantityAttribute()
    {
        return $this->quantity - $this->received_quantity - $this->damaged_quantity - $this->returned_quantity;
    }

    /**
     * Get received percentage for this item
     */
    public function getReceivedPercentageAttribute()
    {
        if ($this->quantity == 0) return 0;
        
        $received = $this->received_quantity + $this->damaged_quantity + $this->returned_quantity;
        return round(($received / $this->quantity) * 100, 1);
    }
}