<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PurchaseOrder extends Model
{
    use HasFactory, LogsActivity;
    protected $fillable = [
        'po_number',
        'supplier_id',
        'user_id',
        'order_date',
        'expected_delivery_date',
        'delivery_date',
        'status',
        'subtotal',
        'tax_amount',
        'shipping_cost',
        'discount_amount',
        'total_amount',
        'amount_paid',
        'balance_due',
        'notes',
        'shipping_address',
        'payment_method',
        'payment_status',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance_due' => 'decimal:2',
    ];
    protected $appends = ['received_percentage'];
    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_ORDERED = 'ordered';
    const STATUS_PARTIALLY_RECEIVED = 'partially_received';
    const STATUS_RECEIVED = 'received';
    const STATUS_CANCELLED = 'cancelled';

    public static function getStatuses()
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PENDING => 'Pending Approval',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_ORDERED => 'Ordered',
            self::STATUS_PARTIALLY_RECEIVED => 'Partially Received',
            self::STATUS_RECEIVED => 'Received',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    // Payment status constants
    const PAYMENT_PENDING = 'pending';
    const PAYMENT_PARTIAL = 'partial';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_OVERDUE = 'overdue';

    public static function getPaymentStatuses()
    {
        return [
            self::PAYMENT_PENDING => 'Pending',
            self::PAYMENT_PARTIAL => 'Partial',
            self::PAYMENT_PAID => 'Paid',
            self::PAYMENT_OVERDUE => 'Overdue',
        ];
    }

    /**
     * Relationship with supplier
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Relationship with user (creator)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with purchase order items
     */
    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Generate unique PO number
     */
    // public static function generatePONumber()
    // {
    //     $prefix = 'PO';
    //     $date = now()->format('ymd');
    //     $lastPO = self::where('po_number', 'like', "{$prefix}{$date}%")
    //         ->orderBy('po_number', 'desc')
    //         ->first();
        
    //     if ($lastPO) {
    //         $lastNumber = intval(substr($lastPO->po_number, -4));
    //         $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    //     } else {
    //         $nextNumber = '0001';
    //     }
        
    //     return "{$prefix}{$date}{$nextNumber}";
    // }

    /**
     * Calculate totals based on items
     */
    public function calculateTotals()
    {
        $subtotal = $this->items()->sum('total_cost');
        $this->subtotal = $subtotal;
        $this->total_amount = $subtotal + $this->tax_amount + $this->shipping_cost - $this->discount_amount;
        $this->balance_due = $this->total_amount - $this->amount_paid;
        
        // Update payment status
        if ($this->balance_due <= 0) {
            $this->payment_status = self::PAYMENT_PAID;
        } elseif ($this->amount_paid > 0) {
            $this->payment_status = self::PAYMENT_PARTIAL;
        } else {
            $this->payment_status = self::PAYMENT_PENDING;
        }
        
        $this->save();
    }

    /**
     * Check if PO is fully received
     */
    // public function isFullyReceived()
    // {
    //     foreach ($this->items as $item) {
    //         if ($item->quantity > ($item->received_quantity + $item->damaged_quantity + $item->returned_quantity)) {
    //             return false;
    //         }
    //     }
    //     return true;
    // }

    /**
     * Get received percentage
     */
// app/Models/PurchaseOrder.php
public function isFullyReceived(): bool
{
    foreach ($this->items as $item) {
        if ($item->received_quantity < $item->quantity) {
            return false;
        }
    }
    return true;
}
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'order_date', 'expected_delivery_date', 'delivery_date', 'total_amount', 'notes'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Purchase Order {$eventName}");
    }
public function getReceivedPercentageAttribute(): float
{
    if ($this->items->isEmpty()) {
        return 0;
    }
    
    $totalOrdered = $this->items->sum('quantity');
    $totalReceived = $this->items->sum('received_quantity');
    
    return $totalOrdered > 0 ? ($totalReceived / $totalOrdered) * 100 : 0;
}

public static function generatePONumber(): string
{
    $year = date('Y');
    $month = date('m');
    
    $lastPO = self::where('po_number', 'like', "PO-{$year}{$month}-%")
        ->orderBy('id', 'desc')
        ->first();
    
    if ($lastPO) {
        $lastNumber = (int) substr($lastPO->po_number, -4);
        $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    } else {
        $nextNumber = '0001';
    }
    
    return "PO-{$year}{$month}-{$nextNumber}";
}
}