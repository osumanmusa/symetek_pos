<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions - UPDATED WITH ALL NEEDED PERMISSIONS
        $permissions = [
            // Dashboard
            'view dashboard',
            
            // User Management
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // Role & Permission Management
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'manage permissions',
            
            // Product Management
            'view products',
            'create products',
            'edit products',
            'delete products',
            'adjust stock',
            
            // Category Management
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',
            
            // Inventory Management
            'view inventory',
            'manage inventory',
            'view stock reports',
            
            // Suppliers
            'view suppliers',
            'create suppliers',
            'edit suppliers',
            'delete suppliers',
            
            // Sales & POS
            'create sales',
            'view sales',
            'edit sales',
            'delete sales',
            'process payments',
            'void sales',
            'apply discounts',
            
            // Customers
            'view customers',
            'create customers',
            'edit customers',
            'delete customers',
            
            // Orders
            'view orders',
            'create orders',
            'edit orders',
            'delete orders',
            
            // Reports
            'view sales reports',
            'view inventory reports',
            'view financial reports',
            'view customer reports',
            
            // System Settings
            'manage settings',
            'manage taxes',
            'manage discounts',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdminRole->syncPermissions(Permission::all());

        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminPermissions = [
            'view dashboard',
            'view users', 'create users', 'edit users',
            'view roles',
            'view products', 'create products', 'edit products', 'delete products', 'adjust stock',
            'view categories', 'create categories', 'edit categories', 'delete categories',
            'view inventory', 'manage inventory', 'view stock reports',
            'view suppliers', 'create suppliers', 'edit suppliers', 'delete suppliers',
            'create sales', 'view sales', 'edit sales', 'delete sales', 'process payments', 'void sales', 'apply discounts',
            'view customers', 'create customers', 'edit customers', 'delete customers',
            'view orders', 'create orders', 'edit orders', 'delete orders',
            'view sales reports', 'view inventory reports', 'view financial reports', 'view customer reports',
            'manage settings', 'manage taxes', 'manage discounts',
        ];
        $adminRole->syncPermissions($adminPermissions);

        $managerRole = Role::firstOrCreate(['name' => 'Manager']);
        $managerPermissions = [
            'view dashboard',
            'view users', 'create users', 'edit users',
            'view products', 'create products', 'edit products', 'adjust stock',
            'view categories', 'create categories', 'edit categories',
            'view inventory', 'manage inventory', 'view stock reports',
            'view suppliers', 'create suppliers', 'edit suppliers',
            'create sales', 'view sales', 'edit sales', 'process payments', 'void sales', 'apply discounts',
            'view customers', 'create customers', 'edit customers',
            'view orders', 'create orders', 'edit orders',
            'view sales reports', 'view inventory reports',
            'manage discounts',
        ];
        $managerRole->syncPermissions($managerPermissions);

        $cashierRole = Role::firstOrCreate(['name' => 'Cashier']);
        $cashierPermissions = [
            'view dashboard',
            'view products',
            'create sales', 'view sales', 'process payments', 'apply discounts',
            'view customers', 'create customers',
        ];
        $cashierRole->syncPermissions($cashierPermissions);

        $inventoryRole = Role::firstOrCreate(['name' => 'Inventory Manager']);
        $inventoryPermissions = [
            'view dashboard',
            'view products', 'create products', 'edit products', 'adjust stock',
            'view categories', 'create categories', 'edit categories',
            'view inventory', 'manage inventory', 'view stock reports',
            'view suppliers', 'create suppliers', 'edit suppliers',
            'view inventory reports',
        ];
        $inventoryRole->syncPermissions($inventoryPermissions);

        // Assign Super Admin role to first user (if exists)
        $firstUser = User::first();
        if ($firstUser) {
            $firstUser->assignRole('Super Admin');
            \Log::info('Assigned Super Admin role to user: ' . $firstUser->email);
        }
        
        // Also check if admin@pos.com exists or create it
        $adminUser = User::where('email', 'admin@pos.com')->first();
        if (!$adminUser) {
            $adminUser = User::create([
                'name' => 'Super Admin',
                'email' => 'admin@pos.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            \Log::info('Created admin user: admin@pos.com');
        }
        
        if ($adminUser && !$adminUser->hasRole('Super Admin')) {
            $adminUser->assignRole('Super Admin');
            \Log::info('Assigned Super Admin role to admin user');
        }
    }
}