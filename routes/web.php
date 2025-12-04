<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\InventoryTransactionController;
use App\Http\Controllers\InventoryDashboardController;
use App\Http\Controllers\InventoryLevelController;
use App\Http\Controllers\InventoryAdjustmentController;
use App\Http\Controllers\InventoryTransferController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\CustomerController;


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;



Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
  
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    
    // Admin Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        // Users
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        
        // Roles
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
            // Permissions
    Route::get('/roles/{role}/permissions', [PermissionController::class, 'index'])->name('roles.permissions');
    Route::put('/roles/{role}/permissions', [PermissionController::class, 'update'])->name('roles.permissions.update');

    });
    
    // POS Routes - Create placeholder routes for all navigation items
Route::post('/inventory/adjustment', [InventoryAdjustmentController::class, 'store'])->name('inventory.adjustment.store');
Route::post('/inventory/transfer', [InventoryTransferController::class, 'store'])->name('inventory.transfer.store');

    // Products Routes
Route::prefix('products')->name('products.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('index');
     Route::get('/create', function () {
        return inertia('Products/Create'); // Add this if you want separate page
    })->name('create');
    Route::post('/', [ProductController::class, 'store'])->name('store');
    Route::put('/{product}', [ProductController::class, 'update'])->name('update');
    Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
    Route::get('/search', [ProductController::class, 'search'])->name('search');
});
    Route::resource('categories', CategoryController::class); 
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');

// Categories Routes
// Route::prefix('categories')->name('categories.')->group(function () {
//     Route::get('/', [CategoryController::class, 'index'])->name('index');
//     Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
//     Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
//     Route::post('/', [CategoryController::class, 'store'])->name('store');
//     Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
//     Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
// });    // Or if you want specific routes:
    
    Route::prefix('inventory')->name('inventory.')->group(function () {
        // Route::get('/', function () {
        //     return inertia('Inventory/index');
        // })->name('index');
        Route::get('/adjust', function () {
            return inertia('Inventory/Adjust');
        })->name('adjust');
    });
        // Purchase Orders
       Route::get('/inventory', [InventoryDashboardController::class, 'index'])->name('inventory.index');
           Route::prefix('inventory-transactions')->name('inventory.transactions.')->group(function () {
        Route::get('/', [InventoryTransactionController::class, 'index'])->name('index');
        Route::get('/create', [InventoryTransactionController::class, 'create'])->name('create');
        Route::post('/', [InventoryTransactionController::class, 'store'])->name('store');
        
        Route::get('/{transaction}', [InventoryTransactionController::class, 'show'])->name('show');
    });
    // routes/web.php
Route::get('/inventory/receive', [InventoryTransactionController::class, 'createReceive'])->name('inventory.receive');
Route::post('/inventory/receive', [InventoryTransactionController::class, 'storeReceive'])->name('inventory.receive.store');

        Route::post('/inventory-transactions', [InventoryTransactionController::class, 'store'])->name('inventory-transactions.store');
    // Purchase Orders
    Route::resource('purchase-orders', PurchaseOrderController::class);
        // Purchase Orders
    Route::get('/purchase-orders/export', [PurchaseOrderController::class, 'export'])->name('purchase-orders.export');
    Route::resource('purchase-orders', PurchaseOrderController::class);
    
    // Purchase Order Additional Routes
    Route::prefix('purchase-orders')->name('purchase-orders.')->group(function () {    
        Route::get('/{purchaseOrder}/print', [PurchaseOrderController::class, 'print'])->name('print');
    Route::get('/export', [PurchaseOrderController::class, 'export'])->name('export');
        // Route::get('/{purchaseOrder}/print', [PurchaseOrderController::class, 'print'])->name('print');
        Route::post('/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->name('approve');
        Route::post('/{purchaseOrder}/order', [PurchaseOrderController::class, 'markAsOrdered'])->name('order');
        Route::post('/{purchaseOrder}/approve-and-order', [PurchaseOrderController::class, 'approveAndOrder'])->name('approve-and-order');
        Route::post('/{purchaseOrder}/receive-full', [PurchaseOrderController::class, 'markAsFullyReceived'])->name('receive-full');
        Route::post('/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('cancel');
        Route::get('/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('receive');
        Route::post('/{purchaseOrder}/process-receive', [PurchaseOrderController::class, 'processReceive'])->name('process-receive');
        Route::post('/{purchaseOrder}/update-payment', [PurchaseOrderController::class, 'updatePayment'])->name('update-payment');
        Route::post('/{purchaseOrder}/mark-paid', [PurchaseOrderController::class, 'markAsPaid'])->name('mark-paid');
    });
    // In routes/web.php
Route::get('/export-purchase-orders', [PurchaseOrderController::class, 'export'])
    ->name('export.purchase-orders'); // Simpler name
//    Route::post('/purchase-orders/{purchaseOrder}/order', [PurchaseOrderController::class, 'markAsOrdered'])
//         ->name('purchase-orders.order');
    
   // Warehouse Routes
    Route::resource('warehouses', WarehouseController::class)->except(['show']);
    
    // Inventory Transactions
    Route::resource('inventory.transactions', InventoryTransactionController::class)->except(['edit', 'update']);
    Route::get('inventory/transactions/{transaction}', [InventoryTransactionController::class, 'show'])->name('inventory.transactions.show');
    Route::get('products/{product}/stock', [InventoryTransactionController::class, 'getProductStock'])->name('products.stock');
        Route::get('products/{product}/inventory', [ProductController::class, 'inventory'])->name('products.inventory');
    Route::get('products/stock-report', [ProductController::class, 'stockReport'])->name('products.stock-report');

    // Stock Levels
    Route::get('inventory/levels', [InventoryLevelController::class, 'index'])->name('inventory.levels.index');
    Route::get('inventory/levels/{warehouse?}', [InventoryLevelController::class, 'warehouseStock'])->name('inventory.levels.warehouse');
    
    // Stock Transfer
    Route::get('inventory/transfer/create', [InventoryTransactionController::class, 'createTransfer'])->name('inventory.transfer.create');
    Route::post('inventory/transfer', [InventoryTransactionController::class, 'transfer'])->name('inventory.transfer.store');
    
    // Stock Adjustment
    Route::get('inventory/adjustment/create', [InventoryTransactionController::class, 'createAdjustment'])->name('inventory.adjustment.create');
    Route::post('inventory/adjustment', [InventoryTransactionController::class, 'adjust'])->name('inventory.adjustment.store');
    // Additional PO routes
    // Route::post('/purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
    Route::post('/purchase-orders/{purchaseOrder}/mark-ordered', [PurchaseOrderController::class, 'markAsOrdered'])->name('purchase-orders.mark-ordered');
    // Route::post('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
    
    // Receiving routes
    Route::get('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
    Route::post('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'processReceive'])->name('purchase-orders.process-receive');    

    // Route::prefix('suppliers')->name('suppliers.')->group(function () {
    //     Route::get('/', function () {
    //         return inertia('Suppliers/Index');
    //     })->name('index');
    //     Route::get('/create', function () {
    //         return inertia('Suppliers/Create');
    //     })->name('create');
    // });
    
    // Sales Management
    
    // Payments

// POS page
    Route::get('/pos', [SaleController::class, 'pos'])->name('pos');
    
    // Sales management
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::get('/sales/{sale}/receipt', [SaleController::class, 'printReceipt'])->name('sales.receipt');
    
    // Payments
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    
    // Customer management (form submissions)
    Route::post('/customers/store', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    
    // Sale processing
    Route::post('/sales/process', [SaleController::class, 'processSale'])->name('sales.process');
    Route::post('/sales/check-product', [SaleController::class, 'checkProduct'])->name('sales.check-product');
    Route::post('/sales/search-products', [SaleController::class, 'searchProducts'])->name('sales.search-products');

    Route::resource('suppliers', SupplierController::class);
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', function () {
            return inertia('Customers/Index');
        })->name('index');
        Route::get('/create', function () {
            return inertia('Customers/Create');
        })->name('create');
    });
    
    Route::prefix('sales')->name('sales.')->group(function () {
        // Route::get('/', function () {
        //     return inertia('Sales/Index');
        // })->name('index');
        Route::get('/create', function () {
            return inertia('Sales/Create');
        })->name('create');
        Route::get('/{id}', function ($id) {
            return inertia('Sales/Show', ['id' => $id]);
        })->name('show');
    });
    
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', function () {
            return inertia('Orders/Index');
        })->name('index');
        Route::get('/create', function () {
            return inertia('Orders/Create');
        })->name('create');
    });
    
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/sales', function () {
            return inertia('Reports/Sales');
        })->name('sales');
        Route::get('/inventory', function () {
            return inertia('Reports/Inventory');
        })->name('inventory');
        Route::get('/financial', function () {
            return inertia('Reports/Financial');
        })->name('financial');
        Route::get('/customers', function () {
            return inertia('Reports/Customers');
        })->name('customers');
    });
    
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', function () {
            return inertia('Settings/Index');
        })->name('index');
        Route::get('/taxes', function () {
            return inertia('Settings/Taxes');
        })->name('taxes');
        Route::get('/discounts', function () {
            return inertia('Settings/Discounts');
        })->name('discounts');
    });
});

require __DIR__.'/auth.php';