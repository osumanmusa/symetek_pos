<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryTransactionController extends Controller
{
    protected $inventoryService;
    
    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }
    
    public function index(Request $request)
    {
        $transactions = InventoryTransaction::with(['product', 'warehouse', 'user'])
            ->latest()
            ->paginate(50);
            
        return Inertia::render('Inventory/Transactions/index', [
            'transactions' => $transactions,
            'transactionTypes' => InventoryTransaction::getTransactionTypes(),
            'filters' => $request->only(['search', 'type', 'product_id', 'warehouse_id']),
        ]);
    }
    
    public function create()
    {
        return Inertia::render('Inventory/Transactions/Create', [
            'products' => Product::active()->get(['id', 'name', 'sku']),
            'warehouses' => Warehouse::active()->get(['id', 'name', 'code']),
            'transactionTypes' => InventoryTransaction::getTransactionTypes(),
        ]);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'transaction_type' => 'required|string',
            'quantity' => 'required|numeric|min:0.01',
            'unit_cost' => 'nullable|numeric|min:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'notes' => 'nullable|string',
            'reference_number' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);
        
        $validated['user_id'] = auth()->id();
        
        $transaction = $this->inventoryService->processTransaction($validated);
        
        return redirect()->route('inventory.transactions.index')
            ->with('success', 'Transaction recorded successfully.');
    }
    
    public function show(InventoryTransaction $transaction)
    {
        $transaction->load(['product', 'warehouse', 'user']);
        
        return Inertia::render('Inventory/Transactions/Show', [
            'transaction' => $transaction,
        ]);
    }
    
    public function getProductStock(Request $request, Product $product)
    {
        $warehouseId = $request->input('warehouse_id');
        $availableStock = $this->inventoryService->getAvailableStock($product->id, $warehouseId);
        
        return response()->json([
            'available_stock' => $availableStock,
            'product' => $product->only(['id', 'name', 'sku']),
            'warehouse_id' => $warehouseId,
        ]);
    }
}
