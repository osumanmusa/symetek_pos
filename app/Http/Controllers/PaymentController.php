<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Sale;
use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments
     */
    public function index(Request $request)
    {
        $query = Payment::with(['sale', 'customer', 'receivedBy'])
            ->latest();
        
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('sale', function ($q2) use ($search) {
                      $q2->where('invoice_number', 'like', "%{$search}%");
                  });
            });
        }
        
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->input('payment_method'));
        }
        
        if ($request->filled('start_date')) {
            $query->whereDate('payment_date', '>=', $request->input('start_date'));
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('payment_date', '<=', $request->input('end_date'));
        }
        
        $payments = $query->paginate(50);
        
        $totalAmount = $payments->sum('amount');
        
        return Inertia::render('Payments/Index', [
            'payments' => $payments,
            'filters' => $request->only(['search', 'payment_method', 'start_date', 'end_date']),
            'totalAmount' => $totalAmount,
            'paymentMethods' => Sale::getPaymentMethods(),
        ]);
    }
    
    /**
     * Record a payment for an existing sale
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
            'payment_date' => 'nullable|date',
        ]);
        
        $sale = Sale::findOrFail($validated['sale_id']);
        
        // Create payment
        $payment = Payment::create([
            'sale_id' => $sale->id,
            'customer_id' => $sale->customer_id,
            'payment_date' => $validated['payment_date'] ?? now(),
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'reference_number' => $validated['reference_number'] ?? 'PAY-' . date('Ymd-His'),
            'notes' => $validated['notes'],
            'received_by' => auth()->id(),
        ]);
        
        // Update sale payment status
        $sale->amount_paid += $validated['amount'];
        $sale->change_amount = max(0, $sale->amount_paid - $sale->total_amount);
        $sale->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Payment recorded successfully',
            'payment' => $payment,
            'sale' => $sale->fresh(),
        ]);
    }
    
    /**
     * Get customer payment history
     */
    public function customerPayments(Customer $customer)
    {
        $payments = $customer->payments()
            ->with(['sale', 'receivedBy'])
            ->latest()
            ->paginate(20);
        
        return Inertia::render('Customers/Payments', [
            'customer' => $customer,
            'payments' => $payments,
        ]);
    }
}