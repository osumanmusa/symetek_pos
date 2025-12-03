<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    // POS endpoints
    Route::get('/pos/product', [SaleController::class, 'getProduct']);
    Route::get('/products/search', [SaleController::class, 'searchProducts']);
    Route::post('/sales', [SaleController::class, 'store']);
    Route::post('/sales/{sale}/refund', [SaleController::class, 'refund']);
    
    // Customer endpoints
    Route::post('/customers', [CustomerController::class, 'store']);
    Route::get('/customers', [CustomerController::class, 'index']);
    
    // Product endpoints
    Route::get('/products/stock/{product}', [ProductController::class, 'checkStock']);
});