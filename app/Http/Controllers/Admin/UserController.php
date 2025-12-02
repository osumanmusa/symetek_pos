<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        // $this->authorize('view users');
        

        $users = User::with('roles')->latest()->paginate(10);        $roles = Role::all();
    
        
        return Inertia::render('Admin/Users/index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        // $this->authorize('create users');
      
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
        'role' => 'required|string|exists:roles,name',
    ]);

    \Log::info('Creating user with data:', $validated);

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => bcrypt($validated['password']),
    ]);

    \Log::info('User created with ID: ' . $user->id);
    \Log::info('Assigning role: ' . $validated['role']);

    try {
        $user->assignRole($validated['role']);
        \Log::info('Role assigned successfully');
    } catch (\Exception $e) {
        \Log::error('Failed to assign role: ' . $e->getMessage());
    }

    return redirect()->route('admin.users.index')
        ->with('success', 'User created successfully.');
    
    
    }

    public function update(Request $request, User $user)
    {
        // $this->authorize('edit users');
        

        \Log::info('Updating user ID: ' . $user->id);
        \Log::info('Current user roles:', $user->getRoleNames()->toArray());
        \Log::info('Request data:', $request->all());
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name',
        ]);

        \Log::info('Validated data:', $validated);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($request->filled('password')) {
            $user->update([
                'password' => bcrypt($validated['password']),
            ]);
            \Log::info('Password updated for user: ' . $user->id);
        }

        // Sync roles (replace all existing roles with the new one)
        try {
            \Log::info('Syncing role: ' . $validated['role']);
            $user->syncRoles([$validated['role']]);
            \Log::info('Roles after sync:', $user->getRoleNames()->toArray());
        } catch (\Exception $e) {
            \Log::error('Failed to sync roles: ' . $e->getMessage());
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        // $this->authorize('delete users');
        
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Cannot delete your own account.');
        }
        
        $user->delete();
        
              return redirect()->route('admin.users.index')
->with('success', 'User deleted successfully.');
    }
}