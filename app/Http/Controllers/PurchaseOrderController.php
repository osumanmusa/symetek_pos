<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{
    // Validate filter inputs
    $validated = $request->validate([
        'search' => 'nullable|string|max:255',
        'status' => 'nullable|string|in:draft,pending,approved,ordered,partially_received,received,cancelled',
        'payment_status' => 'nullable|string|in:pending,partial,paid,overdue',
        'supplier_id' => 'nullable|exists:suppliers,id',
        'date_from' => 'nullable|date',
        'date_to' => 'nullable|date|after_or_equal:date_from',
        'sort_by' => 'nullable|in:po_number,order_date,total_amount,created_at',
        'sort_order' => 'nullable|in:asc,desc',
    ]);

    $query = PurchaseOrder::with(['supplier', 'user'])
        ->withCount('items');

    // Apply filters
    if (!empty($validated['search'])) {
        $search = $validated['search'];
        $query->where(function($q) use ($search) {
            $q->where('po_number', 'like', "%{$search}%")
              ->orWhereHas('supplier', function($q) use ($search) {
                  $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
              });
        });
    }

    if (!empty($validated['status'])) {
        $query->where('status', $validated['status']);
    }

    if (!empty($validated['payment_status'])) {
        $query->where('payment_status', $validated['payment_status']);
    }

    if (!empty($validated['supplier_id'])) {
        $query->where('supplier_id', $validated['supplier_id']);
    }

    // Date filtering - safe approach
    if (!empty($validated['date_from'])) {
        $query->where('order_date', '>=', $validated['date_from']);
    }

    if (!empty($validated['date_to'])) {
        $query->where('order_date', '<=', $validated['date_to']);
    }

    // Apply sorting
    $sortBy = $validated['sort_by'] ?? 'created_at';
    $sortOrder = $validated['sort_order'] ?? 'desc';
    $query->orderBy($sortBy, $sortOrder);

    $purchaseOrders = $query->paginate(15);

    // Calculate stats
    $stats = [
        'total' => PurchaseOrder::count(),
        'to_order' => PurchaseOrder::whereIn('status', ['draft', 'pending', 'approved'])->count(),
        'awaiting_delivery' => PurchaseOrder::where('status', 'ordered')->count(),
        'received' => PurchaseOrder::where('status', 'received')->count(),
        'overdue' => PurchaseOrder::where('status', 'ordered')
            ->whereDate('expected_delivery_date', '<', now()->toDateString())
            ->count(),
    ];

    $suppliers = Supplier::where('is_active', true)->get(['id', 'name']);

    return Inertia::render('PurchaseOrders/index', [
        'purchaseOrders' => $purchaseOrders,
        'stats' => $stats,
        'suppliers' => $suppliers,
        'filters' => $validated
    ]);
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->get();
        $products = Product::where('is_active', true)
            ->with('category')
            ->orderBy('name')
            ->get();

        return Inertia::render('PurchaseOrders/Create', [
            'suppliers' => $suppliers,
            'products' => $products,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string|max:1000',
            'shipping_address' => 'nullable|string|max:500',
            'shipping_cost' => 'nullable|numeric|min:0|max:999999.99',
            'tax_amount' => 'nullable|numeric|min:0|max:999999.99',
            'discount_amount' => 'nullable|numeric|min:0|max:999999.99',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01|max:999999.99',
            'items.*.unit_cost' => 'required|numeric|min:0|max:999999.99',
        ]);

        DB::beginTransaction();

        try {
            $purchaseOrder = PurchaseOrder::create([
                'po_number' => PurchaseOrder::generatePONumber(),
                'supplier_id' => $validated['supplier_id'],
                'user_id' => Auth::id(),
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'shipping_address' => $validated['shipping_address'] ?? null,
                'shipping_cost' => $validated['shipping_cost'] ?? 0,
                'tax_amount' => $validated['tax_amount'] ?? 0,
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'subtotal' => 0,
                'total_amount' => 0,
            ]);

            $subtotal = 0;

            foreach ($validated['items'] as $item) {
                $totalCost = $item['quantity'] * $item['unit_cost'];
                $subtotal += $totalCost;

                $purchaseOrder->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $totalCost,
                    'received_quantity' => 0,
                    'damaged_quantity' => 0,
                    'returned_quantity' => 0,
                ]);
            }

            $totalAmount = $subtotal + $purchaseOrder->tax_amount + $purchaseOrder->shipping_cost - $purchaseOrder->discount_amount;

            $purchaseOrder->update([
                'subtotal' => $subtotal,
                'total_amount' => $totalAmount,
                'balance_due' => $totalAmount,
            ]);

            // Log activity
            activity()
                ->performedOn($purchaseOrder)
                ->causedBy(Auth::user())
                ->withProperties([
                    'items_count' => count($validated['items']),
                    'total_amount' => $totalAmount
                ])
                ->log('created purchase order');

            DB::commit();

            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('success', 'Purchase order created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Purchase order creation failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'supplier_id' => $validated['supplier_id'] ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create purchase order: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier',
            'user',
            'items.product.category',
            'activities' => function ($query) {
                $query->latest()->take(20)->with('causer');
            }
        ]);

        // Calculate receiving stats
        $itemsStats = [
            'total_items' => $purchaseOrder->items->count(),
            'fully_received' => $purchaseOrder->items->where('received_quantity', '>=', DB::raw('quantity'))->count(),
            'partially_received' => $purchaseOrder->items->where('received_quantity', '>', 0)
                ->where('received_quantity', '<', DB::raw('quantity'))->count(),
            'not_received' => $purchaseOrder->items->where('received_quantity', 0)->count(),
        ];

        return Inertia::render('PurchaseOrders/Show', [
            'purchaseOrder' => $purchaseOrder,
            'itemsStats' => $itemsStats,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PurchaseOrder $purchaseOrder)
    {
        // Check if editable
        if (!in_array($purchaseOrder->status, ['draft', 'pending'])) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'Only draft or pending purchase orders can be edited.');
        }

        $purchaseOrder->load(['items.product']);
        $suppliers = Supplier::where('is_active', true)->get();
        $products = Product::where('is_active', true)->with('category')->get();

        return Inertia::render('PurchaseOrders/Edit', [
            'purchaseOrder' => $purchaseOrder,
            'suppliers' => $suppliers,
            'products' => $products,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        // Check if editable
        if (!in_array($purchaseOrder->status, ['draft', 'pending'])) {
            return redirect()->back()
                ->with('error', 'Only draft or pending purchase orders can be edited.');
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string|max:1000',
            'shipping_address' => 'nullable|string|max:500',
            'shipping_cost' => 'nullable|numeric|min:0|max:999999.99',
            'tax_amount' => 'nullable|numeric|min:0|max:999999.99',
            'discount_amount' => 'nullable|numeric|min:0|max:999999.99',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01|max:999999.99',
            'items.*.unit_cost' => 'required|numeric|min:0|max:999999.99',
        ]);

        DB::beginTransaction();

        try {
            $oldStatus = $purchaseOrder->status;
            
            // Update purchase order
            $purchaseOrder->update([
                'supplier_id' => $validated['supplier_id'],
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'shipping_address' => $validated['shipping_address'] ?? null,
                'shipping_cost' => $validated['shipping_cost'] ?? 0,
                'tax_amount' => $validated['tax_amount'] ?? 0,
                'discount_amount' => $validated['discount_amount'] ?? 0,
            ]);

            // Delete existing items
            $purchaseOrder->items()->delete();

            // Add new items
            $subtotal = 0;
            $itemsCount = 0;
            
            foreach ($validated['items'] as $item) {
                $totalCost = $item['quantity'] * $item['unit_cost'];
                $subtotal += $totalCost;
                $itemsCount++;

                $purchaseOrder->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $totalCost,
                    'received_quantity' => 0,
                    'damaged_quantity' => 0,
                    'returned_quantity' => 0,
                ]);
            }

            $totalAmount = $subtotal + $purchaseOrder->tax_amount + $purchaseOrder->shipping_cost - $purchaseOrder->discount_amount;

            $purchaseOrder->update([
                'subtotal' => $subtotal,
                'total_amount' => $totalAmount,
                'balance_due' => $totalAmount - $purchaseOrder->amount_paid,
            ]);

            // Update payment status if needed
            $this->updatePaymentStatus($purchaseOrder);

            // Log activity
            activity()
                ->performedOn($purchaseOrder)
                ->causedBy(Auth::user())
                ->withProperties([
                    'items_count' => $itemsCount,
                    'old_status' => $oldStatus,
                    'new_status' => $purchaseOrder->status,
                    'total_amount' => $totalAmount
                ])
                ->log('updated purchase order');

            DB::commit();

            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('success', 'Purchase order updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Purchase order update failed: ' . $e->getMessage(), [
                'po_id' => $purchaseOrder->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update purchase order: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if (!in_array($purchaseOrder->status, ['draft', 'pending', 'cancelled'])) {
            return redirect()->back()
                ->with('error', 'Only draft, pending, or cancelled purchase orders can be deleted.');
        }

        // Check if any items have been received
        $hasReceivedItems = $purchaseOrder->items()->where('received_quantity', '>', 0)->exists();
        if ($hasReceivedItems) {
            return redirect()->back()
                ->with('error', 'Cannot delete purchase order with received items.');
        }

        DB::beginTransaction();

        try {
            // Log before deletion
            activity()
                ->performedOn($purchaseOrder)
                ->causedBy(Auth::user())
                ->withProperties([
                    'po_number' => $purchaseOrder->po_number,
                    'supplier' => $purchaseOrder->supplier->name,
                    'total_amount' => $purchaseOrder->total_amount,
                    'status' => $purchaseOrder->status
                ])
                ->log('deleted purchase order');

            $purchaseOrder->delete();

            DB::commit();

            return redirect()->route('purchase-orders.index')
                ->with('success', 'Purchase order deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to delete purchase order: ' . $e->getMessage());
        }
    }

    /**
     * Mark purchase order as approved
     */
    public function approve(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending purchase orders can be approved.');
        }

        DB::beginTransaction();

        try {
            $oldStatus = $purchaseOrder->status;
            
            $purchaseOrder->update(['status' => 'approved']);

            // Log the approval
            activity()
                ->performedOn($purchaseOrder)
                ->causedBy(Auth::user())
                ->withProperties([
                    'old_status' => $oldStatus,
                    'new_status' => 'approved'
                ])
                ->log('approved purchase order');

            DB::commit();

            return redirect()->back()
                ->with('success', 'Purchase order approved successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to approve purchase order: ' . $e->getMessage());
        }
    }

    /**
     * Mark purchase order as ordered
     */
    public function markAsOrdered(PurchaseOrder $purchaseOrder)
    {
        // Only allow from approved or pending status
        if (!in_array($purchaseOrder->status, ['approved', 'pending', 'draft'])) {
            return redirect()->back()
                ->with('error', 'Only draft, pending, or approved purchase orders can be marked as ordered.');
        }

        // Check if there are items
        if ($purchaseOrder->items()->count() === 0) {
            return redirect()->back()
                ->with('error', 'Cannot mark empty purchase order as ordered.');
        }

        DB::beginTransaction();

        try {
            $oldStatus = $purchaseOrder->status;
            
            // Update status and order date
            $purchaseOrder->update([
                'status' => 'ordered',
                'order_date' => now(),
            ]);

            // Log the ordering
            activity()
                ->performedOn($purchaseOrder)
                ->causedBy(Auth::user())
                ->withProperties([
                    'old_status' => $oldStatus,
                    'new_status' => 'ordered',
                    'ordered_date' => now()
                ])
                ->log('marked purchase order as ordered');

            DB::commit();

            return redirect()->back()
                ->with('success', 'Purchase order marked as ordered.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to mark purchase order as ordered: ' . $e->getMessage());
        }
    }

    /**
     * Quick action to approve and mark as ordered
     */
    public function approveAndOrder(PurchaseOrder $purchaseOrder)
    {
        // Only allow from pending or draft status
        if (!in_array($purchaseOrder->status, ['pending', 'draft'])) {
            return redirect()->back()
                ->with('error', 'Only draft or pending purchase orders can be approved and ordered.');
        }

        DB::beginTransaction();

        try {
            $oldStatus = $purchaseOrder->status;
            
            // Directly mark as ordered
            $purchaseOrder->update([
                'status' => 'ordered',
                'order_date' => now(),
            ]);

            // Log the approval and ordering
            activity()
                ->performedOn($purchaseOrder)
                ->causedBy(Auth::user())
                ->withProperties([
                    'old_status' => $oldStatus,
                    'new_status' => 'ordered',
                    'action' => 'approve_and_order'
                ])
                ->log('approved and marked purchase order as ordered');

            DB::commit();

            return redirect()->back()
                ->with('success', 'Purchase order approved and marked as ordered.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to process order: ' . $e->getMessage());
        }
    }

    /**
     * Mark purchase order as fully received
     */
    public function markAsFullyReceived(PurchaseOrder $purchaseOrder)
    {
        // Only allow from ordered or partially_received status
        if (!in_array($purchaseOrder->status, ['ordered', 'partially_received'])) {
            return redirect()->back()
                ->with('error', 'Only ordered or partially received purchase orders can be marked as fully received.');
        }

        // Check if all items have been received
        $allReceived = true;
        $items = $purchaseOrder->items;
        
        foreach ($items as $item) {
            if ($item->received_quantity < $item->quantity) {
                $allReceived = false;
                break;
            }
        }

        if (!$allReceived) {
            return redirect()->back()
                ->with('error', 'Cannot mark as fully received. Some items are still pending.');
        }

        DB::beginTransaction();

        try {
            $oldStatus = $purchaseOrder->status;
            
            // Update status and delivery date
            $purchaseOrder->update([
                'status' => 'received',
                'delivery_date' => now(),
            ]);

            // Recalculate payment status
            $this->updatePaymentStatus($purchaseOrder);

            // Log the full receipt
            activity()
                ->performedOn($purchaseOrder)
                ->causedBy(Auth::user())
                ->withProperties([
                    'old_status' => $oldStatus,
                    'new_status' => 'received',
                    'delivery_date' => now(),
                ])
                ->log('marked purchase order as fully received');

            DB::commit();

            return redirect()->back()
                ->with('success', 'Purchase order marked as fully received.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to mark purchase order as received: ' . $e->getMessage());
        }
    }

    /**
     * Cancel purchase order
     */
    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if (in_array($purchaseOrder->status, ['received', 'cancelled'])) {
            return redirect()->back()
                ->with('error', 'Cannot cancel a received or already cancelled purchase order.');
        }

        DB::beginTransaction();

        try {
            $oldStatus = $purchaseOrder->status;
            
            $purchaseOrder->update(['status' => 'cancelled']);

            // Log the cancellation
            activity()
                ->performedOn($purchaseOrder)
                ->causedBy(Auth::user())
                ->withProperties([
                    'old_status' => $oldStatus,
                    'new_status' => 'cancelled'
                ])
                ->log('cancelled purchase order');

            DB::commit();

            return redirect()->back()
                ->with('success', 'Purchase order cancelled successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to cancel purchase order: ' . $e->getMessage());
        }
    }

    /**
     * Show receive form
     */
    public function receive(PurchaseOrder $purchaseOrder)
    {
        if (!in_array($purchaseOrder->status, ['ordered', 'partially_received'])) {
            return redirect()->back()
                ->with('error', 'Only ordered or partially received purchase orders can receive items.');
        }

        $purchaseOrder->load(['items.product']);

        return Inertia::render('PurchaseOrders/Receive', [
            'purchaseOrder' => $purchaseOrder,
        ]);
    }

    /**
     * Process receiving items
     */
    public function processReceive(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!in_array($purchaseOrder->status, ['ordered', 'partially_received'])) {
            return redirect()->back()
                ->with('error', 'Only ordered or partially received purchase orders can receive items.');
        }

        $validated = $request->validate([
            'delivery_date' => 'required|date',
            'receiving_notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:purchase_order_items,id',
            'items.*.received' => 'required|numeric|min:0',
            'items.*.damaged' => 'nullable|numeric|min:0',
            'items.*.returned' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            $receivedItems = [];
            $totalReceived = 0;
            $totalDamaged = 0;
            $totalReturned = 0;
            $updatedProducts = [];

            foreach ($validated['items'] as $itemData) {
                $item = $purchaseOrder->items()->find($itemData['id']);
                
                $received = $itemData['received'] ?? 0;
                $damaged = $itemData['damaged'] ?? 0;
                $returned = $itemData['returned'] ?? 0;
                
                // Calculate total being processed
                $totalProcessed = $received + $damaged + $returned;
                
                // Check if we're not exceeding ordered quantity
                $alreadyProcessed = $item->received_quantity + $item->damaged_quantity + $item->returned_quantity;
                $remaining = $item->quantity - $alreadyProcessed;
                
                if ($totalProcessed > $remaining) {
                    throw new \Exception("Cannot process more than remaining quantity for product: {$item->product->name}. Remaining: {$remaining}, Trying to process: {$totalProcessed}");
                }
                
                // Update item quantities
                $item->received_quantity += $received;
                $item->damaged_quantity += $damaged;
                $item->returned_quantity += $returned;
                
                if (!empty($itemData['notes'])) {
                    $item->notes = $item->notes ? $item->notes . "\n" . $itemData['notes'] : $itemData['notes'];
                }
                
                $item->save();
                
                $totalReceived += $received;
                $totalDamaged += $damaged;
                $totalReturned += $returned;
                
                $receivedItems[] = [
                    'product' => $item->product->name,
                    'received' => $received,
                    'damaged' => $damaged,
                    'returned' => $returned
                ];
                
                // Track products for stock update
                if ($received > 0) {
                    $product = $item->product;
                    if (!isset($updatedProducts[$product->id])) {
                        $updatedProducts[$product->id] = [
                            'product' => $product,
                            'quantity' => $received
                        ];
                    } else {
                        $updatedProducts[$product->id]['quantity'] += $received;
                    }
                }
            }
            
            // Update product stocks
            foreach ($updatedProducts as $data) {
                $data['product']->increment('stock_quantity', $data['quantity']);
                
                // Log stock movement
                activity()
                    ->performedOn($data['product'])
                    ->causedBy(Auth::user())
                    ->withProperties([
                        'po_number' => $purchaseOrder->po_number,
                        'quantity' => $data['quantity'],
                        'reason' => 'purchase_order_receipt'
                    ])
                    ->log('updated stock from purchase order receipt');
            }
            
            // Update delivery date
            $purchaseOrder->delivery_date = $validated['delivery_date'];
            
            $oldStatus = $purchaseOrder->status;
            
            // Update purchase order status
            if ($this->isFullyReceived($purchaseOrder)) {
                $purchaseOrder->status = 'received';
            } else {
                $purchaseOrder->status = 'partially_received';
            }
            
            $purchaseOrder->save();
            
            // Log the receiving of items
            activity()
                ->performedOn($purchaseOrder)
                ->causedBy(Auth::user())
                ->withProperties([
                    'old_status' => $oldStatus,
                    'new_status' => $purchaseOrder->status,
                    'items_received' => $receivedItems,
                    'total_received' => $totalReceived,
                    'total_damaged' => $totalDamaged,
                    'total_returned' => $totalReturned,
                    'receiving_notes' => $validated['receiving_notes'],
                ])
                ->log('received items for purchase order');
            
            DB::commit();
            
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('success', 'Items received successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Purchase order receiving failed: ' . $e->getMessage(), [
                'po_id' => $purchaseOrder->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to receive items: ' . $e->getMessage());
        }
    }
    
    /**
     * Update payment for purchase order
     */
    public function updatePayment(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'amount_paid' => 'required|numeric|min:0|max:999999.99',
            'payment_method' => 'required|string|max:255',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        DB::beginTransaction();
        
        try {
            $oldAmountPaid = $purchaseOrder->amount_paid;
            $newAmountPaid = $validated['amount_paid'];
            
            // Update payment information
            $purchaseOrder->update([
                'amount_paid' => $newAmountPaid,
                'payment_method' => $validated['payment_method'],
                'payment_date' => $validated['payment_date'],
            ]);
            
            // Recalculate balance
            $purchaseOrder->balance_due = $purchaseOrder->total_amount - $newAmountPaid;
            $this->updatePaymentStatus($purchaseOrder);
            
            // Log the payment
            activity()
                ->performedOn($purchaseOrder)
                ->causedBy(Auth::user())
                ->withProperties([
                    'old_amount_paid' => $oldAmountPaid,
                    'new_amount_paid' => $newAmountPaid,
                    'payment_method' => $validated['payment_method'],
                    'payment_date' => $validated['payment_date'],
                    'balance_due' => $purchaseOrder->balance_due,
                    'payment_status' => $purchaseOrder->payment_status
                ])
                ->log('updated payment for purchase order');
            
            DB::commit();
            
            return redirect()->back()
                ->with('success', 'Payment updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update payment: ' . $e->getMessage());
        }
    }

    /**
     * Mark purchase order as paid
     */
    public function markAsPaid(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->payment_status === 'paid') {
            return redirect()->back()
                ->with('error', 'Purchase order is already marked as paid.');
        }
        
        DB::beginTransaction();
        
        try {
            $oldAmountPaid = $purchaseOrder->amount_paid;
            
            // Mark as fully paid
            $purchaseOrder->update([
                'amount_paid' => $purchaseOrder->total_amount,
                'payment_date' => now(),
                'payment_method' => $purchaseOrder->payment_method ?: 'cash',
                'balance_due' => 0,
            ]);
            
            $this->updatePaymentStatus($purchaseOrder);
            
            // Log the payment
            activity()
                ->performedOn($purchaseOrder)
                ->causedBy(Auth::user())
                ->withProperties([
                    'old_amount_paid' => $oldAmountPaid,
                    'new_amount_paid' => $purchaseOrder->total_amount,
                    'balance_due' => 0,
                    'payment_status' => 'paid'
                ])
                ->log('marked purchase order as fully paid');
            
            DB::commit();
            
            return redirect()->back()
                ->with('success', 'Purchase order marked as fully paid.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to mark as paid: ' . $e->getMessage());
        }
    }

    /**
     * Print purchase order
     */
/**
 * Print purchase order
 */
public function print(PurchaseOrder $purchaseOrder)
{
    $purchaseOrder->load([
        'supplier',
        'user',
        'items.product.category'
    ]);

    return Inertia::render('PurchaseOrders/Print', [
        'purchaseOrder' => $purchaseOrder,
        'company' => [
            'name' => config('app.name', 'Your Company'),
            'address' => '123 Business Street, Accra, Ghana',
            'phone' => '+233 123 456 789',
            'email' => 'info@company.com',
            'logo' => null, // You can add logo path here
        ]
    ]);
}

/**
 * Export purchase orders
 */
public function export(Request $request)
{
    // Validate filter inputs
    $validated = $request->validate([
        'search' => 'nullable|string|max:255',
        'status' => 'nullable|string|in:draft,pending,approved,ordered,partially_received,received,cancelled',
        'payment_status' => 'nullable|string|in:pending,partial,paid,overdue',
        'supplier_id' => 'nullable|exists:suppliers,id',
        'date_from' => 'nullable|date',
        'date_to' => 'nullable|date',
        'sort_by' => 'nullable|in:po_number,order_date,total_amount,created_at',
        'sort_order' => 'nullable|in:asc,desc',
    ]);

    $query = PurchaseOrder::with(['supplier', 'user']);

    // Apply the same filters as index method
    if (!empty($validated['search'])) {
        $search = $validated['search'];
        $query->where(function($q) use ($search) {
            $q->where('po_number', 'like', "%{$search}%")
              ->orWhereHas('supplier', function($q) use ($search) {
                  $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
              });
        });
    }

    if (!empty($validated['status'])) {
        $query->where('status', $validated['status']);
    }

    if (!empty($validated['payment_status'])) {
        $query->where('payment_status', $validated['payment_status']);
    }

    if (!empty($validated['supplier_id'])) {
        $query->where('supplier_id', $validated['supplier_id']);
    }

    if (!empty($validated['date_from'])) {
        $query->where('order_date', '>=', $validated['date_from']);
    }

    if (!empty($validated['date_to'])) {
        $query->where('order_date', '<=', $validated['date_to']);
    }

    // Apply sorting
    $sortBy = $validated['sort_by'] ?? 'created_at';
    $sortOrder = $validated['sort_order'] ?? 'desc';
    $query->orderBy($sortBy, $sortOrder);

    $purchaseOrders = $query->get();

    // Generate CSV
    $csvData = $this->generateCsv($purchaseOrders);

    // Return CSV file
    return response()->streamDownload(function () use ($csvData) {
        echo $csvData;
    }, 'purchase_orders_' . date('Y-m-d_H-i-s') . '.csv', [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="purchase_orders_' . date('Y-m-d_H-i-s') . '.csv"',
    ]);
}

/**
 * Generate CSV data
 */
private function generateCsv($purchaseOrders)
{
    $output = fopen('php://temp', 'r+');
    
    // CSV headers
    $headers = [
        'PO Number',
        'Supplier',
        'Order Date',
        'Expected Delivery',
        'Status',
        'Payment Status',
        'Subtotal',
        'Shipping',
        'Tax',
        'Discount',
        'Total Amount',
        'Amount Paid',
        'Balance Due',
        'Created By',
        'Created At'
    ];
    
    fputcsv($output, $headers);
    
    // Add data rows
    foreach ($purchaseOrders as $po) {
        fputcsv($output, [
            $po->po_number,
            $po->supplier?->name,
            $po->order_date,
            $po->expected_delivery_date,
            ucfirst(str_replace('_', ' ', $po->status)),
            ucfirst($po->payment_status),
            number_format($po->subtotal, 2),
            number_format($po->shipping_cost, 2),
            number_format($po->tax_amount, 2),
            number_format($po->discount_amount, 2),
            number_format($po->total_amount, 2),
            number_format($po->amount_paid, 2),
            number_format($po->balance_due, 2),
            $po->user?->name,
            $po->created_at->format('Y-m-d H:i:s')
        ]);
    }
    
    rewind($output);
    $csvData = stream_get_contents($output);
    fclose($output);
    
    return $csvData;
}

/**
 * Generate PDF for purchase order
 */
public function pdf(PurchaseOrder $purchaseOrder)
{
    // If you want PDF generation, you can use DomPDF or similar
    // For now, we'll just return the print view
    
    $purchaseOrder->load([
        'supplier',
        'user',
        'items.product.category'
    ]);

    return Inertia::render('PurchaseOrders/Print', [
        'purchaseOrder' => $purchaseOrder,
        'pdf' => true, // Flag to indicate PDF view
    ]);
}

    /**
     * Helper method to check if fully received
     */
    private function isFullyReceived(PurchaseOrder $purchaseOrder): bool
    {
        foreach ($purchaseOrder->items as $item) {
            if ($item->received_quantity < $item->quantity) {
                return false;
            }
        }
        return true;
    }

    /**
     * Helper method to update payment status
     */
    private function updatePaymentStatus(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->balance_due <= 0) {
            $paymentStatus = 'paid';
        } elseif ($purchaseOrder->amount_paid > 0) {
            $paymentStatus = 'partial';
        } else {
            $paymentStatus = 'pending';
        }
        
        // Check if overdue
        if ($paymentStatus !== 'paid' && $purchaseOrder->delivery_date && $purchaseOrder->delivery_date->addDays(30) < now()) {
            $paymentStatus = 'overdue';
        }
        
        $purchaseOrder->update(['payment_status' => $paymentStatus]);
    }
}