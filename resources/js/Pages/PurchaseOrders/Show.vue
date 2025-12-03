<template>
    <Head :title="`Purchase Order - ${purchaseOrder.po_number}`" />
    
    <AppLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <div class="flex items-center space-x-3">
                        <h2 class="text-2xl font-semibold text-gray-900">Purchase Order: {{ purchaseOrder.po_number }}</h2>
                        <div class="flex items-center space-x-2">
                            <span :class="getStatusClasses(purchaseOrder.status)" class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full">
                                {{ formatStatus(purchaseOrder.status) }}
                            </span>
                            <span :class="getPaymentStatusClasses(purchaseOrder.payment_status)" class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full">
                                {{ formatPaymentStatus(purchaseOrder.payment_status) }}
                            </span>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 mt-2 text-sm text-gray-600">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Created: {{ formatDateTime(purchaseOrder.created_at) }}
                        </span>
                        <span v-if="purchaseOrder.user" class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            By: {{ purchaseOrder.user.name }}
                        </span>
                        <span v-if="purchaseOrder.supplier" class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Supplier: {{ purchaseOrder.supplier.name }}
                        </span>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link :href="route('purchase-orders.index')" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to List
                    </Link>
                    <Link v-if="canEdit(purchaseOrder)" :href="route('purchase-orders.edit', purchaseOrder.id)" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Success/Error Messages -->
                <div v-if="$page.props.flash.success" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ $page.props.flash.success }}
                    </div>
                </div>
                
                <div v-if="$page.props.flash.error" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        {{ $page.props.flash.error }}
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Column: Order Details & Items -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Order Summary Card -->
                        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                                <h3 class="text-lg font-medium text-gray-900">Order Summary</h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Supplier Information -->
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                            Supplier Information
                                        </h4>
                                        <div class="space-y-2">
                                            <div class="flex">
                                                <span class="text-sm font-medium text-gray-600 w-24">Name:</span>
                                                <span class="text-sm text-gray-900">{{ purchaseOrder.supplier?.name }}</span>
                                            </div>
                                            <div class="flex">
                                                <span class="text-sm font-medium text-gray-600 w-24">Contact:</span>
                                                <span class="text-sm text-gray-900">{{ purchaseOrder.supplier?.contact_person || 'N/A' }}</span>
                                            </div>
                                            <div class="flex">
                                                <span class="text-sm font-medium text-gray-600 w-24">Phone:</span>
                                                <span class="text-sm text-gray-900">{{ purchaseOrder.supplier?.phone || 'N/A' }}</span>
                                            </div>
                                            <div class="flex">
                                                <span class="text-sm font-medium text-gray-600 w-24">Email:</span>
                                                <span class="text-sm text-gray-900">{{ purchaseOrder.supplier?.email || 'N/A' }}</span>
                                            </div>
                                            <div class="flex">
                                                <span class="text-sm font-medium text-gray-600 w-24">Address:</span>
                                                <span class="text-sm text-gray-900">{{ purchaseOrder.supplier?.address || 'N/A' }}</span>
                                            </div>
                                            <div v-if="purchaseOrder.supplier?.credit_limit > 0" class="flex">
                                                <span class="text-sm font-medium text-gray-600 w-24">Credit Limit:</span>
                                                <span class="text-sm" :class="purchaseOrder.supplier?.current_balance >= purchaseOrder.supplier?.credit_limit ? 'text-red-600 font-medium' : 'text-gray-900'">
                                                    {{ $currency(purchaseOrder.supplier?.credit_limit) }}
                                                    <span v-if="purchaseOrder.supplier?.current_balance > 0" class="ml-2">
                                                        (Used: {{ $currency(purchaseOrder.supplier?.current_balance) }})
                                                    </span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Order Information -->
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                            Order Information
                                        </h4>
                                        <div class="space-y-2">
                                            <div class="flex">
                                                <span class="text-sm font-medium text-gray-600 w-32">Order Date:</span>
                                                <span class="text-sm text-gray-900">{{ formatDate(purchaseOrder.order_date) }}</span>
                                            </div>
                                            <div class="flex">
                                                <span class="text-sm font-medium text-gray-600 w-32">Expected Delivery:</span>
                                                <span class="text-sm" :class="isDeliveryOverdue ? 'text-red-600 font-medium' : 'text-gray-900'">
                                                    {{ purchaseOrder.expected_delivery_date ? formatDate(purchaseOrder.expected_delivery_date) : 'Not set' }}
                                                    <span v-if="isDeliveryOverdue" class="ml-1">(Overdue)</span>
                                                </span>
                                            </div>
                                            <div class="flex" v-if="purchaseOrder.delivery_date">
                                                <span class="text-sm font-medium text-gray-600 w-32">Actual Delivery:</span>
                                                <span class="text-sm text-green-600 font-medium">{{ formatDate(purchaseOrder.delivery_date) }}</span>
                                            </div>
                                            <div class="flex">
                                                <span class="text-sm font-medium text-gray-600 w-32">Shipping Address:</span>
                                                <span class="text-sm text-gray-900">{{ purchaseOrder.shipping_address || purchaseOrder.supplier?.address || 'N/A' }}</span>
                                            </div>
                                            <div class="flex">
                                                <span class="text-sm font-medium text-gray-600 w-32">Payment Method:</span>
                                                <span class="text-sm text-gray-900">{{ purchaseOrder.payment_method || 'Not specified' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Notes -->
                                <div v-if="purchaseOrder.notes" class="mt-6 pt-6 border-t border-gray-200">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                        </svg>
                                        Notes
                                    </h4>
                                    <p class="text-sm text-gray-600 bg-gray-50 p-3 rounded-lg whitespace-pre-line">{{ purchaseOrder.notes }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items Card -->
                        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                        Order Items
                                    </h3>
                                    <div class="flex items-center space-x-4">
                                        <span class="text-sm text-gray-500">{{ purchaseOrder.items?.length || 0 }} items</span>
                                        <div class="text-sm px-2 py-1 rounded-md" :class="receivingProgressColor">
                                            {{ receivedPercentage }}% Received
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Cost</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Cost</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="item in purchaseOrder.items" :key="item.id" class="hover:bg-gray-50">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center">
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ item.product?.name }}
                                                        </div>
                                                        <div class="text-xs text-gray-500">
                                                            SKU: {{ item.product?.sku || 'N/A' }} | 
                                                            Category: {{ item.product?.category?.name || 'Uncategorized' }}
                                                        </div>
                                                        <div class="text-xs text-gray-500 mt-1">
                                                            Current Stock: {{ item.product?.stock_quantity || 0 }} {{ item.product?.unit || 'pcs' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ item.quantity }}</div>
                                                <div class="text-xs text-gray-500">{{ item.product?.unit || 'pcs' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $currency(item.unit_cost) }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $currency(item.total_cost) }}</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="space-y-1">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-sm" :class="getReceivedStatusClass(item)">
                                                            {{ item.received_quantity || 0 }} / {{ item.quantity }}
                                                        </span>
                                                        <span class="text-xs text-gray-500">
                                                            {{ getReceivedPercentage(item) }}%
                                                        </span>
                                                    </div>
                                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                        <div class="h-1.5 rounded-full transition-all duration-300" 
                                                             :class="getProgressBarColor(item)"
                                                             :style="{ width: getReceivedPercentage(item) + '%' }">
                                                        </div>
                                                    </div>
                                                    <div class="flex flex-wrap gap-1 text-xs">
                                                        <span v-if="item.damaged_quantity > 0" class="text-red-600">
                                                            {{ item.damaged_quantity }} damaged
                                                        </span>
                                                        <span v-if="item.returned_quantity > 0" class="text-yellow-600">
                                                            {{ item.returned_quantity }} returned
                                                        </span>
                                                        <span v-if="item.quantity - (item.received_quantity || 0) > 0" class="text-gray-500">
                                                            {{ item.quantity - (item.received_quantity || 0) }} remaining
                                                        </span>
                                                    </div>
                                                    <div v-if="item.notes" class="text-xs text-gray-500 italic mt-1">
                                                        "{{ item.notes }}"
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <!-- Financial Summary -->
                                    <tfoot class="bg-gray-50">
                                        <tr>
                                            <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-900">Subtotal:</td>
                                            <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ $currency(purchaseOrder.subtotal) }}</td>
                                            <td></td>
                                        </tr>
                                        <tr v-if="purchaseOrder.shipping_cost > 0">
                                            <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-900">Shipping Cost:</td>
                                            <td class="px-6 py-3 text-sm text-gray-900">+ {{ $currency(purchaseOrder.shipping_cost) }}</td>
                                            <td></td>
                                        </tr>
                                        <tr v-if="purchaseOrder.tax_amount > 0">
                                            <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-900">Tax Amount:</td>
                                            <td class="px-6 py-3 text-sm text-gray-900">+ {{ $currency(purchaseOrder.tax_amount) }}</td>
                                            <td></td>
                                        </tr>
                                        <tr v-if="purchaseOrder.discount_amount > 0">
                                            <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-900">Discount:</td>
                                            <td class="px-6 py-3 text-sm font-medium text-red-600">- {{ $currency(purchaseOrder.discount_amount) }}</td>
                                            <td></td>
                                        </tr>
                                        <tr class="border-t border-gray-200">
                                            <td colspan="3" class="px-6 py-3 text-right text-sm font-bold text-gray-900">Total Amount:</td>
                                            <td class="px-6 py-3 text-sm font-bold text-gray-900">{{ $currency(purchaseOrder.total_amount) }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-900">Amount Paid:</td>
                                            <td class="px-6 py-3 text-sm font-medium text-green-600">{{ $currency(purchaseOrder.amount_paid) }}</td>
                                            <td></td>
                                        </tr>
                                        <tr class="border-t border-gray-300">
                                            <td colspan="3" class="px-6 py-3 text-right text-sm font-bold" :class="purchaseOrder.balance_due > 0 ? 'text-red-700' : 'text-green-700'">
                                                Balance Due:
                                            </td>
                                            <td class="px-6 py-3 text-sm font-bold" :class="purchaseOrder.balance_due > 0 ? 'text-red-700' : 'text-green-700'">
                                                {{ $currency(purchaseOrder.balance_due) }}
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Payment History Card -->
                        <div v-if="purchaseOrder.amount_paid > 0" class="bg-white shadow-sm rounded-lg overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Payment History
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">Total Payment Received</div>
                                            <div class="text-xs text-gray-500">{{ formatDate(purchaseOrder.payment_date) || 'No payment date' }}</div>
                                        </div>
                                        <div class="text-lg font-bold text-green-600">
                                            {{ $currency(purchaseOrder.amount_paid) }}
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                                            <div class="text-sm font-medium text-blue-700">Payment Method</div>
                                            <div class="text-lg font-bold text-blue-900 mt-1">{{ purchaseOrder.payment_method || 'Not specified' }}</div>
                                        </div>
                                        <div class="text-center p-4" :class="purchaseOrder.balance_due > 0 ? 'bg-red-50' : 'bg-green-50'">
                                            <div class="text-sm font-medium" :class="purchaseOrder.balance_due > 0 ? 'text-red-700' : 'text-green-700'">
                                                {{ purchaseOrder.balance_due > 0 ? 'Balance Due' : 'Fully Paid' }}
                                            </div>
                                            <div class="text-lg font-bold mt-1" :class="purchaseOrder.balance_due > 0 ? 'text-red-900' : 'text-green-900'">
                                                {{ $currency(Math.abs(purchaseOrder.balance_due)) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Activity Log Card -->
                        <div v-if="purchaseOrder.activities && purchaseOrder.activities.length > 0" class="bg-white shadow-sm rounded-lg overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Activity Log
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="flow-root">
                                    <ul role="list" class="-mb-8">
                                        <li v-for="(activity, activityIdx) in purchaseOrder.activities" :key="activity.id">
                                            <div class="relative pb-8">
                                                <span v-if="activityIdx !== purchaseOrder.activities.length - 1" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                                <div class="relative flex space-x-3">
                                                    <div>
                                                        <span :class="getActivityIconBg(activity.description)" class="h-8 w-8 rounded-full flex items-center justify-center">
                                                            <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                <path v-if="activity.description.includes('created')" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                                                                <path v-else-if="activity.description.includes('updated')" fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd" />
                                                                <path v-else-if="activity.description.includes('received')" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                                <path v-else fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                                            </svg>
                                                        </span>
                                                    </div>
                                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                        <div>
                                                            <p class="text-sm text-gray-800">
                                                                {{ activity.description }}
                                                                <span v-if="activity.causer" class="font-medium text-gray-900">
                                                                    by {{ activity.causer.name }}
                                                                </span>
                                                            </p>
                                                            <div v-if="activity.properties" class="mt-1 text-xs text-gray-500 space-y-1">
                                                                <div v-if="activity.properties.old_status && activity.properties.new_status">
                                                                    Status changed from 
                                                                    <span class="font-medium">{{ formatStatus(activity.properties.old_status) }}</span> to 
                                                                    <span class="font-medium">{{ formatStatus(activity.properties.new_status) }}</span>
                                                                </div>
                                                                <div v-if="activity.properties.items_count">
                                                                    {{ activity.properties.items_count }} items
                                                                </div>
                                                                <div v-if="activity.properties.total_amount">
                                                                    Total: {{ $currency(activity.properties.total_amount) }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                            <time :datetime="activity.created_at">{{ formatDateTime(activity.created_at) }}</time>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Actions & Status -->
                    <div class="space-y-6">
                        <!-- Status & Quick Actions Card -->
                        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                                <h3 class="text-lg font-medium text-gray-900">Order Actions</h3>
                            </div>
                            <div class="p-6 space-y-4">
                                <!-- Status Info -->
                                <div :class="getStatusInfoClass(purchaseOrder.status)" class="p-4 rounded-lg">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5" :class="getStatusIconClass(purchaseOrder.status)" fill="currentColor" viewBox="0 0 20 20">
                                                <path v-if="purchaseOrder.status === 'draft'" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                                                <path v-else-if="purchaseOrder.status === 'pending'" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                <path v-else-if="purchaseOrder.status === 'approved'" fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                <path v-else-if="purchaseOrder.status === 'ordered'" fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                                <path v-else-if="purchaseOrder.status === 'received'" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                <path v-else fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium">
                                                {{ getStatusTitle(purchaseOrder.status) }}
                                            </h3>
                                            <div class="mt-2 text-sm">
                                                {{ getStatusDescription(purchaseOrder.status) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="space-y-3">
                                    <!-- Receive Goods Button -->
                                    <Link
                                        v-if="['ordered', 'partially_received'].includes(purchaseOrder.status)"
                                        :href="route('purchase-orders.receive', purchaseOrder.id)"
                                        class="w-full flex justify-center items-center px-4 py-3 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition"
                                    >
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                        </svg>
                                        Receive Goods
                                    </Link>

                                    <!-- Mark as Ordered Button -->
                                    <button
                                        v-if="purchaseOrder.status === 'approved'"
                                        @click="markAsOrdered"
                                        class="w-full flex justify-center items-center px-4 py-3 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition"
                                    >
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Mark as Ordered
                                    </button>

                                    <!-- Approve Button -->
                                    <button
                                        v-if="purchaseOrder.status === 'pending'"
                                        @click="approveOrder"
                                        class="w-full flex justify-center items-center px-4 py-3 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition"
                                    >
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Approve Order
                                    </button>

                                    <!-- Approve & Order Button -->
                                    <button
                                        v-if="['draft', 'pending'].includes(purchaseOrder.status)"
                                        @click="approveAndOrder"
                                        class="w-full flex justify-center items-center px-4 py-3 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition"
                                    >
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Approve & Order
                                    </button>

                                    <!-- Mark Fully Received Button -->
                                    <button
                                        v-if="purchaseOrder.status === 'partially_received' && isFullyReceived"
                                        @click="markAsFullyReceived"
                                        class="w-full flex justify-center items-center px-4 py-3 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition"
                                    >
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Mark Fully Received
                                    </button>

                                    <!-- Edit Button -->
                                    <Link
                                        v-if="canEdit(purchaseOrder)"
                                        :href="route('purchase-orders.edit', purchaseOrder.id)"
                                        class="w-full flex justify-center items-center px-4 py-3 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition"
                                    >
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit Order
                                    </Link>

                                    <!-- Cancel Button -->
                                    <button
                                        v-if="!['received', 'cancelled'].includes(purchaseOrder.status)"
                                        @click="cancelOrder"
                                        class="w-full flex justify-center items-center px-4 py-3 border border-red-300 text-red-700 text-sm font-medium rounded-lg hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition"
                                    >
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Cancel Order
                                    </button>

                                    <!-- Update Payment Button -->
                                    <button
                                        v-if="purchaseOrder.status === 'received' && purchaseOrder.balance_due > 0"
                                        @click="showPaymentModal = true"
                                        class="w-full flex justify-center items-center px-4 py-3 bg-yellow-600 text-white text-sm font-medium rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition"
                                    >
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Update Payment
                                    </button>

                                    <!-- Mark as Paid Button -->
                                    <button
                                        v-if="purchaseOrder.status === 'received' && purchaseOrder.balance_due > 0"
                                        @click="markAsPaid"
                                        class="w-full flex justify-center items-center px-4 py-3 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition"
                                    >
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Mark as Fully Paid
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Order Statistics Card -->
                        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                                <h3 class="text-lg font-medium text-gray-900">Order Statistics</h3>
                            </div>
                            <div class="p-6 space-y-6">
                                <!-- Progress Section -->
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm font-medium text-gray-700">Receiving Progress</span>
                                        <span class="text-sm font-bold" :class="receivingProgressColor">
                                            {{ receivedPercentage }}%
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        <div class="h-3 rounded-full transition-all duration-500" 
                                             :class="receivingProgressBarColor"
                                             :style="{ width: receivedPercentage + '%' }">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2 mt-3 text-center">
                                        <div class="p-2 bg-green-50 rounded">
                                            <div class="text-xl font-bold text-green-700">{{ receivedItemsCount }}</div>
                                            <div class="text-xs text-green-600">Fully Received</div>
                                        </div>
                                        <div class="p-2 bg-yellow-50 rounded">
                                            <div class="text-xl font-bold text-yellow-700">{{ partiallyReceivedCount }}</div>
                                            <div class="text-xs text-yellow-600">Partial</div>
                                        </div>
                                        <div class="p-2 bg-red-50 rounded">
                                            <div class="text-xl font-bold text-red-700">{{ notReceivedCount }}</div>
                                            <div class="text-xs text-red-600">Pending</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Timeline -->
                                <div class="pt-4 border-t border-gray-200">
                                    <h4 class="text-sm font-medium text-gray-700 mb-3">Timeline</h4>
                                    <div class="space-y-3">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
                                            <div class="flex-1">
                                                <div class="text-sm font-medium text-gray-900">Created</div>
                                                <div class="text-xs text-gray-500">{{ formatDateTime(purchaseOrder.created_at) }}</div>
                                            </div>
                                        </div>
                                        <div v-if="purchaseOrder.order_date" class="flex items-center">
                                            <div class="flex-shrink-0 w-2 h-2 bg-purple-500 rounded-full mr-3"></div>
                                            <div class="flex-1">
                                                <div class="text-sm font-medium text-gray-900">Ordered</div>
                                                <div class="text-xs text-gray-500">{{ formatDateTime(purchaseOrder.order_date) }}</div>
                                            </div>
                                        </div>
                                        <div v-if="purchaseOrder.expected_delivery_date" class="flex items-center">
                                            <div class="flex-shrink-0 w-2 h-2" :class="isDeliveryOverdue ? 'bg-red-500' : 'bg-yellow-500'"></div>
                                            <div class="flex-1 ml-3">
                                                <div class="text-sm font-medium text-gray-900">Expected Delivery</div>
                                                <div class="text-xs" :class="isDeliveryOverdue ? 'text-red-600' : 'text-gray-500'">
                                                    {{ formatDate(purchaseOrder.expected_delivery_date) }}
                                                    <span v-if="isDeliveryOverdue" class="font-medium"> (Overdue)</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="purchaseOrder.delivery_date" class="flex items-center">
                                            <div class="flex-shrink-0 w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                                            <div class="flex-1">
                                                <div class="text-sm font-medium text-gray-900">Delivered</div>
                                                <div class="text-xs text-gray-500">{{ formatDateTime(purchaseOrder.delivery_date) }}</div>
                                            </div>
                                        </div>
                                        <div v-if="purchaseOrder.updated_at !== purchaseOrder.created_at" class="flex items-center">
                                            <div class="flex-shrink-0 w-2 h-2 bg-gray-400 rounded-full mr-3"></div>
                                            <div class="flex-1">
                                                <div class="text-sm font-medium text-gray-900">Last Updated</div>
                                                <div class="text-xs text-gray-500">{{ formatDateTime(purchaseOrder.updated_at) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Financial Summary -->
                                <div class="pt-4 border-t border-gray-200">
                                    <h4 class="text-sm font-medium text-gray-700 mb-3">Financial Summary</h4>
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Order Value</span>
                                            <span class="text-sm font-medium">{{ $currency(purchaseOrder.total_amount) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Amount Paid</span>
                                            <span class="text-sm font-medium text-green-600">{{ $currency(purchaseOrder.amount_paid) }}</span>
                                        </div>
                                        <div class="flex justify-between pt-2 border-t border-gray-200">
                                            <span class="text-sm font-medium" :class="purchaseOrder.balance_due > 0 ? 'text-red-700' : 'text-green-700'">
                                                {{ purchaseOrder.balance_due > 0 ? 'Balance Due' : 'Payment Status' }}
                                            </span>
                                            <span class="text-sm font-bold" :class="purchaseOrder.balance_due > 0 ? 'text-red-700' : 'text-green-700'">
                                                {{ $currency(Math.abs(purchaseOrder.balance_due)) }}
                                            </span>
                                        </div>
                                        <div v-if="purchaseOrder.payment_date" class="text-xs text-gray-500 text-center pt-2">
                                            Last payment: {{ formatDate(purchaseOrder.payment_date) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Modal -->
        <ConfirmationModal :show="showPaymentModal" @close="showPaymentModal = false">
            <template #title>
                Update Payment
            </template>
            <template #content>
                <form @submit.prevent="updatePayment" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Amount Paid *</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">₵</span>
                            </div>
                            <input
                                type="number"
                                v-model="paymentForm.amount_paid"
                                min="0"
                                :max="purchaseOrder.balance_due"
                                step="0.01"
                                class="pl-7 pr-12 py-2 block w-full border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                required
                            />
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <span class="text-gray-500 sm:text-sm">GHS</span>
                            </div>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">
                            Balance due: {{ $currency(purchaseOrder.balance_due) }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Payment Method *</label>
                        <select
                            v-model="paymentForm.payment_method"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            required
                        >
                            <option value="">Select Method</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="mobile_money">Mobile Money</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Payment Date *</label>
                        <input
                            type="date"
                            v-model="paymentForm.payment_date"
                            :max="new Date().toISOString().split('T')[0]"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            required
                        />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
                        <textarea
                            v-model="paymentForm.notes"
                            rows="2"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            placeholder="Payment reference or notes..."
                        ></textarea>
                    </div>
                </form>
            </template>
            <template #footer>
                <button
                    @click="showPaymentModal = false"
                    class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    Cancel
                </button>
                <button
                    @click="updatePayment"
                    :disabled="paymentForm.processing"
                    class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                >
                    {{ paymentForm.processing ? 'Processing...' : 'Update Payment' }}
                </button>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

const props = defineProps({
    purchaseOrder: Object,
    itemsStats: Object,
});

const showPaymentModal = ref(false);

// Payment form
const paymentForm = useForm({
    amount_paid: 0,
    payment_method: '',
    payment_date: new Date().toISOString().split('T')[0],
    notes: '',
});

// Computed properties
const isDeliveryOverdue = computed(() => {
    if (!props.purchaseOrder.expected_delivery_date || 
        ['received', 'cancelled'].includes(props.purchaseOrder.status)) {
        return false;
    }
    const expectedDate = new Date(props.purchaseOrder.expected_delivery_date);
    const today = new Date();
    return expectedDate < today;
});

const receivedItemsCount = computed(() => {
    if (!props.purchaseOrder.items) return 0;
    return props.purchaseOrder.items.filter(item => 
        (item.received_quantity || 0) >= item.quantity
    ).length;
});

const partiallyReceivedCount = computed(() => {
    if (!props.purchaseOrder.items) return 0;
    return props.purchaseOrder.items.filter(item => {
        const received = item.received_quantity || 0;
        return received > 0 && received < item.quantity;
    }).length;
});

const notReceivedCount = computed(() => {
    if (!props.purchaseOrder.items) return 0;
    return props.purchaseOrder.items.filter(item => 
        (item.received_quantity || 0) === 0
    ).length;
});

const receivedPercentage = computed(() => {
    if (!props.purchaseOrder.items || props.purchaseOrder.items.length === 0) return 0;
    
    let totalOrdered = 0;
    let totalReceived = 0;
    
    props.purchaseOrder.items.forEach(item => {
        totalOrdered += parseFloat(item.quantity);
        totalReceived += parseFloat(item.received_quantity || 0);
    });
    
    return totalOrdered > 0 ? Math.round((totalReceived / totalOrdered) * 100) : 0;
});

const receivingProgressColor = computed(() => {
    if (receivedPercentage.value === 100) return 'text-green-700';
    if (receivedPercentage.value >= 50) return 'text-yellow-700';
    return 'text-red-700';
});

const receivingProgressBarColor = computed(() => {
    if (receivedPercentage.value === 100) return 'bg-green-600';
    if (receivedPercentage.value >= 50) return 'bg-yellow-500';
    return 'bg-red-500';
});

const isFullyReceived = computed(() => {
    if (!props.purchaseOrder.items) return false;
    return props.purchaseOrder.items.every(item => 
        (item.received_quantity || 0) >= item.quantity
    );
});

// Helper methods
const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-GH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const formatDateTime = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-GH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getStatusClasses = (status) => {
    const classes = {
        'draft': 'bg-gray-100 text-gray-800',
        'pending': 'bg-yellow-100 text-yellow-800',
        'approved': 'bg-blue-100 text-blue-800',
        'ordered': 'bg-purple-100 text-purple-800',
        'partially_received': 'bg-indigo-100 text-indigo-800',
        'received': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getPaymentStatusClasses = (status) => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'partial': 'bg-blue-100 text-blue-800',
        'paid': 'bg-green-100 text-green-800',
        'overdue': 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getActivityIconBg = (description) => {
    if (description.includes('created')) return 'bg-green-500';
    if (description.includes('updated')) return 'bg-blue-500';
    if (description.includes('deleted')) return 'bg-red-500';
    if (description.includes('ordered')) return 'bg-purple-500';
    if (description.includes('received')) return 'bg-green-500';
    if (description.includes('cancelled')) return 'bg-red-500';
    if (description.includes('approved')) return 'bg-green-500';
    return 'bg-gray-500';
};

const formatStatus = (status) => {
    const statusMap = {
        'draft': 'Draft',
        'pending': 'Pending',
        'approved': 'Approved',
        'ordered': 'Ordered',
        'partially_received': 'Partially Received',
        'received': 'Received',
        'cancelled': 'Cancelled',
    };
    return statusMap[status] || status;
};

const formatPaymentStatus = (status) => {
    const statusMap = {
        'pending': 'Pending',
        'partial': 'Partial',
        'paid': 'Paid',
        'overdue': 'Overdue',
    };
    return statusMap[status] || status;
};

const getStatusInfoClass = (status) => {
    const classes = {
        'draft': 'bg-gray-50 text-gray-700',
        'pending': 'bg-yellow-50 text-yellow-700',
        'approved': 'bg-blue-50 text-blue-700',
        'ordered': 'bg-purple-50 text-purple-700',
        'partially_received': 'bg-indigo-50 text-indigo-700',
        'received': 'bg-green-50 text-green-700',
        'cancelled': 'bg-red-50 text-red-700',
    };
    return classes[status] || 'bg-gray-50 text-gray-700';
};

const getStatusIconClass = (status) => {
    const classes = {
        'draft': 'text-gray-400',
        'pending': 'text-yellow-400',
        'approved': 'text-blue-400',
        'ordered': 'text-purple-400',
        'partially_received': 'text-indigo-400',
        'received': 'text-green-400',
        'cancelled': 'text-red-400',
    };
    return classes[status] || 'text-gray-400';
};

const getStatusTitle = (status) => {
    const titles = {
        'draft': 'Draft Order',
        'pending': 'Pending Approval',
        'approved': 'Approved - Ready to Order',
        'ordered': 'Ordered with Supplier',
        'partially_received': 'Partially Received',
        'received': 'Fully Received',
        'cancelled': 'Cancelled',
    };
    return titles[status] || status;
};

const getStatusDescription = (status) => {
    const descriptions = {
        'draft': 'This order is still in draft. You can edit it before sending for approval.',
        'pending': 'This order is waiting for approval. Once approved, you can mark it as ordered.',
        'approved': 'This order is approved and ready to be placed with the supplier.',
        'ordered': 'This order has been placed with the supplier. Awaiting delivery.',
        'partially_received': 'Some items have been received. Continue receiving the remaining items.',
        'received': 'All items have been received from the supplier.',
        'cancelled': 'This order has been cancelled and will not be processed further.',
    };
    return descriptions[status] || '';
};

const getReceivedStatusClass = (item) => {
    const received = item.received_quantity || 0;
    if (received >= item.quantity) return 'text-green-600 font-medium';
    if (received > 0) return 'text-yellow-600';
    return 'text-red-600';
};

const getProgressBarColor = (item) => {
    const received = item.received_quantity || 0;
    const percentage = (received / item.quantity) * 100;
    if (percentage >= 100) return 'bg-green-600';
    if (percentage >= 50) return 'bg-yellow-500';
    return 'bg-red-500';
};

const getReceivedPercentage = (item) => {
    const received = item.received_quantity || 0;
    return Math.round((received / item.quantity) * 100);
};

const canEdit = (po) => {
    return ['draft', 'pending'].includes(po.status);
};

// Action methods
const approveOrder = () => {
    if (confirm('Are you sure you want to approve this purchase order?')) {
        router.post(route('purchase-orders.approve', props.purchaseOrder.id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                router.reload();
            },
        });
    }
};

const markAsOrdered = () => {
    if (confirm('Mark this purchase order as "Ordered"? This confirms you have placed this order with the supplier.')) {
        router.post(route('purchase-orders.order', props.purchaseOrder.id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                router.reload();
            },
        });
    }
};

const approveAndOrder = () => {
    if (confirm('Approve and mark this purchase order as "Ordered"? This will immediately place the order with the supplier.')) {
        router.post(route('purchase-orders.approve-and-order', props.purchaseOrder.id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                router.reload();
            },
        });
    }
};

const markAsFullyReceived = () => {
    if (confirm('Mark this purchase order as fully received? This will update inventory and complete the order.')) {
        router.post(route('purchase-orders.receive-full', props.purchaseOrder.id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                router.reload();
            },
        });
    }
};

const cancelOrder = () => {
    if (confirm('Are you sure you want to cancel this purchase order?')) {
        router.post(route('purchase-orders.cancel', props.purchaseOrder.id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                router.reload();
            },
        });
    }
};

const markAsPaid = () => {
    if (confirm('Mark this purchase order as fully paid? This will update the payment status to "Paid".')) {
        router.post(route('purchase-orders.mark-paid', props.purchaseOrder.id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                router.reload();
            },
        });
    }
};

const updatePayment = () => {
    if (confirm('Update payment for this purchase order?')) {
        paymentForm.post(route('purchase-orders.update-payment', props.purchaseOrder.id), {
            preserveScroll: true,
            onSuccess: () => {
                showPaymentModal.value = false;
                paymentForm.reset();
                router.reload();
            },
        });
    }
};
</script>

<style scoped>
/* Add any custom styles here */
.status-badge {
    transition: all 0.2s ease;
}

.status-badge:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.progress-bar {
    transition: width 0.5s ease-in-out;
}

.table-row-hover:hover {
    background-color: #f9fafb;
}
</style>