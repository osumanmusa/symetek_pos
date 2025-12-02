<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Calculate today's date
        $today = now()->startOfDay();
        $yesterday = now()->subDay()->startOfDay();
        
        // Today's sales
        $todaySales = Sale::whereDate('created_at', $today)->sum('total_amount') ?? 0;
        
        // Yesterday's sales for growth calculation
        $yesterdaySales = Sale::whereDate('created_at', $yesterday)->sum('total_amount') ?? 0;
        
        // Calculate sales growth percentage
        $salesGrowth = 0;
        if ($yesterdaySales > 0) {
            $salesGrowth = (($todaySales - $yesterdaySales) / $yesterdaySales) * 100;
        } elseif ($todaySales > 0) {
            $salesGrowth = 100; // Infinite growth from 0
        }
        
        // Get other stats
        $totalProducts = Product::count();
        $lowStock = Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count();
        $totalCustomers = Customer::count();
        $newCustomers = Customer::whereMonth('created_at', now()->month)->count();
        $todayTransactions = Sale::whereDate('created_at', $today)->count();
        $averageTransaction = $todayTransactions > 0 ? $todaySales / $todayTransactions : 0;
        
        // Get low stock items
        $lowStockItems = Product::with('category')
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->orderBy('stock_quantity', 'asc')
            ->limit(5)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'category' => $product->category?->name ?? 'Uncategorized',
                    'stock_level' => $product->stock_quantity,
                    'low_stock_threshold' => $product->low_stock_threshold,
                ];
            });
        
        // Get recent transactions (last 5 sales)
        $recentTransactions = Sale::with('customer')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'customer_name' => $sale->customer?->name ?? 'Walk-in Customer',
                    'type' => 'sale',
                    'amount' => $sale->total_amount,
                    'items' => $sale->items_count ?? 0,
                    'time' => $sale->created_at->format('h:i A'),
                ];
            });
        
        // If no sales yet, use sample data
        if ($recentTransactions->isEmpty()) {
            $recentTransactions = collect([
                ['id' => 1, 'customer_name' => 'John Doe', 'type' => 'sale', 'amount' => 249.99, 'items' => 3, 'time' => '10:30 AM'],
                ['id' => 2, 'customer_name' => 'Jane Smith', 'type' => 'sale', 'amount' => 89.50, 'items' => 2, 'time' => '11:15 AM'],
                ['id' => 3, 'customer_name' => 'Acme Corp', 'type' => 'sale', 'amount' => 150.00, 'items' => 1, 'time' => '12:45 PM'],
                ['id' => 4, 'customer_name' => 'Bob Johnson', 'type' => 'sale', 'amount' => 324.75, 'items' => 5, 'time' => '2:20 PM'],
            ]);
        }
        
        // If no low stock items, use sample data
        if ($lowStockItems->isEmpty()) {
            $lowStockItems = collect([
                ['id' => 1, 'name' => 'Wireless Mouse', 'sku' => 'WM-001', 'category' => 'Electronics', 'stock_level' => 8, 'low_stock_threshold' => 10],
                ['id' => 2, 'name' => 'Coffee Beans', 'sku' => 'CB-002', 'category' => 'Grocery', 'stock_level' => 12, 'low_stock_threshold' => 15],
                ['id' => 3, 'name' => 'Notebook', 'sku' => 'NB-003', 'category' => 'Stationery', 'stock_level' => 5, 'low_stock_threshold' => 10],
                ['id' => 4, 'name' => 'USB Cable', 'sku' => 'UC-004', 'category' => 'Electronics', 'stock_level' => 15, 'low_stock_threshold' => 20],
            ]);
        }
        
        return Inertia::render('Dashboard', [
            'stats' => [
                'todaySales' => (float) $todaySales,
                'salesGrowth' => round($salesGrowth, 1),
                'totalProducts' => $totalProducts,
                'lowStock' => $lowStock,
                'totalCustomers' => $totalCustomers,
                'newCustomers' => $newCustomers,
                'todayTransactions' => $todayTransactions,
                'averageTransaction' => round($averageTransaction, 2),
            ],
            'recentTransactions' => $recentTransactions,
            'lowStockItems' => $lowStockItems,
        ]);
    }
}