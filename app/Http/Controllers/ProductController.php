<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\InventoryLevel;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('category')
            ->latest()
            ->paginate(20);
            
        $categories = Category::where('is_active', true)->get();
        
        return Inertia::render('Products/index', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
            'barcode' => 'nullable|string|unique:products,barcode',
            'unit' => 'nullable|string|max:50',
            'weight' => 'nullable|numeric|min:0',
            'is_taxable' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $product = Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
            'barcode' => 'nullable|string|unique:products,barcode,' . $product->id,
            'unit' => 'nullable|string|max:50',
            'weight' => 'nullable|numeric|min:0',
            'is_taxable' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }



public function inventory(Product $product)
{
    $product->load(['inventoryLevels.warehouse', 'inventoryTransactions' => function ($query) {
        $query->latest()->limit(50);
    }]);
    
    return Inertia::render('Products/Inventory', [
        'product' => $product,
        'warehouses' => Warehouse::active()->get(),
        'transactionTypes' => \App\Models\InventoryTransaction::getTransactionTypes(), // Add this line
    ]);
}


/**
 * Get product stock across warehouses
 */



public function stockReport(Request $request)
{
    // Start with all products
    $query = Product::with(['category', 'inventoryLevels.warehouse']);
    
    // Apply filters
    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('sku', 'like', "%{$search}%")
              ->orWhere('barcode', 'like', "%{$search}%");
        });
    }
    
    if ($request->filled('category_id')) {
        $query->where('category_id', $request->input('category_id'));
    }
    
    if ($request->filled('product_status')) {
        if ($request->input('product_status') === 'active') {
            $query->where('is_active', true);
        } elseif ($request->input('product_status') === 'inactive') {
            $query->where('is_active', false);
        }
    }
    
    // Get paginated products
    $perPage = $request->input('per_page', 25);
    $products = $query->paginate($perPage);
    
    // Calculate stock data for each product
    $products->getCollection()->transform(function ($product) {
        $totalQuantity = $product->inventoryLevels->sum('quantity_on_hand');
        $totalCommitted = $product->inventoryLevels->sum('quantity_committed');
        $totalAvailable = $totalQuantity - $totalCommitted;
        
        $product->total_quantity = $totalQuantity;
        $product->total_available = $totalAvailable;
        $product->stock_status = $this->getStockStatus($totalAvailable, $product->low_stock_threshold);
        $product->is_low_stock = $totalAvailable > 0 && $totalAvailable <= $product->low_stock_threshold;
        
        return $product;
    });
    
    // Filter by stock status if needed
    if ($request->filled('stock_status')) {
        $filteredProducts = $products->getCollection()->filter(function ($product) use ($request) {
            return $product->stock_status === $request->input('stock_status');
        });
        
        // Create new paginator with filtered results
        $products = new \Illuminate\Pagination\LengthAwarePaginator(
            $filteredProducts,
            $filteredProducts->count(),
            $perPage,
            $request->input('page', 1),
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }
    
    // Calculate summary statistics
    $summary = $this->getStockSummary();
    
    return Inertia::render('Products/StockReport', [
        'products' => $products,
        'categories' => Category::all(),
        'summary' => $summary,
        'filters' => $request->only(['search', 'category_id', 'stock_status', 'product_status']),
    ]);
}

private function getStockStatus($available, $threshold): string
{
    if ($available <= 0) return 'out_of_stock';
    if ($available <= $threshold) return 'low_stock';
    return 'in_stock';
}

private function getStockSummary(): array
{
    $products = Product::with('inventoryLevels')->get();
    
    $inStock = 0;
    $lowStock = 0;
    $outOfStock = 0;
    
    foreach ($products as $product) {
        $totalAvailable = $product->inventoryLevels->sum(function ($level) {
            return $level->quantity_on_hand - $level->quantity_committed;
        });
        
        if ($totalAvailable <= 0) {
            $outOfStock++;
        } elseif ($totalAvailable <= $product->low_stock_threshold) {
            $lowStock++;
        } else {
            $inStock++;
        }
    }
    
    $totalProducts = $products->count();
    
    return [
        'total_products' => $totalProducts,
        'in_stock' => $inStock,
        'low_stock' => $lowStock,
        'out_of_stock' => $outOfStock,
        'in_stock_percentage' => $totalProducts > 0 ? round(($inStock / $totalProducts) * 100, 1) : 0,
        'out_of_stock_percentage' => $totalProducts > 0 ? round(($outOfStock / $totalProducts) * 100, 1) : 0,
    ];
}


private function calculateStockStatus($product): string
{
    if ($product->total_available <= 0) {
        return 'out_of_stock';
    }
    
    if ($product->total_available <= $product->low_stock_threshold) {
        return 'low_stock';
    }
    
    return 'in_stock';
}

private function isLowStock($product): bool
{
    return $product->total_available > 0 && $product->total_available <= $product->low_stock_threshold;
}

private function calculateStockSummary(array $productIds): array
{
    $totalProducts = Product::count();
    
    // Get all products with their stock levels
    $productsWithStock = Product::select([
            'products.id',
            'products.low_stock_threshold',
            \DB::raw('COALESCE(SUM(inventory_levels.quantity_on_hand - inventory_levels.quantity_committed), 0) as available')
        ])
        ->leftJoin('inventory_levels', 'products.id', '=', 'inventory_levels.product_id')
        ->groupBy('products.id')
        ->get();
    
    $inStock = 0;
    $lowStock = 0;
    $outOfStock = 0;
    
    foreach ($productsWithStock as $product) {
        if ($product->available <= 0) {
            $outOfStock++;
        } elseif ($product->available <= $product->low_stock_threshold) {
            $lowStock++;
        } else {
            $inStock++;
        }
    }
    
    $summary = [
        'total_products' => $totalProducts,
        'in_stock' => $inStock,
        'low_stock' => $lowStock,
        'out_of_stock' => $outOfStock,
    ];
    
    // Calculate percentages
    if ($totalProducts > 0) {
        $summary['in_stock_percentage'] = round(($inStock / $totalProducts) * 100, 1);
        $summary['out_of_stock_percentage'] = round(($outOfStock / $totalProducts) * 100, 1);
    }
    
    return $summary;
}


    /**
     * Search products for POS
     */
    public function search(Request $request)
    {
        $search = $request->input('search', '');
        
        $products = Product::where('is_active', true)
            ->where(function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            })
            ->with('category')
            ->limit(20)
            ->get();
            
        return response()->json($products);
    }
}