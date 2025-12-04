<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\InventoryLevel;
use Illuminate\Support\Facades\DB;

class InventoryTransactionController extends Controller
{
    protected $inventoryService;
    
    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }
    
    /**
     * Display a listing of the resource.
     */

public function index(Request $request)
{
    $query = InventoryTransaction::with(['product', 'warehouse', 'user']);
    
    // Apply filters
    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function ($q) use ($search) {
            $q->whereHas('product', function ($productQuery) use ($search) {
                $productQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            })
            ->orWhere('reference_number', 'like', "%{$search}%")
            ->orWhere('notes', 'like', "%{$search}%");
        });
    }
    
    if ($request->filled('transaction_type')) {
        $query->where('transaction_type', $request->input('transaction_type'));
    }
    
    if ($request->filled('warehouse_id')) {
        $query->where('warehouse_id', $request->input('warehouse_id'));
    }
    
    if ($request->filled('start_date')) {
        $query->whereDate('transaction_date', '>=', $request->input('start_date'));
    }
    
    if ($request->filled('end_date')) {
        $query->whereDate('transaction_date', '<=', $request->input('end_date'));
    }
    
    // Apply sorting
    $sortBy = $request->input('sort_by', 'transaction_date');
    $sortOrder = $request->input('sort_order', 'desc');
    $query->orderBy($sortBy, $sortOrder);
    
    $perPage = $request->input('per_page', 25);
    $transactions = $query->paginate($perPage);
    
    // Calculate statistics
    $stats = [
        'total_transactions' => InventoryTransaction::count(),
        'stock_in' => InventoryTransaction::whereIn('transaction_type', ['purchase', 'return', 'transfer_in', 'production'])
            ->sum('quantity'),
        'stock_out' => InventoryTransaction::whereIn('transaction_type', ['sale', 'damage', 'transfer_out', 'consumption'])
            ->sum('quantity'),
        'total_value' => InventoryTransaction::sum('total_cost'),
    ];
    
    // Handle CSV export
    if ($request->has('export') && $request->input('export') === 'csv') {
        return $this->exportToCsv($query->get());
    }
    
    return Inertia::render('Inventory/Transactions/index', [
        'transactions' => $transactions,
        'transactionTypes' => InventoryTransaction::getTransactionTypes(),
        'warehouses' => \App\Models\Warehouse::active()->get(['id', 'name', 'code']),
        'filters' => $request->only(['search', 'transaction_type', 'warehouse_id', 'start_date', 'end_date', 'sort_by', 'sort_order']),
        'stats' => $stats,
    ]);
}

private function exportToCsv($transactions)
{
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="inventory-transactions-' . date('Y-m-d') . '.csv"',
    ];

    $callback = function() use ($transactions) {
        $file = fopen('php://output', 'w');
        
        // Add headers
        fputcsv($file, [
            'Date',
            'Type',
            'Product Name',
            'Product SKU',
            'Quantity',
            'Unit Cost',
            'Total Cost',
            'Warehouse',
            'Reference',
            'User',
            'Notes'
        ]);
        
        // Add data rows
        foreach ($transactions as $transaction) {
            fputcsv($file, [
                $transaction->transaction_date,
                $transaction->transaction_type,
                $transaction->product?->name,
                $transaction->product?->sku,
                $transaction->quantity,
                $transaction->unit_cost,
                $transaction->total_cost,
                $transaction->warehouse?->name,
                $transaction->reference_number,
                $transaction->user?->name,
                $transaction->notes
            ]);
        }
        
        fclose($file);
    };
    
    return response()->stream($callback, 200, $headers);
}
    
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Inventory/Transactions/Create', [
            'products' => Product::active()->get(['id', 'name', 'sku']),
            'warehouses' => Warehouse::active()->get(['id', 'name', 'code']),
            'transactionTypes' => InventoryTransaction::getTransactionTypes(),
        ]);
    }
    
    /**
     * Store a newly created resource in storage.
     */

public function store(Request $request)
{
    $validated = $request->validate([
        'transaction_type' => 'required|string',
        'warehouse_id' => 'nullable|exists:warehouses,id',
        'reference_number' => 'nullable|string',
        'notes' => 'nullable|string',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|numeric|min:0.01',
        'items.*.unit_cost' => 'nullable|numeric|min:0',
        'items.*.notes' => 'nullable|string',
    ]);
    
    try {
        DB::beginTransaction();
        
        $transactions = [];
        foreach ($validated['items'] as $item) {
            $transactionData = [
                'product_id' => $item['product_id'],
                'transaction_type' => $validated['transaction_type'],
                'quantity' => $item['quantity'],
                'unit_cost' => $item['unit_cost'] ?? null,
                'total_cost' => isset($item['unit_cost']) ? $item['quantity'] * $item['unit_cost'] : null,
                'warehouse_id' => $validated['warehouse_id'] ?? null,
                'user_id' => auth()->id(),
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $item['notes'] ?? $validated['notes'] ?? null,
            ];
            
            $transaction = $this->inventoryService->processTransaction($transactionData);
            $transactions[] = $transaction;
        }
        
        DB::commit();
        
        return redirect()->route('inventory.transactions.index')
            ->with('success', count($transactions) . ' transaction(s) recorded successfully.');
            
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()
            ->withInput()
            ->with('error', 'Error recording transactions: ' . $e->getMessage());
    }
}
    
    /**
     * Display the specified resource.
     */

public function show(InventoryTransaction $transaction)
{
    $transaction->load(['product.category', 'warehouse', 'user']);
    
    return Inertia::render('Inventory/Transactions/Show', [
        'transaction' => $transaction,
        'transactionTypes' => InventoryTransaction::getTransactionTypes(), // Add this line
    ]);
}

    
    /**
     * Get product stock information.
     */
    public function getProductStock(Request $request, Product $product)
    {
        $warehouseId = $request->input('warehouse_id');
        $availableStock = $this->inventoryService->getAvailableStock($product->id, $warehouseId);
        
        return response()->json([
            'available_stock' => $availableStock,
            'product' => $product->only(['id', 'name', 'sku', 'unit']),
            'warehouse_id' => $warehouseId,
        ]);
    }

 // ... existing code ...

    /**
     * Handle stock adjustment
     */

public function adjust(Request $request)
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
        
        // Build notes string safely
        $notes = "Adjustment: {$validated['reason']}";
        if (!empty($validated['notes'])) {
            $notes .= "\n" . $validated['notes'];
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
            'notes' => $notes, // Use the safely built notes
            'metadata' => [
                'adjustment_type' => $validated['adjustment_type'],
                'previous_quantity' => $currentQuantity,
                'new_quantity' => $newQuantity,
                'reason' => $validated['reason'],
                'notes' => $validated['notes'] ?? '', // Handle missing notes in metadata too
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

    
    /**
     * Handle stock transfer
     */
    public function transfer(Request $request)
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
// In InventoryTransactionController.php, add these methods:

/**
 * Show receive goods interface (unified for new products and stock receiving)
 */
public function createReceive()
{
    return Inertia::render('Inventory/Transactions/Receive', [
        'warehouses' => Warehouse::active()->get(['id', 'name', 'code']),
        'suppliers' => \App\Models\Supplier::active()->get(['id', 'name', 'supplier_code']),
        'categories' => \App\Models\Category::active()->get(['id', 'name']),
        'existingProducts' => Product::active()
            ->with(['category'])
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'barcode', 'selling_price', 'cost_price', 'unit', 'category_id'])
            ->take(100), // Limit for performance
        'paymentMethods' => [
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'cheque' => 'Cheque',
            'credit' => 'Credit'
        ],
    ]);
}

/**
 * Process receive goods (can create new products or add to existing)
 */
public function storeReceive(Request $request)
{
    $validated = $request->validate([
        'supplier_id' => 'nullable|exists:suppliers,id',
        'warehouse_id' => 'required|exists:warehouses,id',
        'reference_number' => 'nullable|string',
        'invoice_number' => 'nullable|string',
        'payment_method' => 'nullable|string',
        'notes' => 'nullable|string',
        'items' => 'required|array|min:1',
        'items.*.type' => 'required|in:existing,new',
        'items.*.product_id' => 'required_if:items.*.type,existing|exists:products,id',
        'items.*.quantity' => 'required|numeric|min:0.01',
        'items.*.unit_cost' => 'required|numeric|min:0',
        'items.*.selling_price' => 'nullable|numeric|min:0',
        'items.*.barcode' => 'nullable|string',
        'items.*.sku' => 'nullable|string',
        'items.*.name' => 'required_if:items.*.type,new|string|max:255',
        'items.*.category_id' => 'nullable|exists:categories,id',
        'items.*.unit' => 'nullable|string|max:50',
        'items.*.description' => 'nullable|string',
    ]);
    
    try {
        DB::beginTransaction();
        
        $transactions = [];
        $newProducts = [];
        
        foreach ($validated['items'] as $item) {
            if ($item['type'] === 'new') {
                // Create new product
                $product = Product::create([
                    'name' => $item['name'],
                    'sku' => $item['sku'] ?? Product::generateSku(),
                    'barcode' => $item['barcode'] ?? null,
                    'category_id' => $item['category_id'] ?? null,
                    'cost_price' => $item['unit_cost'],
                    'selling_price' => $item['selling_price'] ?? ($item['unit_cost'] * 1.3), // 30% markup by default
                    'stock_quantity' => 0, // Will be updated by transaction
                    'low_stock_threshold' => 10,
                    'unit' => $item['unit'] ?? 'pcs',
                    'description' => $item['description'] ?? null,
                    'is_active' => true,
                    'is_taxable' => true,
                ]);
                
                $newProducts[] = $product;
                $productId = $product->id;
            } else {
                $productId = $item['product_id'];
                $product = Product::find($productId);
                
                // Update product cost price if needed
                if ($item['unit_cost'] > 0) {
                    $product->cost_price = $item['unit_cost'];
                    $product->save();
                }
            }
            
            // Create inventory transaction
            $transactionData = [
                'product_id' => $productId,
                'transaction_type' => InventoryTransaction::TYPE_PURCHASE,
                'quantity' => $item['quantity'],
                'unit_cost' => $item['unit_cost'],
                'total_cost' => $item['quantity'] * $item['unit_cost'],
                'warehouse_id' => $validated['warehouse_id'],
                'user_id' => auth()->id(),
                'reference_number' => $validated['reference_number'] ?? 'PUR-' . date('Ymd-His'),
                'notes' => "Purchase from supplier" . 
                          ($validated['supplier_id'] ? " #" . $validated['supplier_id'] : "") .
                          ($validated['notes'] ? "\n{$validated['notes']}" : ''),
                'metadata' => [
                    'supplier_id' => $validated['supplier_id'],
                    'invoice_number' => $validated['invoice_number'],
                    'payment_method' => $validated['payment_method'],
                ],
            ];
            
            $transaction = $this->inventoryService->processTransaction($transactionData);
            $transactions[] = $transaction;
        }
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => count($transactions) . ' item(s) received successfully',
            'transactions' => $transactions,
            'new_products' => $newProducts,
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        
        return response()->json([
            'success' => false,
            'message' => 'Error receiving goods: ' . $e->getMessage(),
        ], 500);
    }
}

// Also add this helper method to Product model
public static function generateSku()
{
    $prefix = 'PROD';
    $random = strtoupper(substr(md5(microtime()), 0, 6));
    return $prefix . '-' . $random;
}
}
