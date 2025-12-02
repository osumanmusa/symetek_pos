<template>
    <aside :class="[
        'fixed inset-y-0 left-0 z-50 w-64 bg-gray-800 text-white transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
    ]">
        <!-- Logo -->
        <div class="flex items-center justify-center h-16 border-b border-gray-700">
            <div class="flex items-center space-x-2">
                <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span class="text-xl font-bold">POS<span class="text-blue-400">System</span></span>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="p-4 space-y-2">
            <div class="mb-4">
                <h3 class="text-xs uppercase tracking-wider text-gray-400 mb-2">Main</h3>
                <Link :href="route('dashboard')" 
                    :class="['flex items-center space-x-3 px-3 py-2 rounded-lg transition-colors', 
                            $page.url === '/dashboard' ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700']">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard</span>
                </Link>
            </div>

            <div v-if="can('view sales')">
                <h3 class="text-xs uppercase tracking-wider text-gray-400 mb-2">Sales</h3>
                <Link :href="route('sales.index')" 
                    :class="['flex items-center space-x-3 px-3 py-2 rounded-lg transition-colors', 
                            $page.url.startsWith('/sales') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700']">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Sales</span>
                </Link>
            </div>

            <div v-if="can('view products')">
                <h3 class="text-xs uppercase tracking-wider text-gray-400 mb-2">Inventory</h3>
                <Link :href="route('products.index')" 
                    :class="['flex items-center space-x-3 px-3 py-2 rounded-lg transition-colors', 
                            $page.url.startsWith('/products') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700']">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span>Products</span>
                </Link>
                <Link :href="route('inventory.index')" 
                    :class="['flex items-center space-x-3 px-3 py-2 rounded-lg transition-colors', 
                            $page.url.startsWith('/inventory') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700']">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span>Inventory</span>
                </Link>
            </div>

            <div v-if="can('view customers')">
                <h3 class="text-xs uppercase tracking-wider text-gray-400 mb-2">People</h3>
                <Link :href="route('customers.index')" 
                    :class="['flex items-center space-x-3 px-3 py-2 rounded-lg transition-colors', 
                            $page.url.startsWith('/customers') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700']">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Customers</span>
                </Link>
            </div>

            <div v-if="can('view users')">
                <h3 class="text-xs uppercase tracking-wider text-gray-400 mb-2">Admin</h3>
                <Link :href="route('admin.users.index')" 
                    :class="['flex items-center space-x-3 px-3 py-2 rounded-lg transition-colors', 
                            $page.url.startsWith('/admin/users') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700']">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 1.5a6 6 0 01-9-5.197" />
                    </svg>
                    <span>Users</span>
                </Link>
            </div>

            <div class="pt-4 mt-4 border-t border-gray-700">
                <Link :href="route('profile.edit')" 
                    :class="['flex items-center space-x-3 px-3 py-2 rounded-lg transition-colors', 
                            $page.url.startsWith('/profile') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700']">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Profile</span>
                </Link>
            </div>
        </nav>
    </aside>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';

defineProps({
    sidebarOpen: Boolean
});

const page = usePage();
const user = page.props.auth.user;

const can = (permission) => {
    return user?.permissions?.includes(permission);
};
</script>