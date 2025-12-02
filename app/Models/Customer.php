<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'total_spent',
        'total_visits',
        'last_visit',
        'notes'
    ];

    protected $casts = [
        'total_spent' => 'decimal:2',
        'total_visits' => 'integer',
        'last_visit' => 'date',
    ];

    /**
     * Relationship with sales
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Get customer's recent sales
     */
    public function recentSales($limit = 5)
    {
        return $this->sales()->latest()->limit($limit)->get();
    }

    /**
     * Update customer statistics after a purchase
     */
    public function updatePurchaseStats(Sale $sale): void
    {
        $this->total_spent += $sale->total_amount;
        $this->total_visits += 1;
        $this->last_visit = now();
        $this->save();
    }

    /**
     * Get average purchase value
     */
    public function getAveragePurchaseAttribute(): float
    {
        if ($this->total_visits === 0) {
            return 0;
        }
        return $this->total_spent / $this->total_visits;
    }

    /**
     * Scope active customers (have made purchases)
     */
    public function scopeActive($query)
    {
        return $query->where('total_visits', '>', 0);
    }

    /**
     * Scope customers by location
     */
    public function scopeInCity($query, $city)
    {
        return $query->where('city', $city);
    }

    /**
     * Find customer by phone or email
     */
    public static function findByContact($contact)
    {
        return self::where('phone', $contact)
            ->orWhere('email', $contact)
            ->first();
    }

    /**
     * Generate customer display name
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->company_name) {
            return "{$this->name} ({$this->company_name})";
        }
        return $this->name;
    }
}