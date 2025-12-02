<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with(['supplier', 'user'])
            ->withCount('items')
            ->latest()
            ->paginate(10);
            
        // Calculate stats for the dashboard
        $stats = [
            'total' => PurchaseOrder::count(),
            'to_order' => PurchaseOrder::whereIn('status', ['draft', 'pending', 'approved'])->count(),
            'awaiting_delivery' => PurchaseOrder::where('status', 'ordered')->count(),
            'received' => PurchaseOrder::where('status', 'received')->count(),
        ];
            
        return Inertia::render('PurchaseOrders/index', [
            'purchaseOrders' => $purchaseOrders,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->get();
        $products = Product::where('is_active', true)->with('category')->get();
        
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
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'shipping_cost' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);
        
        DB::beginTransaction();
        
        try {
            $purchaseOrder = PurchaseOrder::create([
                'po_number' => PurchaseOrder::generatePONumber(),
                'supplier_id' => $request->supplier_id,
                'user_id' => auth()->id(),
                'order_date' => $request->order_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'status' => 'draft',
                'notes' => $request->notes,
                'shipping_address' => $request->shipping_address,
                'shipping_cost' => $request->shipping_cost ?? 0,
                'tax_amount' => $request->tax_amount ?? 0,
                'discount_amount' => $request->discount_amount ?? 0,
                'subtotal' => 0,
                'total_amount' => 0,
            ]);
            
            $subtotal = 0;
            
            foreach ($request->items as $item) {
                $totalCost = $item['quantity'] * $item['unit_cost'];
                $subtotal += $totalCost;
                
                $purchaseOrder->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $totalCost,
                ]);
            }
            
            $purchaseOrder->subtotal = $subtotal;
            $purchaseOrder->total_amount = $subtotal + 
                                         $purchaseOrder->tax_amount + 
                                         $purchaseOrder->shipping_cost - 
                                         $purchaseOrder->discount_amount;
            $purchaseOrder->balance_due = $purchaseOrder->total_amount;
            $purchaseOrder->save();
            
            // Log the creation
            activity()
                ->performedOn($purchaseOrder)
                ->causedBy(auth()->user())
                ->withProperties(['items_count' => count($request->items)])
                ->log('created purchase order');
            
            DB::commit();
            
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('success', 'Purchase order created successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
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
            'items.product',
            'activities.causer'
        ]);
        
        return Inertia::render('PurchaseOrders/Show', [
            'purchaseOrder' => $purchaseOrder,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PurchaseOrder $purchaseOrder)
    {
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
        if (!in_array($purchaseOrder->status, ['draft', 'pending'])) {
            return redirect()->back()
                ->with('error', 'Only draft or pending purchase orders can be edited.');
        }
        
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'shipping_cost' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Log changes before update
            $oldStatus = $purchaseOrder->status;
            
            // Update purchase order
            $purchaseOrder->update([
                'supplier_id' => $request->supplier_id,
                'order_date' => $request->order_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'notes' => $request->notes,
                'shipping_address' => $request->shipping_address,
                'shipping_cost' => $request->shipping_cost ?? 0,
                'tax_amount' => $request->tax_amount ?? 0,
                'discount_amount' => $request->discount_amount ?? 0,
            ]);
            
            // Delete existing items
            $purchaseOrder->items()->delete();
            
            // Add new items
            $subtotal = 0;
            $itemsCount = 0;
            foreach ($request->items as $item) {
                $totalCost = $item['quantity'] * $item['unit_cost'];
                $subtotal += $totalCost;
                $itemsCount++;
                
                $purchaseOrder->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $totalCost,
                ]);
            }
            
            // Update totals
            $purchaseOrder->subtotal = $subtotal;
            $purchaseOrder->total_amount = $subtotal + 
                                         $purchaseOrder->tax_amount + 
                                         $purchaseOrder->shipping_cost - 
                                         $purchaseOrder->discount_amount;
            $purchaseOrder->balance_due = $purchaseOrder->total_amount - $purchaseOrder->amount_paid;
            $purchaseOrder->save();
            
            // Log the update
            activity()
                ->performedOn($purchaseOrder)
                ->causedBy(auth()->user())
                ->withProperties([
                    'items_count' => $itemsCount,
                    'old_status' => $oldStatus,
                    'new_status' => $purchaseOrder->status
                ])
                ->log('updated purchase order');
            
            DB::commit();
            
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('success', 'Purchase order updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
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
        
        // Log before deletion
        activity()
            ->performedOn($purchaseOrder)
            ->causedBy(auth()->user())
            ->withProperties([
                'po_number' => $purchaseOrder->po_number,
                'supplier' => $purchaseOrder->supplier->name,
                'total_amount' => $purchaseOrder->total_amount
            ])
            ->log('deleted purchase order');
        
        $purchaseOrder->delete();
        
        return redirect()->route('purchase-orders.index')
            ->with('success', 'Purchase order deleted successfully.');
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
        
        $oldStatus = $purchaseOrder->status;
        $purchaseOrder->update(['status' => 'approved']);
        
        // Log the approval
        activity()
            ->performedOn($purchaseOrder)
            ->causedBy(auth()->user())
            ->withProperties([
                'old_status' => $oldStatus,
                'new_status' => 'approved'
            ])
            ->log('approved purchase order');
        
        return redirect()->back()
            ->with('success', 'Purchase order approved successfully.');
    }

    /**
     * Mark purchase order as ordered (placed with supplier)
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
            
            // Update status and order date (when actually placed with supplier)
            $purchaseOrder->update([
                'status' => 'ordered',
                'order_date' => now(), // Update to actual order date with supplier
            ]);
            
            // Log the ordering
            activity()
                ->performedOn($purchaseOrder)
                ->causedBy(auth()->user())
                ->withProperties([
                    'old_status' => $oldStatus,
                    'new_status' => 'ordered',
                    'ordered_date' => now()
                ])
                ->log('marked purchase order as ordered (placed with supplier)');
            
            DB::commit();
            
            return redirect()->back()
                ->with('success', 'Purchase order marked as ordered. You have placed this order with the supplier.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to mark purchase order as ordered: ' . $e->getMessage());
        }
    }

    /**
     * Quick action to approve and mark as ordered (one-click)
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
            
            // Directly mark as ordered (bypassing approved status)
            $purchaseOrder->update([
                'status' => 'ordered',
                'order_date' => now(), // Update to actual order date
            ]);
            
            // Log the approval and ordering
            activity()
                ->performedOn($purchaseOrder)
                ->causedBy(auth()->user())
                ->withProperties([
                    'old_status' => $oldStatus,
                    'new_status' => 'ordered',
                    'action' => 'approve_and_order'
                ])
                ->log('approved and marked purchase order as ordered (placed with supplier)');
            
            DB::commit();
            
            return redirect()->back()
                ->with('success', 'Purchase order approved and marked as ordered. You have placed this order with the supplier.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to process order: ' . $e->getMessage());
        }
    }

    /**
     * Mark purchase order as received (goods arrived)
     */
    public function markAsReceived(PurchaseOrder $purchaseOrder)
    {
        // Only allow from ordered or partially_received status
        if (!in_array($purchaseOrder->status, ['ordered', 'partially_received'])) {
            return redirect()->back()
                ->with('error', 'Only ordered or partially received purchase orders can be marked as fully received.');
        }
        
        // Check if all items have been received
        $allReceived = true;
        foreach ($purchaseOrder->items as $item) {
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
                'delivery_date' => now(), // Actual delivery date
            ]);
            
            // Log the full receipt
            activity()
                ->performedOn($purchaseOrder)
                ->causedBy(auth()->user())
                ->withProperties([
                    'old_status' => $oldStatus,
                    'new_status' => 'received',
                    'delivery_date' => now()
                ])
                ->log('marked purchase order as fully received');
            
            DB::commit();
            
            return redirect()->back()
                ->with('success', 'Purchase order marked as fully received. All items have been received.');
                
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
        
        $oldStatus = $purchaseOrder->status;
        $purchaseOrder->update(['status' => 'cancelled']);
        
        // Log the cancellation
        activity()
            ->performedOn($purchaseOrder)
            ->causedBy(auth()->user())
            ->withProperties([
                'old_status' => $oldStatus,
                'new_status' => 'cancelled'
            ])
            ->log('cancelled purchase order');
        
        return redirect()->back()
            ->with('success', 'Purchase order cancelled successfully.');
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
        
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:purchase_order_items,id',
            'items.*.received' => 'required|numeric|min:0',
            'items.*.damaged' => 'nullable|numeric|min:0',
            'items.*.returned' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
            'delivery_date' => 'nullable|date',
            'receiving_notes' => 'nullable|string',
        ]);
        
        DB::beginTransaction();
        
        try {
            $receivedItems = [];
            $totalReceived = 0;
            $totalDamaged = 0;
            $totalReturned = 0;
            
            foreach ($request->items as $itemData) {
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
                
                // Update product stock for received items (not damaged or returned)
                if ($received > 0) {
                    $product = $item->product;
                    $product->stock_quantity += $received;
                    $product->save();
                    
                    // TODO: Create stock adjustment record here
                    // We'll implement this when we create the StockAdjustment model
                }
            }
            
            // Update delivery date if provided
            if ($request->delivery_date) {
                $purchaseOrder->delivery_date = $request->delivery_date;
            }
            
            $oldStatus = $purchaseOrder->status;
            
            // Update purchase order status
            $this->calculateTotals($purchaseOrder);
            
            // Check if fully received
            if ($this->isFullyReceived($purchaseOrder)) {
                $purchaseOrder->status = 'received';
                $purchaseOrder->delivery_date = $purchaseOrder->delivery_date ?? now();
            } else {
                $purchaseOrder->status = 'partially_received';
            }
            
            $purchaseOrder->save();
            
            // Log the receiving of items
            activity()
                ->performedOn($purchaseOrder)
                ->causedBy(auth()->user())
                ->withProperties([
                    'old_status' => $oldStatus,
                    'new_status' => $purchaseOrder->status,
                    'items_received' => $receivedItems,
                    'total_received' => $totalReceived,
                    'total_damaged' => $totalDamaged,
                    'total_returned' => $totalReturned,
                    'receiving_notes' => $request->receiving_notes
                ])
                ->log('received items for purchase order');
            
            DB::commit();
            
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('success', 'Items received successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to receive items: ' . $e->getMessage());
        }
    }
    
    /**
     * Helper method to calculate totals
     */
    private function calculateTotals(PurchaseOrder $purchaseOrder)
    {
        $subtotal = $purchaseOrder->items()->sum('total_cost');
        $purchaseOrder->subtotal = $subtotal;
        $purchaseOrder->total_amount = $subtotal + 
                                     $purchaseOrder->tax_amount + 
                                     $purchaseOrder->shipping_cost - 
                                     $purchaseOrder->discount_amount;
        $purchaseOrder->balance_due = $purchaseOrder->total_amount - $purchaseOrder->amount_paid;
        
        // Update payment status
        if ($purchaseOrder->balance_due <= 0) {
            $purchaseOrder->payment_status = 'paid';
        } elseif ($purchaseOrder->amount_paid > 0) {
            $purchaseOrder->payment_status = 'partial';
        } else {
            $purchaseOrder->payment_status = 'pending';
        }
        
        $purchaseOrder->save();
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
}