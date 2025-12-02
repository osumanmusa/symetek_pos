<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'user_id',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'amount_paid',
        'change_amount',
        'payment_method',
        'notes',
        'is_completed'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'is_completed' => 'boolean',
    ];

    // Payment methods
    const PAYMENT_CASH = 'cash';
    const PAYMENT_CARD = 'card';
    const PAYMENT_MOBILE = 'mobile';
    const PAYMENT_TRANSFER = 'transfer';

    public static function getPaymentMethods(): array
    {
        return [
            self::PAYMENT_CASH => 'Cash',
            self::PAYMENT_CARD => 'Card',
            self::PAYMENT_MOBILE => 'Mobile Money',
            self::PAYMENT_TRANSFER => 'Bank Transfer',
        ];
    }

    /**
     * Relationship with customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relationship with user (cashier)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with sale items
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Generate unique invoice number
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $date = now()->format('ymd');
        $lastInvoice = self::where('invoice_number', 'like', "{$prefix}{$date}%")
            ->orderBy('invoice_number', 'desc')
            ->first();
        
        if ($lastInvoice) {
            $lastNumber = intval(substr($lastInvoice->invoice_number, -4));
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }
        
        return "{$prefix}{$date}{$nextNumber}";
    }

    /**
     * Calculate totals based on items
     */
    public function calculateTotals(): void
    {
        $subtotal = $this->items()->sum('total_price');
        
        $this->update([
            'subtotal' => $subtotal,
            'total_amount' => $subtotal + $this->tax_amount - $this->discount_amount,
            'change_amount' => max(0, $this->amount_paid - ($subtotal + $this->tax_amount - $this->discount_amount))
        ]);
    }

    /**
     * Add item to sale
     */
    public function addItem(Product $product, int $quantity): SaleItem
    {
        $totalPrice = $product->price * $quantity;
        
        $item = SaleItem::create([
            'sale_id' => $this->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => $product->price,
            'total_price' => $totalPrice,
            'cost_price' => $product->cost_price,
        ]);
        
        // Update product stock
        $product->decrement('stock_quantity', $quantity);
        
        $this->calculateTotals();
        
        return $item;
    }

    /**
     * Mark sale as completed
     */
    public function markAsCompleted(): bool
    {
        $this->is_completed = true;
        
        // Update customer stats if customer exists
        if ($this->customer) {
            $this->customer->updatePurchaseStats($this);
        }
        
        return $this->save();
    }

    /**
     * Get profit margin for this sale
     */
    public function getProfitAttribute(): float
    {
        $totalCost = $this->items()->sum(\DB::raw('cost_price * quantity'));
        return $this->total_amount - $totalCost;
    }

    /**
     * Scope today's sales
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope completed sales
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    /**
     * Get items count
     */
    public function getItemsCountAttribute(): int
    {
        return $this->items()->count();
    }
}