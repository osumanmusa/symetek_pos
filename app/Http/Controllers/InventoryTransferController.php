<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\InventoryLevel;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryTransferController extends Controller
{
    protected $inventoryService;
    
    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
            'reference_number' => 'nullable|string',
        ]);
        
        try {
            DB::beginTransaction();
            
            $product = Product::findOrFail($validated['product_id']);
            $fromWarehouse = Warehouse::findOrFail($validated['from_warehouse_id']);
            $toWarehouse = Warehouse::findOrFail($validated['to_warehouse_id']);
            
            // Check if source has enough stock
            $sourceLevel = InventoryLevel::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'warehouse_id' => $fromWarehouse->id,
                ],
                [
                    'quantity_on_hand' => 0,
                    'quantity_committed' => 0,
                    'average_cost' => $product->cost_price,
                ]
            );
            
            if ($sourceLevel->quantity_on_hand < $validated['quantity']) {
                throw new \Exception("Insufficient stock in source warehouse. Available: {$sourceLevel->quantity_on_hand}, Requested: {$validated['quantity']}");
            }
            
            // Get or create destination inventory level
            $destinationLevel = InventoryLevel::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'warehouse_id' => $toWarehouse->id,
                ],
                [
                    'quantity_on_hand' => 0,
                    'quantity_committed' => 0,
                    'average_cost' => $product->cost_price,
                ]
            );
            
            // Create transfer reference number
            $referenceNumber = $validated['reference_number'] ?? 'TRF-' . date('Ymd-His');
            
            // Create transfer out transaction
            $transferOut = InventoryTransaction::create([
                'product_id' => $product->id,
                'transaction_type' => InventoryTransaction::TYPE_TRANSFER_OUT,
                'quantity' => $validated['quantity'],
                'unit_cost' => $sourceLevel->average_cost,
                'total_cost' => $validated['quantity'] * $sourceLevel->average_cost,
                'warehouse_id' => $fromWarehouse->id,
                'user_id' => auth()->id(),
                'reference_number' => $referenceNumber,
                'notes' => "Transfer to {$toWarehouse->name}" . ($validated['notes'] ? "\n{$validated['notes']}" : ''),
                'metadata' => [
                    'transfer_to' => $toWarehouse->id,
                    'transfer_type' => 'stock_transfer',
                ],
            ]);
            
            // Create transfer in transaction
            $transferIn = InventoryTransaction::create([
                'product_id' => $product->id,
                'transaction_type' => InventoryTransaction::TYPE_TRANSFER_IN,
                'quantity' => $validated['quantity'],
                'unit_cost' => $sourceLevel->average_cost,
                'total_cost' => $validated['quantity'] * $sourceLevel->average_cost,
                'warehouse_id' => $toWarehouse->id,
                'user_id' => auth()->id(),
                'reference_number' => $referenceNumber,
                'notes' => "Transfer from {$fromWarehouse->name}" . ($validated['notes'] ? "\n{$validated['notes']}" : ''),
                'metadata' => [
                    'transfer_from' => $fromWarehouse->id,
                    'transfer_type' => 'stock_transfer',
                ],
                'transaction_date' => $transferOut->transaction_date,
            ]);
            
            // Update source inventory level
            $sourceLevel->quantity_on_hand -= $validated['quantity'];
            $sourceLevel->save();
            
            // Update destination inventory level
            // For average cost calculation on transfer in
            $destinationLevel->updateAverageCost($validated['quantity'], $sourceLevel->average_cost);
            $destinationLevel->quantity_on_hand += $validated['quantity'];
            $destinationLevel->save();
            
            // Update product main stock quantity
            $this->inventoryService->updateProductStock($product->id);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Stock transfer completed successfully.',
                'transactions' => [
                    'transfer_out' => $transferOut,
                    'transfer_in' => $transferIn,
                ],
                'inventory_levels' => [
                    'source' => $sourceLevel,
                    'destination' => $destinationLevel,
                ],
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete transfer: ' . $e->getMessage(),
            ], 500);
        }
    }
}
