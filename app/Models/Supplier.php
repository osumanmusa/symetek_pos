<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Supplier extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'contact_person', 'email', 'phone', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
    protected $fillable = [
        'supplier_code',
        'name',
        'contact_person',
        'email',
        'phone',
        'phone_2',
        'address',
        'city',
        'state',
        'country',
        'tax_id',
        'payment_terms',
        'credit_limit',
        'current_balance',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Payment terms options
    const TERMS_CASH = 'cash';
    const TERMS_7_DAYS = '7_days';
    const TERMS_14_DAYS = '14_days';
    const TERMS_30_DAYS = '30_days';
    const TERMS_60_DAYS = '60_days';

    public static function getPaymentTerms()
    {
        return [
            self::TERMS_CASH => 'Cash on Delivery',
            self::TERMS_7_DAYS => '7 Days',
            self::TERMS_14_DAYS => '14 Days',
            self::TERMS_30_DAYS => '30 Days',
            self::TERMS_60_DAYS => '60 Days',
        ];
    }

    /**
     * Relationship with purchase orders
     */
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Generate unique supplier code
     */
    public static function generateSupplierCode()
    {
        $prefix = 'SUP';
        $date = now()->format('ymd');
        $lastSupplier = self::where('supplier_code', 'like', "{$prefix}{$date}%")
            ->orderBy('supplier_code', 'desc')
            ->first();
        
        if ($lastSupplier) {
            $lastNumber = intval(substr($lastSupplier->supplier_code, -3));
            $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '001';
        }
        
        return "{$prefix}{$date}{$nextNumber}";
    }

    /**
     * Get total purchases amount
     */
    public function getTotalPurchasesAttribute()
    {
        return $this->purchaseOrders()->where('status', 'received')->sum('total_amount');
    }

    /**
     * Get outstanding balance
     */
    public function getOutstandingBalanceAttribute()
    {
        return $this->purchaseOrders()
            ->where('payment_status', '!=', 'paid')
            ->where('status', 'received')
            ->sum('balance_due');
    }

    /**
     * Check if supplier has exceeded credit limit
     */
    public function hasExceededCreditLimit()
    {
        if ($this->credit_limit <= 0) {
            return false;
        }
        return $this->current_balance >= $this->credit_limit;
    }

    /**
     * Scope active suppliers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}