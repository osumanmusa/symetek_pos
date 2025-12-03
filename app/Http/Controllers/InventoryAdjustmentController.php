<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\InventoryLevel;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryAdjustmentController extends Controller
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
            'warehouse_id' => 'required|exists:warehouses,id',
            'adjustment_type' => 'required|in:add,remove,set',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:1000',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        
        try {
            DB::beginTransaction();
            
            $product = Product::findOrFail($validated['product_id']);
            $warehouse = Warehouse::findOrFail($validated['warehouse_id']);
            
            // Get current inventory level
            $inventoryLevel = InventoryLevel::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                ],
                [
                    'quantity_on_hand' => 0,
                    'quantity_committed' => 0,
                    'average_cost' => $product->cost_price,
                ]
            );
            
            $currentQuantity = $inventoryLevel->quantity_on_hand;
            $adjustmentQuantity = $validated['quantity'];
            
            // Calculate new quantity based on adjustment type
            switch ($validated['adjustment_type']) {
                case 'add':
                    $newQuantity = $currentQuantity + $adjustmentQuantity;
                    $transactionQuantity = $adjustmentQuantity;
                    break;
                    
                case 'remove':
                    $newQuantity = max(0, $currentQuantity - $adjustmentQuantity);
                    $transactionQuantity = -$adjustmentQuantity;
                    break;
                    
                case 'set':
                    $newQuantity = $adjustmentQuantity;
                    $transactionQuantity = $adjustmentQuantity - $currentQuantity;
                    break;
                    
                default:
                    throw new \Exception('Invalid adjustment type');
            }
            
            // Create inventory transaction
            $transaction = InventoryTransaction::create([
                'product_id' => $product->id,
                'transaction_type' => InventoryTransaction::TYPE_ADJUSTMENT,
                'quantity' => abs($transactionQuantity),
                'unit_cost' => $inventoryLevel->average_cost,
                'total_cost' => abs($transactionQuantity) * $inventoryLevel->average_cost,
                'warehouse_id' => $warehouse->id,
                'user_id' => auth()->id(),
                'reference_number' => $validated['reference_number'] ?? 'ADJ-' . date('Ymd-His'),
                'notes' => "Adjustment: {$validated['reason']}" . ($validated['notes'] ? "\n{$validated['notes']}" : ''),
                'metadata' => [
                    'adjustment_type' => $validated['adjustment_type'],
                    'previous_quantity' => $currentQuantity,
                    'new_quantity' => $newQuantity,
                    'reason' => $validated['reason'],
                ],
            ]);
            
            // Update inventory level
            $inventoryLevel->quantity_on_hand = $newQuantity;
            $inventoryLevel->save();
            
            // Update product main stock quantity
            $this->inventoryService->updateProductStock($product->id);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Stock adjustment recorded successfully.',
                'transaction' => $transaction,
                'inventory_level' => $inventoryLevel,
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to record adjustment: ' . $e->getMessage(),
            ], 500);
        }
    }
}
