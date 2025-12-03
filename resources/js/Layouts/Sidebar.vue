<!-- resources/js/Components/Layout/Sidebar.vue -->
<template>
    <aside :class="[
        'fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 text-white transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0',
        open ? 'translate-x-0' : '-translate-x-full'
    ]">
        <!-- Logo -->
        <div class="flex items-center justify-center h-16 border-b border-gray-800">
            <div class="flex items-center space-x-3">
                <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span class="text-xl font-bold">POS<span class="text-blue-400">Pro</span></span>
            </div>
        </div>

        <!-- User Profile -->
        <div class="p-4 border-b border-gray-800">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center">
                    <span class="font-semibold">{{ userInitials }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate">{{ user.name }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ user.email }}</p>
                    <div class="flex items-center mt-1">
                        <span v-for="role in user.roles" :key="role" 
                              class="text-xs px-2 py-1 rounded-full bg-blue-900 text-blue-200 mr-1">
                            {{ role }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="p-4 space-y-1 overflow-y-auto h-[calc(100vh-10rem)]">
            <!-- Dashboard -->
            <Link 
                :href="route('dashboard')"
                :class="[
                    'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors',
                    $page.url === '/dashboard' 
                        ? 'bg-blue-600 text-white' 
                        : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                ]"
                @click="handleNavigation">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Dashboard
            </Link>

            <!-- SALES SECTION -->
            <div v-if="can('create sales') || can('view sales')" class="pt-4">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">SALES</p>
                <div class="mt-2 space-y-1">
                    <Link v-if="can('create sales')" 
                        :href="route('sales.index')"
                        :class="[
                            'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors',
                            $page.url.startsWith('/sales/create') 
                                ? 'bg-blue-600 text-white' 
                                : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                        ]"
                        @click="handleNavigation">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        POS Terminal
                    </Link>
                    
                    <Link v-if="can('view sales')" 
                        :href="route('sales.index')"
                        :class="[
                            'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors',
                            ($page.url.startsWith('/sales') && !$page.url.startsWith('/sales/create')) 
                                ? 'bg-blue-600 text-white' 
                                : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                        ]"
                        @click="handleNavigation">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Transactions
                        <span v-if="$page.url.startsWith('/sales') && !$page.url.startsWith('/sales/create')" class="ml-auto">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </Link>
                    
                    <Link v-if="can('view orders')" 
                        :href="route('orders.index')"
                        :class="[
                            'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors',
                            $page.url.startsWith('/orders') 
                                ? 'bg-blue-600 text-white' 
                                : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                        ]"
                        @click="handleNavigation">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Orders
                    </Link>
                </div>
            </div>

            <!-- INVENTORY SECTION -->
            <div v-if="can('view products') || can('view inventory') || can('view categories') || can('view suppliers')" class="pt-4">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">INVENTORY</p>
                <div class="mt-2 space-y-1">
             <Link v-if="can('view products')" 
            :href="route('products.index')"
            :class="[
                'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors',
                $page.url.startsWith('/products') 
                    ? 'bg-blue-600 text-white' 
                    : 'text-gray-300 hover:bg-gray-800 hover:text-white'
            ]"
            @click="handleNavigation">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            Products
        </Link>
        
        <Link v-if="can('view categories')" 
            :href="route('categories.index')"
            :class="[
                'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors',
                $page.url.startsWith('/categories') 
                    ? 'bg-blue-600 text-white' 
                    : 'text-gray-300 hover:bg-gray-800 hover:text-white'
            ]"
            @click="handleNavigation">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
            Categories
        </Link>
                    

<!-- Inventory Dropdown -->
<div v-if="can('view inventory')" class="mb-2">
    <button
        @click="toggleInventoryDropdown"
        :class="[
            'flex items-center justify-between w-full px-4 py-3 text-sm font-medium rounded-lg transition-colors',
            isInventoryActive 
                ? 'bg-blue-600 text-white' 
                : 'text-gray-300 hover:bg-gray-800 hover:text-white'
        ]"
    >
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            Inventory
        </div>
        <svg 
            :class="[
                'w-4 h-4 transition-transform duration-200',
                inventoryDropdownOpen ? 'transform rotate-180' : ''
            ]" 
            fill="none" stroke="currentColor" viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>
    
    <!-- Dropdown Menu -->
    <div 
        v-show="inventoryDropdownOpen" 
        class="mt-1 ml-8 space-y-1"
    >
        <!-- Dashboard -->
        <Link
            :href="route('inventory.index')"
            :class="[
                'flex items-center px-3 py-2 text-sm rounded-md transition-colors',
                $page.url === '/inventory' 
                    ? 'bg-blue-100 text-blue-700' 
                    : 'text-gray-300 hover:bg-gray-800 hover:text-white'
            ]"
        >
            <span class="w-1 h-3 mr-2 bg-blue-500 rounded-full"></span>
            Inventory Dashboard
        </Link>
        
        <!-- Transactions -->
        <Link
            :href="route('inventory.transactions.index')"
            :class="[
                'flex items-center px-3 py-2 text-sm rounded-md transition-colors',
                $page.url.startsWith('/inventory-transactions') || $page.url.startsWith('/inventory/transactions')
                    ? 'bg-blue-100 text-blue-700' 
                    : 'text-gray-300 hover:bg-gray-800 hover:text-white'
            ]"
        >
            <span class="w-1 h-3 mr-2 bg-green-500 rounded-full"></span>
            Transactions
        </Link>
        
        <!-- Stock Levels -->
        <Link
            :href="route('inventory.levels.index')"
            :class="[
                'flex items-center px-3 py-2 text-sm rounded-md transition-colors',
                $page.url.startsWith('/inventory/stock-levels') || $page.url.startsWith('/inventory/levels')
                    ? 'bg-blue-100 text-blue-700' 
                    : 'text-gray-300 hover:bg-gray-800 hover:text-white'
            ]"
        >
            <span class="w-1 h-3 mr-2 bg-yellow-500 rounded-full"></span>
            Stock Levels
        </Link>
        
        <!-- Warehouses -->
        <Link
            :href="route('warehouses.index')"
            :class="[
                'flex items-center px-3 py-2 text-sm rounded-md transition-colors',
                $page.url.startsWith('/warehouses')
                    ? 'bg-blue-100 text-blue-700' 
                    : 'text-gray-300 hover:bg-gray-800 hover:text-white'
            ]"
        >
            <span class="w-1 h-3 mr-2 bg-purple-500 rounded-full"></span>
            Warehouses
        </Link>
        
        <!-- Stock Report -->
        <Link
            :href="route('products.stock-report')"
            :class="[
                'flex items-center px-3 py-2 text-sm rounded-md transition-colors',
                $page.url.startsWith('/products/stock-report')
                    ? 'bg-blue-100 text-blue-700' 
                    : 'text-gray-300 hover:bg-gray-800 hover:text-white'
            ]"
        >
            <span class="w-1 h-3 mr-2 bg-red-500 rounded-full"></span>
            Stock Report
        </Link>
    </div>
</div>

                    
                    <Link v-if="can('view suppliers')" 
                        :href="route('suppliers.index')"
                        :class="[
                            'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors',
                            $page.url.startsWith('/suppliers') 
                                ? 'bg-blue-600 text-white' 
                                : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                        ]"
                        @click="handleNavigation">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Suppliers
                    </Link>
                    <Link v-if="can('view suppliers')" 
                        :href="route('purchase-orders.index')"
                        :class="[
                            'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors',
                            $page.url.startsWith('/purchase-orders') 
                                ? 'bg-blue-600 text-white' 
                                : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                        ]"
                        @click="handleNavigation">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                                        Purchase Orders
                    </Link>
                </div>
            </div>

            <!-- PEOPLE SECTION -->
            <div v-if="can('view customers')" class="pt-4">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">PEOPLE</p>
                <div class="mt-2 space-y-1">
                    <Link 
                        :href="route('customers.index')"
                        :class="[
                            'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors',
                            $page.url.startsWith('/customers') 
                                ? 'bg-blue-600 text-white' 
                                : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                        ]"
                        @click="handleNavigation">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Customers
                    </Link>
                </div>
            </div>

            <!-- ADMINISTRATION SECTION -->
            <div v-if="can('view users') || can('view roles')" class="pt-4">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">ADMINISTRATION</p>
                <div class="mt-2 space-y-1">
                    <Link v-if="hasRole('Super Admin')" 
                        :href="route('admin.dashboard')"
                        :class="[
                            'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors',
                            $page.url === '/admin/dashboard' 
                                ? 'bg-blue-600 text-white' 
                                : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                        ]"
                        @click="handleNavigation">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Admin Dashboard
                    </Link>
                    
                    <Link v-if="can('view users')" 
                        :href="route('admin.users.index')"
                        :class="[
                            'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors',
                            $page.url.startsWith('/admin/users') 
                                ? 'bg-blue-600 text-white' 
                                : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                        ]"
                        @click="handleNavigation">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 1.5a6 6 0 01-9-5.197" />
                        </svg>
                        Users
                        <span v-if="$page.url.startsWith('/admin/users')" class="ml-auto">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </Link>
                    
        <Link v-if="can('view roles')" 
    :href="route('admin.roles.index')"
    :class="[
        'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors',
        $page.url.startsWith('/admin/roles') 
            ? 'bg-blue-600 text-white' 
            : 'text-gray-300 hover:bg-gray-800 hover:text-white'
    ]"
    @click="handleNavigation">
    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
    </svg>
    Roles & Permissions
</Link>
                </div>
            </div>

            <!-- REPORTS SECTION -->
            <div v-if="can('view sales reports') || can('view inventory reports') || can('view financial reports') || can('view customer reports')" class="pt-4">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">REPORTS</p>
                <div class="mt-2 space-y-1">
                    <Link v-if="can('view sales reports')" 
                        :href="route('reports.sales')"
                        :class="[
                            'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors',
                            $page.url.startsWith('/reports/sales') 
                                ? 'bg-blue-600 text-white' 
                                : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                        ]"
                        @click="handleNavigation">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Sales Reports
                    </Link>
                    
                    <Link v-if="can('view inventory reports')" 
                        :href="route('reports.inventory')"
                        :class="[
                            'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors',
                            $page.url.startsWith('/reports/inventory') 
                                ? 'bg-blue-600 text-white' 
                                : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                        ]"
                        @click="handleNavigation">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Inventory Reports
                    </Link>
                    
                    <Link v-if="can('view financial reports')" 
                        :href="route('reports.financial')"
                        :class="[
                            'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors',
                            $page.url.startsWith('/reports/financial') 
                                ? 'bg-blue-600 text-white' 
                                : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                        ]"
                        @click="handleNavigation">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Financial Reports
                    </Link>
                    
                    <Link v-if="can('view customer reports')" 
                        :href="route('reports.customers')"
                        :class="[
                            'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors',
                            $page.url.startsWith('/reports/customers') 
                                ? 'bg-blue-600 text-white' 
                                : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                        ]"
                        @click="handleNavigation">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Customer Reports
                    </Link>
                </div>
            </div>

            <!-- SETTINGS SECTION -->
            <div class="pt-4">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">SETTINGS</p>
                <div class="mt-2 space-y-1">
                    <Link 
                        :href="route('settings.index')"
                        :class="[
                            'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors',
                            $page.url.startsWith('/settings') && !$page.url.startsWith('/settings/taxes') && !$page.url.startsWith('/settings/discounts')
                                ? 'bg-blue-600 text-white' 
                                : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                        ]"
                        @click="handleNavigation">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        General Settings
                    </Link>
                    
                    <Link 
                        :href="route('settings.taxes')"
                        :class="[
                            'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors',
                            $page.url.startsWith('/settings/taxes') 
                                ? 'bg-blue-600 text-white' 
                                : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                        ]"
                        @click="handleNavigation">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8h6m-5 0a3 3 0 110 6H9l3 3m-3-6h6m6 1a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Tax Settings
                    </Link>
                    
                    <Link 
                        :href="route('settings.discounts')"
                        :class="[
                            'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors',
                            $page.url.startsWith('/settings/discounts') 
                                ? 'bg-blue-600 text-white' 
                                : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                        ]"
                        @click="handleNavigation">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Discount Settings
                    </Link>
                    
                    <Link 
                        :href="route('profile.edit')"
                        :class="[
                            'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors',
                            $page.url === '/profile' 
                                ? 'bg-blue-600 text-white' 
                                : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                        ]"
                        @click="handleNavigation">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Profile
                    </Link>
                </div>
            </div>

            <!-- Logout -->
            <form @submit.prevent="logout" class="pt-4 mt-4 border-t border-gray-800">
                <button type="submit"
                    class="flex items-center w-full px-4 py-3 text-sm font-medium text-gray-300 rounded-lg hover:bg-gray-800 hover:text-white transition-colors"
                    @click="handleNavigation">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </button>
            </form>
        </nav>
    </aside>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    open: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['close']);

const page = usePage();
const user = computed(() => page.props.auth.user);

const userInitials = computed(() => {
    if (!user.value?.name) return 'U';
    return user.value.name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
});


// Inventory dropdown state
const inventoryDropdownOpen = ref(false)

// Check if any inventory page is active
const isInventoryActive = computed(() => {
  const url = page.url
  return url.startsWith('/inventory') || 
         url.startsWith('/inventory-transactions') ||
         url.startsWith('/warehouses')
})

// Toggle dropdown
const toggleInventoryDropdown = () => {
  inventoryDropdownOpen.value = !inventoryDropdownOpen.value
}

// Auto-open dropdown when on inventory page
watch(() => page.url, (newUrl) => {
  if (newUrl.startsWith('/inventory') || 
      newUrl.startsWith('/inventory-transactions') ||
      newUrl.startsWith('/warehouses')) {
    inventoryDropdownOpen.value = true
  }
}, { immediate: true })


const can = (permission) => {
    return user.value?.permissions?.includes(permission);
};

const hasRole = (role) => {
    return user.value?.roles?.includes(role);
};

const handleNavigation = () => {
    // Close sidebar on mobile after navigation
    if (window.innerWidth < 1024) {
        emit('close');
    }
};

const logout = () => {
    router.post(route('logout'));
};
</script>