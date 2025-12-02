<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseOrderController;

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
        Route::get('/', function () {
            return inertia('Inventory/Index');
        })->name('index');
        Route::get('/adjust', function () {
            return inertia('Inventory/Adjust');
        })->name('adjust');
    });
        // Purchase Orders
    
    // Purchase Orders
    Route::resource('purchase-orders', PurchaseOrderController::class);
      
   Route::post('/purchase-orders/{purchaseOrder}/order', [PurchaseOrderController::class, 'markAsOrdered'])
        ->name('purchase-orders.order');
    
    Route::post('/purchase-orders/{purchaseOrder}/approve-and-order', [PurchaseOrderController::class, 'approveAndOrder'])
        ->name('purchase-orders.approve-and-order');
    
    Route::post('/purchase-orders/{purchaseOrder}/receive-full', [PurchaseOrderController::class, 'markAsReceived'])
        ->name('purchase-orders.receive-full');
    
    Route::get('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])
        ->name('purchase-orders.receive');
    
    Route::post('/purchase-orders/{purchaseOrder}/process-receive', [PurchaseOrderController::class, 'processReceive'])
        ->name('purchase-orders.process-receive');
    
    Route::post('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])
        ->name('purchase-orders.cancel');
    // Additional PO routes
    Route::post('/purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
    Route::post('/purchase-orders/{purchaseOrder}/mark-ordered', [PurchaseOrderController::class, 'markAsOrdered'])->name('purchase-orders.mark-ordered');
    Route::post('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
    
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
        Route::get('/', function () {
            return inertia('Sales/Index');
        })->name('index');
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