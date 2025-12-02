<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PermissionController extends Controller
{
    /**
     * Display permissions for a role
     */
    public function index(Role $role)
    {
        // Get all permissions grouped by category
        $permissions = Permission::orderBy('name')->get()->groupBy(function($permission) {
            // Group by the first word (action)
            $parts = explode(' ', $permission->name);
            return $parts[0]; // 'view', 'create', 'edit', etc.
        });
        
        // Get role's current permissions
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        
        return Inertia::render('Admin/Roles/Permissions', [
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissions' => $rolePermissions,
        ]);
    }

    /**
     * Update role permissions
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        // Sync permissions (replace all existing with new ones)
        $role->syncPermissions($request->permissions);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Permissions updated successfully for ' . $role->name);
    }
}