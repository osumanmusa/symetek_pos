<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    /**
     * Store a newly created customer.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:customers,email',
            'phone' => 'required|string|max:20|unique:customers,phone',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
        ]);
        
        $customer = Customer::create($validated);
        
        // Return JSON for AJAX or redirect for form
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully',
                'customer' => $customer,
            ]);
        }
        
        return back()->with('success', 'Customer created successfully');
    }
    
    /**
     * Display a listing of customers.
     */
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            $customers = Customer::orderBy('name')->get(['id', 'name', 'phone', 'email']);
            return response()->json($customers);
        }
        
        return Inertia::render('Customers/Index', [
            'customers' => Customer::latest()->paginate(20),
        ]);
    }
}