<template>
    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                    <p class="text-gray-600 mt-1">Welcome back, {{ $page.props.auth.user.name }}!</p>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="text-right">
                        <p class="text-sm text-gray-600">Today's Date</p>
                        <p class="font-semibold">{{ formattedDate }}</p>
                    </div>
                </div>
            </div>
        </template>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Sales -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Today's Sales</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $currency(stats.todaySales) }}</p>
                        <p class="text-sm mt-2" :class="stats.salesGrowth >= 0 ? 'text-green-600' : 'text-red-600'">
                            <span v-if="stats.salesGrowth >= 0">↗</span>
                            <span v-else>↘</span>
                            {{ Math.abs(stats.salesGrowth) }}% from yesterday
                        </p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Products -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Products</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatNumber(stats.totalProducts) }}</p>
                        <p class="text-sm text-gray-500 mt-2">{{ stats.lowStock }} low stock items</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Customers -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Customers</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatNumber(stats.totalCustomers) }}</p>
                        <p class="text-sm text-gray-500 mt-2">{{ stats.newCustomers }} new this month</p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Transactions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Today's Transactions</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatNumber(stats.todayTransactions) }}</p>
                        <p class="text-sm text-gray-500 mt-2">Average: {{ $currency(stats.averageTransaction) }}</p>
                    </div>
                    <div class="p-3 bg-orange-100 rounded-lg">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Tables Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Sales Chart -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Sales Overview</h3>
                    <select class="text-sm border border-gray-300 rounded-lg px-3 py-1.5">
                        <option>Last 7 days</option>
                        <option>Last 30 days</option>
                        <option>Last 90 days</option>
                    </select>
                </div>
                <div class="h-64 flex items-center justify-center border border-dashed border-gray-300 rounded-lg">
                    <div class="text-center">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <p class="text-gray-500">Sales chart will appear here</p>
                        <p class="text-sm text-gray-400 mt-1">Connect your sales data to view analytics</p>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Transactions</h3>
                    <Link v-if="can('view sales')" :href="route('sales.index')" class="text-sm text-blue-600 hover:text-blue-800">
                        View All →
                    </Link>
                </div>
                <div class="space-y-4">
                    <div v-for="transaction in recentTransactions" :key="transaction.id"
                        class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div :class="[
                                'w-10 h-10 rounded-full flex items-center justify-center',
                                transaction.type === 'sale' ? 'bg-green-100' : 'bg-blue-100'
                            ]">
                                <svg class="w-5 h-5" :class="transaction.type === 'sale' ? 'text-green-600' : 'text-blue-600'" 
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        :d="transaction.type === 'sale' 
                                            ? 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
                                            : 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ transaction.customer_name }}</p>
                                <p class="text-sm text-gray-500">{{ transaction.time }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold" :class="transaction.type === 'sale' ? 'text-green-600' : 'text-blue-600'">
                                {{ transaction.type === 'sale' ? '+' : '' }}{{ $currency(transaction.amount) }}
                            </p>
                            <p class="text-sm text-gray-500">{{ transaction.items }} items</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Low Stock Alert -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 lg:col-span-2">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Low Stock Alerts</h3>
                    <Link v-if="can('view inventory')" :href="route('inventory.index')" 
                        class="text-sm text-blue-600 hover:text-blue-800">
                        Manage Inventory →
                    </Link>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stock</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Low Stock Level</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="item in lowStockItems" :key="item.id" class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded bg-gray-200 mr-3"></div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ item.name }}</p>
                                            <p class="text-sm text-gray-500">SKU: {{ item.sku }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ item.category }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center">
                                        <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                            <div :class="[
                                                'h-2 rounded-full',
                                                item.stock_level <= item.low_stock_threshold * 0.3 ? 'bg-red-500' : 
                                                item.stock_level <= item.low_stock_threshold * 0.6 ? 'bg-yellow-500' : 'bg-green-500'
                                            ]" :style="{ width: `${(item.stock_level / (item.low_stock_threshold * 2)) * 100}%` }"></div>
                                        </div>
                                        <span class="text-sm font-medium">{{ item.stock_level }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ item.low_stock_threshold }}</td>
                                <td class="px-4 py-3">
                                    <span :class="[
                                        'px-2 py-1 text-xs rounded-full font-medium',
                                        item.stock_level <= item.low_stock_threshold * 0.3 ? 'bg-red-100 text-red-800' : 
                                        item.stock_level <= item.low_stock_threshold * 0.6 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'
                                    ]">
                                        {{ item.stock_level <= item.low_stock_threshold * 0.3 ? 'Critical' : 
                                           item.stock_level <= item.low_stock_threshold * 0.6 ? 'Low' : 'Warning' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Quick Actions</h3>
                <div class="space-y-3">
                    <Link v-if="can('create sales')" :href="route('sales.create')"
                        class="flex items-center justify-between p-4 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-blue-900">New Sale</p>
                                <p class="text-sm text-blue-700">Process a new transaction</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>

                    <Link v-if="can('create products')" :href="route('products.index')"
                        class="flex items-center justify-between p-4 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition-colors">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-green-900">Products</p>
                                <p class="text-sm text-green-700">Manage inventory items</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>

                    <Link v-if="can('view sales reports')" :href="route('reports.sales')"
                        class="flex items-center justify-between p-4 bg-purple-50 border border-purple-200 rounded-lg hover:bg-purple-100 transition-colors">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-purple-900">View Reports</p>
                                <p class="text-sm text-purple-700">Sales & inventory analytics</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>

                    <Link v-if="can('manage settings')" :href="route('profile.edit')"
                        class="flex items-center justify-between p-4 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Settings</p>
                                <p class="text-sm text-gray-700">System configuration</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';




// Get data from props
const props = defineProps({
    stats: Object,
    recentTransactions: Array,
    lowStockItems: Array,
});

const formatNumber = (num) => {
    const number = parseFloat(num) || 0;
    return number.toLocaleString('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    });
};

const formattedDate = computed(() => {
    return new Date().toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
});

// Permission check
const can = (permission) => {
    return usePage().props.auth.user?.permissions?.includes(permission);
};
</script>