<?php

namespace App\Services;

use App\Models\InventoryTransaction;
use App\Models\InventoryLevel;
use App\Models\Warehouse;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    /**
     * Process inventory transaction
     */
    public function processTransaction(array $data): InventoryTransaction
    {
        return DB::transaction(function () use ($data) {
            // Create transaction
            $transaction = InventoryTransaction::create($data);
            
            // Update inventory level
            $this->updateInventoryLevel(
                $transaction->product_id,
                $transaction->warehouse_id,
                $transaction->transaction_type,
                $transaction->quantity,
                $transaction->unit_cost
            );
            
            // Update product main stock quantity
            $this->updateProductStock($transaction->product_id);
            
            return $transaction;
        });
    }
    
    /**
     * Update inventory level
     */
    protected function updateInventoryLevel(
        int $productId,
        ?int $warehouseId,
        string $transactionType,
        float $quantity,
        ?float $unitCost = null
    ): void {
        // If no warehouse specified, use default
        if (!$warehouseId) {
            $warehouse = Warehouse::getDefault();
            $warehouseId = $warehouse?->id;
        }
        
        if (!$warehouseId) {
            throw new \Exception('No warehouse specified and no default warehouse found');
        }
        
        // Get or create inventory level
        $inventoryLevel = InventoryLevel::firstOrCreate(
            [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
            ],
            [
                'quantity_on_hand' => 0,
                'quantity_committed' => 0,
                'average_cost' => 0,
            ]
        );
        
        // Process based on transaction type
        switch ($transactionType) {
            case InventoryTransaction::TYPE_PURCHASE:
            case InventoryTransaction::TYPE_RETURN:
            case InventoryTransaction::TYPE_TRANSFER_IN:
            case InventoryTransaction::TYPE_PRODUCTION:
                $inventoryLevel->increaseOnHand($quantity, $unitCost);
                break;
                
            case InventoryTransaction::TYPE_SALE:
            case InventoryTransaction::TYPE_TRANSFER_OUT:
            case InventoryTransaction::TYPE_CONSUMPTION:
                $inventoryLevel->decreaseOnHand($quantity);
                break;
                
            case InventoryTransaction::TYPE_DAMAGE:
                $inventoryLevel->decreaseOnHand($quantity);
                break;
                
            case InventoryTransaction::TYPE_ADJUSTMENT:
                // For adjustment, quantity can be positive or negative
                if ($quantity > 0) {
                    $inventoryLevel->increaseOnHand($quantity);
                } else {
                    $inventoryLevel->decreaseOnHand(abs($quantity));
                }
                break;
        }
    }
    
    /**
     * Update product's main stock quantity from inventory levels
     */
    public function updateProductStock(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $product->updateMainStockQuantity();
    }
    
    /**
     * Get available stock for product in warehouse
     */
    public function getAvailableStock(int $productId, ?int $warehouseId = null): float
    {
        if ($warehouseId) {
            $level = InventoryLevel::where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->first();
                
            return $level ? $level->quantity_available : 0;
        }
        
        // Get total across all warehouses
        return InventoryLevel::where('product_id', $productId)
            ->sum(DB::raw('quantity_on_hand - quantity_committed'));
    }
    
    /**
     * Transfer stock between warehouses
     */
    public function transferStock(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // Create transfer out transaction
            $transferOut = InventoryTransaction::create([
                'product_id' => $data['product_id'],
                'transaction_type' => InventoryTransaction::TYPE_TRANSFER_OUT,
                'quantity' => $data['quantity'],
                'unit_cost' => $data['unit_cost'] ?? null,
                'total_cost' => $data['total_cost'] ?? null,
                'warehouse_id' => $data['from_warehouse_id'],
                'user_id' => $data['user_id'],
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'metadata' => $data['metadata'] ?? null,
            ]);
            
            // Create transfer in transaction
            $transferIn = InventoryTransaction::create([
                'product_id' => $data['product_id'],
                'transaction_type' => InventoryTransaction::TYPE_TRANSFER_IN,
                'quantity' => $data['quantity'],
                'unit_cost' => $data['unit_cost'] ?? null,
                'total_cost' => $data['total_cost'] ?? null,
                'warehouse_id' => $data['to_warehouse_id'],
                'user_id' => $data['user_id'],
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'metadata' => $data['metadata'] ?? null,
                'transaction_date' => $transferOut->transaction_date,
            ]);
            
            return [
                'transfer_out' => $transferOut,
                'transfer_in' => $transferIn,
            ];
        });
    }
    
    /**
     * Check if product is in stock
     */
    public function checkStock(int $productId, float $quantity, ?int $warehouseId = null): bool
    {
        return $this->getAvailableStock($productId, $warehouseId) >= $quantity;
    }
    
    /**
     * Get stock movement report
     */
    public function getStockMovementReport(array $filters = []): \Illuminate\Support\Collection
    {
        $query = InventoryTransaction::with(['product', 'warehouse', 'user'])
            ->orderBy('transaction_date', 'desc');
            
        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }
        
        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }
        
        if (!empty($filters['transaction_type'])) {
            $query->where('transaction_type', $filters['transaction_type']);
        }
        
        if (!empty($filters['start_date'])) {
            $query->whereDate('transaction_date', '>=', $filters['start_date']);
        }
        
        if (!empty($filters['end_date'])) {
            $query->whereDate('transaction_date', '<=', $filters['end_date']);
        }
        
        return $query->get();
    }


/**
 * Process multiple inventory transactions
 */
public function processBulkTransactions(array $data): array
{
    return DB::transaction(function () use ($data) {
        $transactions = [];
        
        foreach ($data['items'] as $item) {
            $transactionData = [
                'product_id' => $item['product_id'],
                'transaction_type' => $data['transaction_type'],
                'quantity' => $item['quantity'],
                'unit_cost' => $item['unit_cost'] ?? null,
                'total_cost' => isset($item['unit_cost']) ? $item['quantity'] * $item['unit_cost'] : null,
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'user_id' => $data['user_id'],
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $item['notes'] ?? $data['notes'] ?? null,
            ];
            
            $transaction = $this->processTransaction($transactionData);
            $transactions[] = $transaction;
        }
        
        return $transactions;
    });
}


}
