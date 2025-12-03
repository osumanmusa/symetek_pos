<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WarehouseController extends Controller
{
public function index(Request $request)
{
    $query = Warehouse::withCount(['inventoryLevels as product_count']);
    
    // Apply filters if provided
    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhere('location', 'like', "%{$search}%");
        });
    }
    
    if ($request->filled('status')) {
        if ($request->input('status') === 'active') {
            $query->where('is_active', true);
        } elseif ($request->input('status') === 'inactive') {
            $query->where('is_active', false);
        }
    }
    
    $warehouses = $query->latest()->paginate(20);
        
    return Inertia::render('Inventory/Warehouses/index', [
        'warehouses' => $warehouses,
        'filters' => $request->only(['search', 'status']), // Pass filters to Vue
    ]);
}
    
    public function create()
    {
        return Inertia::render('Inventory/Warehouses/Create');
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|unique:warehouses,code',
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);
        
        if (empty($validated['code'])) {
            $validated['code'] = Warehouse::generateCode();
        }
        
        Warehouse::create($validated);
        
        return redirect()->route('warehouses.index')
            ->with('success', 'Warehouse created successfully.');
    }
    
    public function edit(Warehouse $warehouse)
    {
        return Inertia::render('Inventory/Warehouses/Edit', [
            'warehouse' => $warehouse,
        ]);
    }
    
    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:warehouses,code,' . $warehouse->id,
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);
        
        // If setting this as default, unset other defaults
        if ($validated['is_default']) {
            Warehouse::where('is_default', true)->update(['is_default' => false]);
        }
        
        $warehouse->update($validated);
        
        return redirect()->route('warehouses.index')
            ->with('success', 'Warehouse updated successfully.');
    }
    
    public function destroy(Warehouse $warehouse)
    {
        if ($warehouse->inventoryLevels()->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete warehouse with inventory. Transfer stock first.');
        }
        
        $warehouse->delete();
        
        return redirect()->route('warehouses.index')
            ->with('success', 'Warehouse deleted successfully.');
    }
}
