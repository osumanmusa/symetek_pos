<template>
    <AppLayout>
        <template #header>
            <div class="mb-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Sales History</h2>
                        <p class="text-gray-600">View and manage all sales transactions</p>
                    </div>
                    <div class="flex space-x-3">
                        <Link
                            :href="route('pos')"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            New Sale
                        </Link>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Today's Sales</p>
                                <p class="text-2xl font-bold text-gray-900">${{ formatCurrency(stats.today_sales) }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">This Month</p>
                                <p class="text-2xl font-bold text-gray-900">${{ formatCurrency(stats.month_sales) }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="flex items-center">
                            <div class="p-2 bg-purple-100 rounded-lg">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Sales</p>
                                <p class="text-2xl font-bold text-gray-900">{{ stats.total_sales || 0 }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="flex items-center">
                            <div class="p-2 bg-yellow-100 rounded-lg">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Customers Today</p>
                                <p class="text-2xl font-bold text-gray-900">{{ stats.customers_today || 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filters -->
                <div class="bg-white rounded-lg shadow mb-6 p-4">
                    <div class="flex flex-wrap gap-4 items-center">
                        <!-- Search -->
                        <div class="flex-1 min-w-[300px]">
                            <input
                                type="text"
                                v-model="filters.search"
                                placeholder="Search by invoice, customer, phone..."
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            />
                        </div>
                        
                        <!-- Date Range -->
                        <div class="flex gap-2">
                            <input
                                type="date"
                                v-model="filters.start_date"
                                class="px-3 py-2 border rounded-lg"
                            />
                            <span class="self-center">to</span>
                            <input
                                type="date"
                                v-model="filters.end_date"
                                class="px-3 py-2 border rounded-lg"
                            />
                        </div>
                        
                        <!-- Status Filter -->
                        <select
                            v-model="filters.status"
                            class="px-3 py-2 border rounded-lg"
                        >
                            <option value="">All Status</option>
                            <option value="completed">Completed</option>
                            <option value="pending">Pending</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        
                        <!-- Payment Method -->
                        <select
                            v-model="filters.payment_method"
                            class="px-3 py-2 border rounded-lg"
                        >
                            <option value="">All Payment Methods</option>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="mobile">Mobile Money</option>
                            <option value="transfer">Bank Transfer</option>
                        </select>
                        
                        <!-- Action Buttons -->
                        <div class="flex gap-2">
                            <button
                                @click="resetFilters"
                                class="px-4 py-2 text-gray-700 border rounded-lg hover:bg-gray-50"
                            >
                                Reset
                            </button>
                            <button
                                @click="applyFilters"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                            >
                                Apply
                            </button>
                            <button
                                @click="exportToCSV"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
                            >
                                Export CSV
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Sales Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Invoice #
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Customer
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Items
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Payment
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="sale in sales.data" :key="sale.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-blue-600">{{ sale.invoice_number }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div v-if="sale.customer" class="text-sm font-medium text-gray-900">
                                        {{ sale.customer.name }}
                                    </div>
                                    <div v-else class="text-sm text-gray-500">Walk-in Customer</div>
                                    <div v-if="sale.customer?.phone" class="text-sm text-gray-500">
                                        {{ sale.customer.phone }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ formatDate(sale.created_at) }}</div>
                                    <div class="text-sm text-gray-500">{{ formatTime(sale.created_at) }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ sale.items_count || 0 }} items</div>
                                    <div class="text-xs text-gray-500">{{ getProductNames(sale.items) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-lg font-bold text-gray-900">${{ formatCurrency(sale.total_amount) }}</div>
                                    <div class="text-xs text-gray-500">
                                        Paid: ${{ formatCurrency(sale.amount_paid) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ getPaymentMethodLabel(sale.payment_method) }}</div>
                                    <div v-if="sale.change_amount > 0" class="text-xs text-green-600">
                                        Change: ${{ formatCurrency(sale.change_amount) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getStatusClass(sale)" class="px-2 py-1 text-xs font-medium rounded-full">
                                        {{ sale.is_completed ? 'Completed' : 'Pending' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <Link
                                            :href="route('sales.show', sale.id)"
                                            class="text-blue-600 hover:text-blue-900"
                                        >
                                            View
                                        </Link>
                                        <Link
                                            :href="route('sales.receipt', sale.id)"
                                            target="_blank"
                                            class="text-green-600 hover:text-green-900"
                                        >
                                            Receipt
                                        </Link>
                                        <button
                                            v-if="sale.is_completed"
                                            @click="processRefund(sale)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Refund
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <!-- Empty State -->
                    <div v-if="sales.data.length === 0" class="text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="mt-4 text-lg text-gray-500">No sales found</p>
                        <p class="text-gray-400">Start by creating your first sale from the POS</p>
                    </div>
                </div>
                
                <!-- Pagination -->
                <div v-if="sales.data.length > 0" class="mt-4">
                    <Pagination :links="sales.links" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    sales: Object,
    filters: Object,
    stats: Object,
    paymentMethods: Object,
})

const filters = ref({
    search: props.filters?.search || '',
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
    status: props.filters?.status || '',
    payment_method: props.filters?.payment_method || '',
})

// Helper function to safely format currency
const formatCurrency = (value) => {
    if (value === null || value === undefined) return '0.00'
    const num = parseFloat(value)
    return isNaN(num) ? '0.00' : num.toFixed(2)
}

// Helper function to safely format numbers
const formatNumber = (value) => {
    if (value === null || value === undefined) return '0'
    const num = parseFloat(value)
    return isNaN(num) ? '0' : num.toString()
}

const applyFilters = () => {
    router.get(route('sales.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
    })
}

const resetFilters = () => {
    filters.value = {
        search: '',
        start_date: '',
        end_date: '',
        status: '',
        payment_method: '',
    }
    applyFilters()
}

const formatDate = (dateString) => {
    if (!dateString) return ''
    try {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        })
    } catch (error) {
        return ''
    }
}

const formatTime = (dateString) => {
    if (!dateString) return ''
    try {
        return new Date(dateString).toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
        })
    } catch (error) {
        return ''
    }
}

const getProductNames = (items) => {
    if (!items || items.length === 0) return ''
    const names = items.slice(0, 2).map(item => item.product?.name || 'Unknown Product')
    return names.join(', ') + (items.length > 2 ? ` +${items.length - 2} more` : '')
}

const getPaymentMethodLabel = (method) => {
    if (!method) return 'Unknown'
    return props.paymentMethods?.[method] || method.charAt(0).toUpperCase() + method.slice(1)
}

const getStatusClass = (sale) => {
    if (sale.is_completed) {
        return 'bg-green-100 text-green-800'
    } else if (sale.status === 'cancelled') {
        return 'bg-red-100 text-red-800'
    } else {
        return 'bg-yellow-100 text-yellow-800'
    }
}

const processRefund = (sale) => {
    if (confirm(`Process refund for sale ${sale.invoice_number}?`)) {
        router.visit(route('sales.show', sale.id))
    }
}

const exportToCSV = () => {
    window.location.href = route('sales.index', {
        ...filters.value,
        export: 'csv'
    })
}

// Watch filters with debounce
let debounceTimer = null
watch(filters, () => {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(() => {
        applyFilters()
    }, 500)
}, { deep: true })
</script>