<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function index()
    {
        // $this->authorize('view roles');
        
        // $roles = Role::withCount('users')->get();
        $permissions = Permission::all()->groupBy(function ($permission) {
            $parts = explode(' ', $permission->name);
            return $parts[0];
        });
            // If it's an AJAX request, return JSON
    $roles = Role::withCount(['users', 'permissions'])->latest()->get();
    
    if (request()->expectsJson()) {
        return response()->json(['roles' => $roles]);
    }
        return Inertia::render('Admin/Roles/index', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        // $this->authorize('create roles');
        
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array',
        ]);
        
        $role = Role::create(['name' => $validated['name']]);
        
        if ($request->has('permissions')) {
            $role->syncPermissions($validated['permissions']);
        }
        
        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function update(Request $request, Role $role)
    {
        // $this->authorize('edit roles');
        
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'array',
        ]);
        
        $role->update(['name' => $validated['name']]);
        
        if ($request->has('permissions')) {
            $role->syncPermissions($validated['permissions']);
        }
        
        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        // $this->authorize('delete roles');
        
        if ($role->users()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete role with assigned users.');
        }
        
        $role->delete();
        
        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }
}