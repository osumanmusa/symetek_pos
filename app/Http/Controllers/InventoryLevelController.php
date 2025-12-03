<?php

namespace App\Http\Controllers;

use App\Models\InventoryLevel;
use App\Models\Warehouse;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryLevelController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryLevel::with(['product.category', 'warehouse'])
            ->selectRaw('inventory_levels.*,
                CASE 
                    WHEN (quantity_on_hand - quantity_committed) <= 0 THEN "out_of_stock"
                    WHEN (quantity_on_hand - quantity_committed) <= products.low_stock_threshold THEN "low_stock"
                    ELSE "in_stock"
                END as stock_status')
            ->join('products', 'inventory_levels.product_id', '=', 'products.id');
            
        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                  ->orWhere('products.sku', 'like', "%{$search}%")
                  ->orWhere('products.barcode', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('category_id')) {
            $query->where('products.category_id', $request->input('category_id'));
        }
        
        if ($request->filled('warehouse_id')) {
            $query->where('inventory_levels.warehouse_id', $request->input('warehouse_id'));
        }
        
        if ($request->filled('stock_status')) {
            switch ($request->input('stock_status')) {
                case 'out_of_stock':
                    $query->whereRaw('(quantity_on_hand - quantity_committed) <= 0');
                    break;
                case 'low_stock':
                    $query->whereRaw('(quantity_on_hand - quantity_committed) <= products.low_stock_threshold')
                          ->whereRaw('(quantity_on_hand - quantity_committed) > 0');
                    break;
                case 'in_stock':
                    $query->whereRaw('(quantity_on_hand - quantity_committed) > products.low_stock_threshold');
                    break;
            }
        }
        
        $stockLevels = $query->paginate(50);
        
        // Calculate summary
        $summary = [
            'total_products' => InventoryLevel::distinct('product_id')->count(),
            'in_stock' => $query->clone()
                ->whereRaw('(quantity_on_hand - quantity_committed) > products.low_stock_threshold')
                ->count(),
            'low_stock' => $query->clone()
                ->whereRaw('(quantity_on_hand - quantity_committed) <= products.low_stock_threshold')
                ->whereRaw('(quantity_on_hand - quantity_committed) > 0')
                ->count(),
            'out_of_stock' => $query->clone()
                ->whereRaw('(quantity_on_hand - quantity_committed) <= 0')
                ->count(),
        ];
        
        if ($request->wantsJson() || $request->input('export') === 'csv') {
            return $this->exportCsv($stockLevels);
        }
        
        return Inertia::render('Inventory/StockLevels', [
            'stockLevels' => $stockLevels,
            'categories' => Category::all(),
            'warehouses' => Warehouse::active()->get(),
            'summary' => $summary,
            'filters' => $request->only(['search', 'category_id', 'warehouse_id', 'stock_status']),
        ]);
    }
    
    private function exportCsv($stockLevels)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="stock-report.csv"',
        ];
        
        $callback = function() use ($stockLevels) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, [
                'Product Name',
                'SKU',
                'Category',
                'Warehouse',
                'On Hand',
                'Committed',
                'Available',
                'Average Cost',
                'Total Value',
                'Status',
            ]);
            
            // Add data rows
            foreach ($stockLevels as $level) {
                fputcsv($file, [
                    $level->product->name,
                    $level->product->sku,
                    $level->product->category->name ?? '',
                    $level->warehouse->name,
                    $level->quantity_on_hand,
                    $level->quantity_committed,
                    $level->quantity_available,
                    $level->average_cost,
                    $level->total_value,
                    $level->stock_status_text,
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
