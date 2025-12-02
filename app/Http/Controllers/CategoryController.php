<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::with(['parent', 'children', 'products'])
        ->withCount('products')
            ->latest()
            ->paginate(10);
            
        $parentCategories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->get();
            
        return Inertia::render('Categories/index', [
            'categories' => $categories,
            'parentCategories' => $parentCategories,
        ]);


    }
   public function create()
    {
        $parentCategories = Category::whereNull('parent_id')->get();
        
        return Inertia::render('Categories/Create', [
            'parentCategories' => $parentCategories
        ]);
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
        ]);

        Category::create($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
        ]);

        // Prevent circular reference
        if ($validated['parent_id'] == $category->id) {
            return redirect()->back()
                ->with('error', 'Category cannot be its own parent.');
        }

        $category->update($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        // Check if category has products
        if ($category->products()->count() > 0) {
            return redirect()->route('categories.index')
                ->with('error', 'Cannot delete category that has products.');
        }

        // Check if category has children
        if ($category->children()->count() > 0) {
            return redirect()->route('categories.index')
                ->with('error', 'Cannot delete category that has sub-categories.');
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}