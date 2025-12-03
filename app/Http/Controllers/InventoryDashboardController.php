<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\InventoryLevel;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryDashboardController extends Controller
{
    public function index()
    {
        // Get recent transactions (last 10)
        $recentTransactions = InventoryTransaction::with(['product', 'warehouse'])
            ->latest()
            ->limit(10)
            ->get();

        // Get low stock items
        $lowStockItems = InventoryLevel::with(['product', 'warehouse'])
            ->whereHas('product', function ($query) {
                $query->whereRaw('quantity_on_hand - quantity_committed <= low_stock_threshold')
                      ->whereRaw('quantity_on_hand - quantity_committed > 0');
            })
            ->limit(10)
            ->get();

        // Calculate stats
        $stats = [
            'total_products' => Product::count(),
            'total_inventory_value' => InventoryLevel::sum(\DB::raw('quantity_on_hand * average_cost')),
            'low_stock_items' => InventoryLevel::whereHas('product', function ($query) {
                $query->whereRaw('quantity_on_hand - quantity_committed <= low_stock_threshold')
                      ->whereRaw('quantity_on_hand - quantity_committed > 0');
            })->count(),
            'out_of_stock_items' => InventoryLevel::whereRaw('quantity_on_hand - quantity_committed <= 0')->count(),
        ];

        return Inertia::render('Inventory/index', [
            'recentTransactions' => $recentTransactions,
            'lowStockItems' => $lowStockItems,
            'stats' => $stats,
            'transactionTypes' => \App\Models\InventoryTransaction::getTransactionTypes(),
        ]);
    }
}
