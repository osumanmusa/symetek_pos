<template>
    <Head title="Purchase Orders" />
    
    <AppLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900">Purchase Orders</h2>
                    <p class="mt-1 text-sm text-gray-600">Manage your inventory purchases from suppliers</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link :href="route('products.index')" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition">
                        Products
                    </Link>
                    <Link :href="route('suppliers.index')" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition">
                        Suppliers
                    </Link>
                    <Link :href="route('purchase-orders.create')" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                        <PlusIcon class="w-4 h-4 mr-2" />
                        New Purchase Order
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow p-4 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                                <DocumentTextIcon class="h-6 w-6 text-blue-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total POs</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.total }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-4 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                                <ClipboardDocumentCheckIcon class="h-6 w-6 text-purple-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">To Order</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.to_order }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-4 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                                <ClockIcon class="h-6 w-6 text-yellow-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Awaiting</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.awaiting_delivery }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-4 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-red-100 rounded-lg p-3">
                                <ExclamationTriangleIcon class="h-6 w-6 text-red-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Overdue</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.overdue }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-4 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                                <CheckCircleIcon class="h-6 w-6 text-green-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Received</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.received }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Advanced Filters -->
                <div class="mb-6 bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-medium text-gray-900">Filters</h3>
                            <div class="flex items-center space-x-3">
                                <button
                                    @click="toggleAdvancedFilters"
                                    class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900"
                                >
                                    <FunnelIcon class="w-4 h-4 mr-1" />
                                    {{ showAdvancedFilters ? 'Simple Filters' : 'Advanced Filters' }}
                                </button>
                                <button
                                    @click="exportData"
                                    :disabled="exporting"
                                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <ArrowDownTrayIcon v-if="!exporting" class="w-4 h-4 mr-1" />
                                    <ArrowPathIcon v-else class="w-4 h-4 mr-1 animate-spin" />
                                    {{ exporting ? 'Exporting...' : 'Export' }}
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <!-- Simple Filters -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                                </div>
                                <input
                                    type="text"
                                    v-model="filters.search"
                                    @input="debouncedFilter"
                                    placeholder="Search PO numbers, suppliers..."
                                    class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                />
                            </div>
                            
                            <select v-model="filters.status" @change="updateFilters" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Status</option>
                                <option value="draft">Draft</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="ordered">Ordered</option>
                                <option value="partially_received">Partially Received</option>
                                <option value="received">Received</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            
                            <select v-model="filters.payment_status" @change="updateFilters" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Payment Status</option>
                                <option value="pending">Pending</option>
                                <option value="partial">Partial</option>
                                <option value="paid">Paid</option>
                                <option value="overdue">Overdue</option>
                            </select>
                            
                            <select v-model="filters.supplier_id" @change="updateFilters" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Suppliers</option>
                                <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                                    {{ supplier.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Advanced Filters (Collapsible) -->
                        <div v-if="showAdvancedFilters" class="border-t border-gray-200 pt-4 mt-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                                    <input
                                        type="date"
                                        v-model="filters.date_from"
                                        @change="updateFilters"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                                    <input
                                        type="date"
                                        v-model="filters.date_from"
                                        @change="updateFilters"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                                    <select v-model="filters.sort_by" @change="updateFilters" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                        <option value="created_at">Created Date</option>
                                        <option value="order_date">Order Date</option>
                                        <option value="total_amount">Total Amount</option>
                                        <option value="po_number">PO Number</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Active Filters & Actions -->
                        <div class="flex flex-wrap items-center justify-between gap-3 mt-4">
                            <div class="flex flex-wrap gap-2">
                                <span v-if="activeFilterCount > 0" class="text-sm text-gray-500">
                                    {{ activeFilterCount }} active filter(s)
                                </span>
                                <span v-if="purchaseOrders.total" class="text-sm text-gray-500">
                                    Showing {{ purchaseOrders.from }}-{{ purchaseOrders.to }} of {{ purchaseOrders.total }} results
                                </span>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-if="activeFilterCount > 0"
                                    @click="resetFilters"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200"
                                >
                                    <XMarkIcon class="w-4 h-4 mr-1" />
                                    Clear Filters
                                </button>
                                <button
                                    @click="refreshData"
                                    :disabled="loading"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50"
                                >
                                    <ArrowPathIcon :class="['w-4 h-4 mr-1', loading ? 'animate-spin' : '']" />
                                    Refresh
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Purchase Orders Table -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" @click="sortBy('po_number')">
                                        <div class="flex items-center">
                                            PO Number
                                            <ChevronUpDownIcon class="w-4 h-4 ml-1" />
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Supplier
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" @click="sortBy('order_date')">
                                        <div class="flex items-center">
                                            Dates
                                            <ChevronUpDownIcon class="w-4 h-4 ml-1" />
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" @click="sortBy('total_amount')">
                                        <div class="flex items-center">
                                            Amount & Status
                                            <ChevronUpDownIcon class="w-4 h-4 ml-1" />
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="po in purchaseOrders.data" :key="po.id" 
                                    class="hover:bg-gray-50 transition-colors duration-150"
                                    :class="{
                                        'bg-red-50': isDeliveryOverdue(po),
                                        'bg-yellow-50': isPaymentOverdue(po)
                                    }"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div :class="getStatusIconBg(po.status)" class="h-10 w-10 flex-shrink-0 rounded-lg flex items-center justify-center">
                                                <DocumentTextIcon class="h-5 w-5" :class="getStatusIconColor(po.status)" />
                                            </div>
                                            <div class="ml-4">
                                                <div class="flex items-center space-x-2">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ po.po_number }}
                                                    </div>
                                                    <div v-if="isDeliveryOverdue(po)" class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        <ExclamationTriangleIcon class="w-3 h-3 mr-0.5" />
                                                        Overdue
                                                    </div>
                                                    <div v-if="isPaymentOverdue(po)" class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        <CurrencyDollarIcon class="w-3 h-3 mr-0.5" />
                                                        Payment Due
                                                    </div>
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    Created by: {{ po.user?.name }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    Items: {{ po.items_count || 0 }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ po.supplier?.name }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ po.supplier?.contact_person || 'No contact' }}
                                        </div>
                                        <div class="text-xs text-gray-500 flex items-center">
                                            <PhoneIcon class="w-3 h-3 mr-1" />
                                            {{ po.supplier?.phone || 'No phone' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="space-y-1">
                                            <div class="text-sm text-gray-900 flex items-center">
                                                <CalendarDaysIcon class="w-4 h-4 mr-1 text-gray-400" />
                                                Ordered: {{ formatDate(po.order_date) }}
                                            </div>
                                            <div class="text-xs" :class="getDeliveryDateClass(po)">
                                                <ClockIcon class="w-3 h-3 mr-1 inline" />
                                                Expected: {{ po.expected_delivery_date ? formatDate(po.expected_delivery_date) : 'Not set' }}
                                                <span v-if="isDeliveryOverdue(po)" class="ml-1 font-medium">(Overdue)</span>
                                            </div>
                                            <div v-if="po.delivery_date" class="text-xs text-green-600 font-medium flex items-center">
                                                <CheckCircleIcon class="w-3 h-3 mr-1" />
                                                Delivered: {{ formatDate(po.delivery_date) }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="space-y-2">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $currency(po.total_amount) }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    Paid: {{ $currency(po.amount_paid) }}
                                                    <span v-if="po.balance_due > 0" class="text-red-600 ml-1">
                                                        Due: {{ $currency(po.balance_due) }}
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="flex flex-wrap gap-1">
                                                <span :class="getStatusClasses(po.status)" class="px-2 py-1 inline-flex text-xs leading-4 font-semibold rounded-full">
                                                    {{ formatStatus(po.status) }}
                                                </span>
                                                <span :class="getPaymentStatusClasses(po.payment_status)" class="px-2 py-1 inline-flex text-xs leading-4 font-semibold rounded-full">
                                                    {{ formatPaymentStatus(po.payment_status) }}
                                                </span>
                                            </div>
                                            
                                            <!-- Progress bar for partially received -->
                                            <div v-if="po.status === 'partially_received' || po.status === 'ordered'">
                                                <div class="flex justify-between text-xs text-gray-500 mb-1">
                                                    <span>Receiving Progress</span>
                                                    <span>{{ po.received_percentage || 0 }}%</span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                    <div class="bg-green-600 h-1.5 rounded-full transition-all duration-300" 
                                                         :style="{ width: (po.received_percentage || 0) + '%' }">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Status hints -->
                                            <div v-if="po.status === 'approved'" class="text-xs text-blue-600 flex items-center">
                                                <BoltIcon class="w-3 h-3 mr-1" />
                                                Ready to order with supplier
                                            </div>
                                            <div v-else-if="po.status === 'ordered'" class="text-xs text-purple-600 flex items-center">
                                                <TruckIcon class="w-3 h-3 mr-1" />
                                                Awaiting delivery
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col space-y-2">
                                            <!-- Primary Action Button -->
                                            <div v-if="po.status === 'approved'">
                                                <button @click="markAsOrdered(po)" 
                                                        class="w-full inline-flex justify-center items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                                                    <CheckCircleIcon class="w-3 h-3 mr-1" />
                                                    Mark as Ordered
                                                </button>
                                            </div>
                                            <div v-else-if="po.status === 'ordered' || po.status === 'partially_received'">
                                                <Link :href="route('purchase-orders.receive', po.id)" 
                                                      class="w-full inline-flex justify-center items-center px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition">
                                                    <TruckIcon class="w-3 h-3 mr-1" />
                                                    Receive Goods
                                                </Link>
                                            </div>
                                            <div v-else-if="po.status === 'draft' || po.status === 'pending'">
                                                <button @click="approveAndOrder(po)" 
                                                        class="w-full inline-flex justify-center items-center px-3 py-1.5 bg-purple-600 text-white text-xs font-medium rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition">
                                                    <CheckCircleIcon class="w-3 h-3 mr-1" />
                                                    Approve & Order
                                                </button>
                                            </div>
                                            
                                            <!-- Action Links -->
                                            <div class="flex flex-wrap gap-2">
                                                <Link :href="route('purchase-orders.show', po.id)" 
                                                      class="inline-flex items-center text-blue-600 hover:text-blue-900 text-xs font-medium">
                                                    <EyeIcon class="w-3 h-3 mr-1" />
                                                    View
                                                </Link>
                                                
                                                <Link v-if="canEdit(po)" 
                                                      :href="route('purchase-orders.edit', po.id)" 
                                                      class="inline-flex items-center text-green-600 hover:text-green-900 text-xs font-medium">
                                                    <PencilIcon class="w-3 h-3 mr-1" />
                                                    Edit
                                                </Link>
                                                
                                            <Link :href="route('purchase-orders.print', po.id)" 
      class="inline-flex items-center text-gray-600 hover:text-gray-900 text-xs font-medium"
      target="_blank">
    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
    </svg>
    Print
</Link>
                                                
                                                <button v-if="canCancel(po)" 
                                                        @click="confirmCancel(po)" 
                                                        class="inline-flex items-center text-red-600 hover:text-red-900 text-xs font-medium">
                                                    <XCircleIcon class="w-3 h-3 mr-1" />
                                                    Cancel
                                                </button>
                                            </div>
                                            
                                            <!-- Quick Complete -->
                                            <div v-if="po.status === 'partially_received' && isFullyReceived(po)">
                                                <button @click="markAsFullyReceived(po)" 
                                                        class="w-full inline-flex justify-center items-center px-2 py-1 bg-indigo-600 text-white text-xs font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                                                    <CheckIcon class="w-3 h-3 mr-1" />
                                                    Mark Fully Received
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Loading State -->
                    <div v-if="loading && (!purchaseOrders.data || purchaseOrders.data.length === 0)" class="text-center py-12">
                        <ArrowPathIcon class="mx-auto h-8 w-8 text-gray-400 animate-spin" />
                        <p class="mt-2 text-sm text-gray-500">Loading purchase orders...</p>
                    </div>

                    <!-- Empty State -->
                    <div v-if="!loading && (!purchaseOrders.data || purchaseOrders.data.length === 0)" class="text-center py-12">
                        <DocumentTextIcon class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No purchase orders found</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ hasActiveFilters ? 'Try changing your filters' : 'Get started by creating your first purchase order' }}
                        </p>
                        <div class="mt-6">
                            <Link :href="route('purchase-orders.create')" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                                <PlusIcon class="w-4 h-4 mr-2" />
                                New Purchase Order
                            </Link>
                            <button v-if="hasActiveFilters" @click="resetFilters" class="ml-3 inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition">
                                Clear Filters
                            </button>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50" v-if="purchaseOrders.data && purchaseOrders.data.length > 0">
                        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                            <div class="text-sm text-gray-700">
                                Showing <span class="font-medium">{{ purchaseOrders.from }}</span> to 
                                <span class="font-medium">{{ purchaseOrders.to }}</span> of 
                                <span class="font-medium">{{ purchaseOrders.total }}</span> results
                            </div>
                            <Pagination :links="purchaseOrders.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <ConfirmationModal :show="showDeleteModal" @close="showDeleteModal = false">
            <template #title>
                Delete Purchase Order
            </template>
            <template #content>
                <!-- Delete modal content -->
            </template>
        </ConfirmationModal>

        <ConfirmationModal :show="showCancelModal" @close="showCancelModal = false">
            <template #title>
                Cancel Purchase Order
            </template>
            <template #content>
                <!-- Cancel modal content -->
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import Pagination from '@/Components/Pagination.vue';

// Heroicons
import {
    PlusIcon,
    DocumentTextIcon,
    ClipboardDocumentCheckIcon,
    ClockIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    MagnifyingGlassIcon,
    FunnelIcon,
    ArrowDownTrayIcon,
    ArrowPathIcon,
    XMarkIcon,
    ChevronUpDownIcon,
    PhoneIcon,
    CalendarDaysIcon,
    TruckIcon,
    EyeIcon,
    PencilIcon,
    PrinterIcon,
    XCircleIcon,
    CheckIcon,
    BoltIcon,
    CurrencyDollarIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    purchaseOrders: Object,
    stats: Object,
    suppliers: Array,
    filters: {
        type: Object,
        default: () => ({
            search: '',
            status: '',
            payment_status: '',
            supplier_id: '',
            date_from: '',
            date_to: '',
            sort_by: 'created_at',
            sort_order: 'desc'
        })
    }
});

// Refs
const showAdvancedFilters = ref(false);
const showDeleteModal = ref(false);
const showCancelModal = ref(false);
const poToDelete = ref(null);
const poToCancel = ref(null);
const loading = ref(false);
const exporting = ref(false);

// Filters
const filters = ref({
    search: props.filters.search || '',
    status: props.filters.status || '',
    payment_status: props.filters.payment_status || '',
    supplier_id: props.filters.supplier_id || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
    sort_by: props.filters.sort_by || 'created_at',
    sort_order: props.filters.sort_order || 'desc'
});

// Computed
const activeFilterCount = computed(() => {
    return Object.values(filters.value).filter(value => 
        value !== '' && value !== null && value !== undefined
    ).length - 2; // Subtract sort_by and sort_order
});

const hasActiveFilters = computed(() => activeFilterCount.value > 0);

// Methods
const debouncedFilter = debounce(() => {
    updateFilters();
}, 500);

const updateFilters = () => {
    router.get(route('purchase-orders.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onStart: () => loading.value = true,
        onFinish: () => loading.value = false
    });
};

const resetFilters = () => {
    filters.value = {
        search: '',
        status: '',
        payment_status: '',
        supplier_id: '',
        date_from: '',
        date_to: '',
        sort_by: 'created_at',
        sort_order: 'desc'
    };
    updateFilters();
};

const toggleAdvancedFilters = () => {
    showAdvancedFilters.value = !showAdvancedFilters.value;
};

const sortBy = (field) => {
    if (filters.value.sort_by === field) {
        filters.value.sort_order = filters.value.sort_order === 'asc' ? 'desc' : 'asc';
    } else {
        filters.value.sort_by = field;
        filters.value.sort_order = 'desc';
    }
    updateFilters();
};

const refreshData = () => {
    updateFilters();
};

const exportData = () => {
    exporting.value = true;
    
    // Use the new route name
    const baseUrl = route('export.purchase-orders');
    console.log('Export URL:', baseUrl);
    
    // Build query parameters
    const params = new URLSearchParams();
    
    // Add filters
    const filterKeys = ['search', 'status', 'payment_status', 'supplier_id', 'date_from', 'date_to', 'sort_by', 'sort_order'];
    
    filterKeys.forEach(key => {
        const value = filters.value[key];
        if (value !== null && value !== undefined && value !== '') {
            params.append(key, value);
        }
    });
    
    const queryString = params.toString();
    const fullUrl = queryString ? `${baseUrl}?${queryString}` : baseUrl;
    
    console.log('Full URL with filters:', fullUrl);
    
    // Open in new tab
    window.open(fullUrl, '_blank');
    
    exporting.value = false;
};

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

const isDeliveryOverdue = (po) => {
    if (!po.expected_delivery_date || po.status === 'received' || po.status === 'cancelled') {
        return false;
    }
    const expectedDate = new Date(po.expected_delivery_date);
    const today = new Date();
    return expectedDate < today;
};

const isPaymentOverdue = (po) => {
    return po.payment_status === 'overdue' || (po.balance_due > 0 && po.delivery_date && 
           new Date(po.delivery_date) < new Date(Date.now() - 30 * 24 * 60 * 60 * 1000));
};

const getDeliveryDateClass = (po) => {
    if (isDeliveryOverdue(po)) return 'text-red-600 font-medium';
    if (!po.expected_delivery_date) return 'text-gray-500';
    return 'text-gray-500';
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

const getStatusIconBg = (status) => {
    const classes = {
        'draft': 'bg-gray-100',
        'pending': 'bg-yellow-100',
        'approved': 'bg-blue-100',
        'ordered': 'bg-purple-100',
        'partially_received': 'bg-indigo-100',
        'received': 'bg-green-100',
        'cancelled': 'bg-red-100',
    };
    return classes[status] || 'bg-gray-100';
};

const getStatusIconColor = (status) => {
    const classes = {
        'draft': 'text-gray-600',
        'pending': 'text-yellow-600',
        'approved': 'text-blue-600',
        'ordered': 'text-purple-600',
        'partially_received': 'text-indigo-600',
        'received': 'text-green-600',
        'cancelled': 'text-red-600',
    };
    return classes[status] || 'text-gray-600';
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

const canEdit = (po) => ['draft', 'pending'].includes(po.status);
const canCancel = (po) => !['received', 'cancelled'].includes(po.status);
const isFullyReceived = (po) => {
    if (!po.items || po.items.length === 0) return false;
    return po.items.every(item => item.received_quantity >= item.quantity);
};

// Action methods
const markAsOrdered = (po) => {
    if (confirm(`Mark Purchase Order ${po.po_number} as "Ordered"?`)) {
        router.post(route('purchase-orders.order', po.id), {}, {
            preserveScroll: true,
            onSuccess: () => refreshData()
        });
    }
};

const approveAndOrder = (po) => {
    if (confirm(`Approve and mark Purchase Order ${po.po_number} as "Ordered"?`)) {
        router.post(route('purchase-orders.approve-and-order', po.id), {}, {
            preserveScroll: true,
            onSuccess: () => refreshData()
        });
    }
};

const markAsFullyReceived = (po) => {
    if (confirm(`Mark Purchase Order ${po.po_number} as fully received?`)) {
        router.post(route('purchase-orders.receive-full', po.id), {}, {
            preserveScroll: true,
            onSuccess: () => refreshData()
        });
    }
};

const confirmDelete = (po) => {
    poToDelete.value = po;
    showDeleteModal.value = true;
};

const confirmCancel = (po) => {
    poToCancel.value = po;
    showCancelModal.value = true;
};

// Initialize
onMounted(() => {
    // Any initialization code
});
</script>

<style scoped>
/* Add any custom styles here */
.table-row-hover:hover {
    background-color: #f9fafb;
}

.status-badge {
    transition: all 0.2s ease;
}

.status-badge:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.progress-bar {
    transition: width 0.3s ease-in-out;
}
</style>