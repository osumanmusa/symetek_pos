<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Warehouse;
use App\Models\InventoryLevel;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\InventoryTransaction;
class SaleController extends Controller
{
    protected $inventoryService;
    
    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }
    
    /**
     * Display POS interface
     */
  public function pos()
{
    // Get products with inventory information
    $products = Product::active()
        ->with(['category', 'inventoryLevels' => function($query) {
            $query->where('warehouse_id', Warehouse::getDefault()?->id);
        }])
        ->get()
        ->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'selling_price' => $product->selling_price,
                'cost_price' => $product->cost_price,
                'available_stock' => $product->inventoryLevels->sum('quantity_available'),
                'unit' => $product->unit,
                'is_taxable' => $product->is_taxable,
                'category' => $product->category,
                'image' => $product->images ? json_decode($product->images)[0] ?? null : null,
            ];
        });
    
    return Inertia::render('Sales/Pos', [
        'customers' => Customer::orderBy('name')->get(['id', 'name', 'phone', 'email']),
        'paymentMethods' => Sale::getPaymentMethods(),
        'defaultWarehouse' => Warehouse::getDefault(),
        'taxRate' => config('app.tax_rate', 16),
        'products' => $products, // Add this line
    ]);
}
    
    /**
     * Process a new sale
     */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'nullable|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'change_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
            'warehouse_id' => 'nullable|exists:warehouses,id',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Generate invoice number
            $invoiceNumber = Sale::generateInvoiceNumber();
            
            // Create sale
            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $validated['customer_id'] ?? null,
                'user_id' => auth()->id(),
                'subtotal' => $validated['subtotal'],
                'tax_amount' => $validated['tax_amount'] ?? 0,
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'total_amount' => $validated['total_amount'],
                'amount_paid' => $validated['amount_paid'],
                'change_amount' => $validated['change_amount'] ?? 0,
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
                'is_completed' => true,
            ]);
            
            // Add sale items and update inventory
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                
                // Check stock availability
                if ($validated['warehouse_id']) {
                    $inventoryLevel = InventoryLevel::where('product_id', $product->id)
                        ->where('warehouse_id', $validated['warehouse_id'])
                        ->first();
                    
                    if (!$inventoryLevel || $inventoryLevel->quantity_available < $item['quantity']) {
                        throw new \Exception("Insufficient stock for {$product->name}");
                    }
                }
                
                // Create sale item
                $saleItem = SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'] ?? $product->selling_price,
                    'total_price' => ($item['price'] ?? $product->selling_price) * $item['quantity'],
                    'cost_price' => $product->cost_price,
                ]);
                
                // Update inventory if warehouse specified
                if ($validated['warehouse_id']) {
                    // Create inventory transaction
                    InventoryTransaction::create([
                        'product_id' => $product->id,
                        'transaction_type' => InventoryTransaction::TYPE_SALE,
                        'quantity' => $item['quantity'],
                        'unit_cost' => $product->cost_price,
                        'total_cost' => $item['quantity'] * $product->cost_price,
                        'warehouse_id' => $validated['warehouse_id'],
                        'user_id' => auth()->id(),
                        'reference_number' => $invoiceNumber,
                        'notes' => "POS Sale #{$invoiceNumber}",
                        'metadata' => [
                            'sale_id' => $sale->id,
                            'sale_item_id' => $saleItem->id,
                        ],
                    ]);
                    
                    // Update inventory level
                    if ($inventoryLevel) {
                        $inventoryLevel->decreaseOnHand($item['quantity']);
                    }
                }
            }
            
            // Create payment record
            if ($validated['amount_paid'] > 0) {
                Payment::create([
                    'sale_id' => $sale->id,
                    'customer_id' => $validated['customer_id'] ?? null,
                    'payment_date' => now(),
                    'amount' => $validated['amount_paid'],
                    'payment_method' => $validated['payment_method'],
                    'reference_number' => $invoiceNumber,
                    'received_by' => auth()->id(),
                ]);
            }
            
            // Update customer stats if customer exists
            if ($sale->customer) {
                $sale->customer->updatePurchaseStats($sale);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Sale completed successfully',
                'sale' => $sale->load(['items.product', 'customer', 'user']),
                'invoice_number' => $invoiceNumber,
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Sale failed: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    
    /**
     * Get product details by barcode/SKU
     */
    public function getProduct(Request $request)
    {
        $search = $request->input('search');
        
        $product = Product::where('sku', $search)
            ->orWhere('barcode', $search)
            ->orWhere('id', $search)
            ->active()
            ->with(['category'])
            ->first();
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }
        
        // Get available stock
        $warehouseId = $request->input('warehouse_id');
        $availableStock = $this->inventoryService->getAvailableStock($product->id, $warehouseId);
        
        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'selling_price' => $product->selling_price,
                'cost_price' => $product->cost_price,
                'available_stock' => $availableStock,
                'unit' => $product->unit,
                'is_taxable' => $product->is_taxable,
                'category' => $product->category,
            ],
        ]);
    }
    
    /**
     * Search products
     */
    public function searchProducts(Request $request)
    {
        $search = $request->input('search', '');
        
        $products = Product::where('name', 'like', "%{$search}%")
            ->orWhere('sku', 'like', "%{$search}%")
            ->orWhere('barcode', 'like', "%{$search}%")
            ->active()
            ->with(['category'])
            ->limit(20)
            ->get();
        
        return response()->json($products);
    }
    
    /**
     * List all sales
     */
// In SaleController.php, update index() method:
public function index(Request $request)
{
    $query = Sale::with(['customer', 'user', 'items.product'])
        ->latest();
    
    // ... existing filter code ...
    
    $sales = $query->paginate(50);
    
    // Enhanced statistics
    $today = now()->toDateString();
    $stats = [
        'today_sales' => Sale::whereDate('created_at', $today)
            ->completed()
            ->sum('total_amount'),
        'month_sales' => Sale::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->completed()
            ->sum('total_amount'),
        'total_sales' => Sale::completed()->count(),
        'customers_today' => Sale::whereDate('created_at', $today)
            ->distinct('customer_id')
            ->count('customer_id'),
    ];
    
    // CSV Export
    if ($request->has('export') && $request->input('export') === 'csv') {
        return $this->exportSalesToCSV($query->get());
    }
    
    return Inertia::render('Sales/index', [
        'sales' => $sales,
        'filters' => $request->only(['search', 'start_date', 'end_date', 'status', 'payment_method']),
        'stats' => $stats,
        'paymentMethods' => Sale::getPaymentMethods(),
    ]);
}

private function exportSalesToCSV($sales)
{
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="sales-' . date('Y-m-d') . '.csv"',
    ];

    $callback = function() use ($sales) {
        $file = fopen('php://output', 'w');
        
        // Add headers
        fputcsv($file, [
            'Invoice Number',
            'Date',
            'Customer',
            'Phone',
            'Items Count',
            'Subtotal',
            'Tax',
            'Discount',
            'Total Amount',
            'Amount Paid',
            'Change',
            'Payment Method',
            'Cashier',
        ]);
        
        // Add data rows
        foreach ($sales as $sale) {
            fputcsv($file, [
                $sale->invoice_number,
                $sale->created_at,
                $sale->customer?->name,
                $sale->customer?->phone,
                $sale->items()->count(),
                $sale->subtotal,
                $sale->tax_amount,
                $sale->discount_amount,
                $sale->total_amount,
                $sale->amount_paid,
                $sale->change_amount,
                $sale->payment_method,
                $sale->user?->name,
            ]);
        }
        
        fclose($file);
    };
    
    return response()->stream($callback, 200, $headers);
}
    
    /**
     * Show sale details
     */
    public function show(Sale $sale)
    {
        $sale->load(['customer', 'user', 'items.product', 'payments']);
        
        return Inertia::render('Sales/Show', [
            'sale' => $sale,
        ]);
    }
    
    /**
     * Print receipt
     */
    public function printReceipt($id)
    {
        $sale = Sale::with(['customer', 'items.product', 'user'])->findOrFail($id);
        
        return Inertia::render('Sales/Receipt', [
            'sale' => $sale,
            'company' => [
                'name' => config('app.company_name', 'Your Business'),
                'address' => config('app.company_address', ''),
                'phone' => config('app.company_phone', ''),
                'email' => config('app.company_email', ''),
            ],
        ]);
    }
    
    /**
     * Process refund/return
     */
    public function refund(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:sale_items,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'reason' => 'required|string',
            'refund_amount' => 'required|numeric|min:0',
            'refund_method' => 'required|string',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Create return sale
            $returnSale = Sale::create([
                'invoice_number' => 'RET-' . $sale->invoice_number,
                'customer_id' => $sale->customer_id,
                'user_id' => auth()->id(),
                'subtotal' => $validated['refund_amount'],
                'total_amount' => -$validated['refund_amount'], // Negative amount for return
                'amount_paid' => -$validated['refund_amount'],
                'payment_method' => $validated['refund_method'],
                'notes' => "Return/Refund: " . $validated['reason'],
                'is_completed' => true,
            ]);
            
            foreach ($validated['items'] as $item) {
                $saleItem = SaleItem::find($item['id']);
                $product = $saleItem->product;
                
                // Create return item
                SaleItem::create([
                    'sale_id' => $returnSale->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => -$saleItem->unit_price, // Negative price
                    'total_price' => -($saleItem->unit_price * $item['quantity']),
                    'cost_price' => $product->cost_price,
                ]);
                
                // Restore inventory
                if ($sale->warehouse_id) {
                    $this->inventoryService->processTransaction([
                        'product_id' => $product->id,
                        'transaction_type' => \App\Models\InventoryTransaction::TYPE_RETURN,
                        'quantity' => $item['quantity'],
                        'unit_cost' => $product->cost_price,
                        'warehouse_id' => $sale->warehouse_id,
                        'user_id' => auth()->id(),
                        'reference_number' => $returnSale->invoice_number,
                        'notes' => "Return from sale #{$sale->invoice_number}",
                    ]);
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Refund processed successfully',
                'return_sale' => $returnSale,
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Refund failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}