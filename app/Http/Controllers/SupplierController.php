<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::latest()
            ->paginate(10);
            
        return Inertia::render('Suppliers/index', [
            'suppliers' => $suppliers,
        ]);
    }
    
    public function create()
    {
        return Inertia::render('Suppliers/Create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'phone_2' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:50',
            'payment_terms' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        
        $supplier = Supplier::create([
            'supplier_code' => Supplier::generateSupplierCode(),
            'name' => $request->name,
            'contact_person' => $request->contact_person,
            'email' => $request->email,
            'phone' => $request->phone,
            'phone_2' => $request->phone_2,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country ?? 'Ghana',
            'tax_id' => $request->tax_id,
            'payment_terms' => $request->payment_terms ?? '30_days',
            'credit_limit' => $request->credit_limit ?? 0,
            'current_balance' => 0,
            'notes' => $request->notes,
            'is_active' => $request->is_active ?? true,
        ]);
        
        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }
    
    public function edit(Supplier $supplier)
    {
        return Inertia::render('Suppliers/Edit', [
            'supplier' => $supplier,
        ]);
    }
    
    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'phone_2' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:50',
            'payment_terms' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        
        $supplier->update([
            'name' => $request->name,
            'contact_person' => $request->contact_person,
            'email' => $request->email,
            'phone' => $request->phone,
            'phone_2' => $request->phone_2,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country ?? 'Ghana',
            'tax_id' => $request->tax_id,
            'payment_terms' => $request->payment_terms,
            'credit_limit' => $request->credit_limit,
            'notes' => $request->notes,
            'is_active' => $request->is_active,
        ]);
        
        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }
    
    public function destroy(Supplier $supplier)
    {
        // Check if supplier has purchase orders
        if ($supplier->purchaseOrders()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete supplier with existing purchase orders.');
        }
        
        $supplier->delete();
        
        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }
}