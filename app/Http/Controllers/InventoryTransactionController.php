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
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $transactions = InventoryTransaction::with(['product', 'warehouse', 'user'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('product', function ($productQuery) use ($search) {
                        $productQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%");
                    })
                    ->orWhere('reference_number', 'like', "%{$search}%");
                });
            })
            ->when($request->input('type'), function ($query, $type) {
                $query->where('transaction_type', $type);
            })
            ->when($request->input('product_id'), function ($query, $productId) {
                $query->where('product_id', $productId);
            })
            ->when($request->input('warehouse_id'), function ($query, $warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            })
            ->when($request->input('start_date'), function ($query, $startDate) {
                $query->whereDate('transaction_date', '>=', $startDate);
            })
            ->when($request->input('end_date'), function ($query, $endDate) {
                $query->whereDate('transaction_date', '<=', $endDate);
            })
            ->latest()
            ->paginate(50);
            
        return Inertia::render('Inventory/Transactions/index', [
            'transactions' => $transactions,
            'transactionTypes' => InventoryTransaction::getTransactionTypes(),
            'filters' => $request->only(['search', 'type', 'product_id', 'warehouse_id', 'start_date', 'end_date']),
        ]);
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
        
        try {
            $transaction = $this->inventoryService->processTransaction($validated);
            
            return redirect()->route('inventory-transactions.index')
                ->with('success', 'Transaction recorded successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error recording transaction: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the specified resource.
     */
    public function show(InventoryTransaction $transaction)
    {
        $transaction->load(['product', 'warehouse', 'user']);
        
        return Inertia::render('Inventory/Transactions/Show', [
            'transaction' => $transaction,
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
}
