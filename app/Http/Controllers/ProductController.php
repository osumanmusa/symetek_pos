<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
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