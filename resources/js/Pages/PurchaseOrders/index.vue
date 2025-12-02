<template>
    <Head title="Purchase Orders" />
    
    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900">Purchase Orders</h2>
                    <p class="mt-1 text-sm text-gray-600">Manage your inventory purchases from suppliers</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route('products.index')" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Products
                    </Link>
                    <Link :href="route('suppliers.index')" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Suppliers
                    </Link>
                    <Link :href="route('purchase-orders.create')" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        New Purchase Order
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total POs</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.total }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">To Order</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.to_order }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Awaiting Delivery</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.awaiting_delivery }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Received</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.received }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters and Search -->
                <div class="mb-6 bg-white shadow-sm rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div class="relative md:col-span-2">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                type="text"
                                v-model="search"
                                placeholder="Search PO numbers, suppliers..."
                                class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            />
                        </div>
                        
                        <select v-model="statusFilter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Status</option>
                            <option value="draft">Draft</option>
                            <option value="pending">Pending Review</option>
                            <option value="approved">Approved (Ready to Order)</option>
                            <option value="ordered">Ordered (Placed with Supplier)</option>
                            <option value="partially_received">Partially Received</option>
                            <option value="received">Fully Received</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        
                        <input
                            type="date"
                            v-model="dateFilter"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
                        />
                        
                        <button @click="clearFilters" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200">
                            Clear Filters
                        </button>
                    </div>
                </div>

                <!-- Purchase Orders Table -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Purchase Order
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Supplier
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Dates
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Amount & Status
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="po in filteredPurchaseOrders" :key="po.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div :class="getStatusIconBg(po.status)" class="h-10 w-10 flex-shrink-0 rounded-lg flex items-center justify-center">
                                                <svg class="h-6 w-6" :class="getStatusIconColor(po.status)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                </svg>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 flex items-center">
                                                    {{ po.po_number }}
                                                    <span v-if="isDeliveryOverdue(po)" class="ml-2">
                                                        <svg class="h-4 w-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                        </svg>
                                                    </span>
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
                                        <div class="text-xs text-gray-500">
                                            📞 {{ po.supplier?.phone || 'No phone' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            Ordered: {{ formatDate(po.order_date) }}
                                        </div>
                                        <div class="text-xs" :class="isDeliveryOverdue(po) ? 'text-red-600 font-medium' : 'text-gray-500'">
                                            Expected: {{ po.expected_delivery_date ? formatDate(po.expected_delivery_date) : 'Not set' }}
                                            <span v-if="isDeliveryOverdue(po)" class="ml-1">(Overdue)</span>
                                        </div>
                                        <div v-if="po.delivery_date" class="text-xs text-green-600 font-medium">
                                            ✅ Delivered: {{ formatDate(po.delivery_date) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $currency(po.total_amount) }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Paid: {{ $currency(po.amount_paid) }}
                                            <span v-if="po.balance_due > 0" class="text-red-600">
                                                (Due: {{ $currency(po.balance_due) }})
                                            </span>
                                        </div>
                                        <div class="mt-2">
                                            <span :class="getStatusClasses(po.status)" class="px-2 py-1 inline-flex text-xs leading-4 font-semibold rounded-full">
                                                {{ formatStatus(po.status) }}
                                            </span>
                                            <!-- Status hint -->
                                            <div v-if="po.status === 'approved'" class="text-xs text-blue-600 mt-1">
                                                ⚡ Ready to order with supplier
                                            </div>
                                            <div v-else-if="po.status === 'ordered'" class="text-xs text-purple-600 mt-1">
                                                📦 Placed with supplier - awaiting delivery
                                            </div>
                                            <div v-else-if="po.status === 'partially_received'" class="text-xs text-indigo-600 mt-1">
                                                📥 {{ po.received_percentage || 0 }}% received
                                            </div>
                                            <!-- Progress bar -->
                                            <div v-if="po.received_percentage > 0" class="mt-1">
                                                <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                    <div class="bg-green-600 h-1.5 rounded-full" :style="{ width: (po.received_percentage || 0) + '%' }"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col space-y-2">
                                            <!-- Primary Action Button -->
                                            <div v-if="po.status === 'approved'">
                                                <button @click="markAsOrdered(po)" 
                                                        class="w-full inline-flex justify-center items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Mark as Ordered
                                                </button>
                                            </div>
                                            <div v-else-if="po.status === 'ordered' || po.status === 'partially_received'">
                                                <Link :href="route('purchase-orders.receive', po.id)" 
                                                      class="w-full inline-flex justify-center items-center px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Receive Goods
                                                </Link>
                                            </div>
                                            <div v-else-if="po.status === 'draft' || po.status === 'pending'">
                                                <button @click="approveAndOrder(po)" 
                                                        class="w-full inline-flex justify-center items-center px-3 py-1.5 bg-purple-600 text-white text-xs font-medium rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Approve & Order
                                                </button>
                                            </div>
                                            
                                            <!-- Action Links -->
                                            <div class="flex flex-wrap gap-2">
                                                <Link :href="route('purchase-orders.show', po.id)" 
                                                      class="text-blue-600 hover:text-blue-900 text-xs font-medium">
                                                    View
                                                </Link>
                                                
                                                <Link v-if="canEdit(po)" 
                                                      :href="route('purchase-orders.edit', po.id)" 
                                                      class="text-green-600 hover:text-green-900 text-xs font-medium">
                                                    Edit
                                                </Link>
                                                
                                                <button v-if="canCancel(po)" 
                                                        @click="confirmCancel(po)" 
                                                        class="text-red-600 hover:text-red-900 text-xs font-medium">
                                                    Cancel
                                                </button>
                                                
                                                <button v-if="canDelete(po)" 
                                                        @click="confirmDelete(po)" 
                                                        class="text-gray-600 hover:text-gray-900 text-xs font-medium">
                                                    Delete
                                                </button>
                                            </div>
                                            
                                            <!-- Quick Complete for partially received -->
                                            <div v-if="po.status === 'partially_received' && isFullyReceived(po)">
                                                <button @click="markAsFullyReceived(po)" 
                                                        class="w-full inline-flex justify-center items-center px-2 py-1 bg-indigo-600 text-white text-xs font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Mark Fully Received
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-gray-200" v-if="purchaseOrders.data && purchaseOrders.data.length > 0">
                        <Pagination :links="purchaseOrders.links" />
                    </div>

                    <!-- Empty State -->
                    <div v-if="purchaseOrders.data && purchaseOrders.data.length === 0" class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No purchase orders found</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ search || statusFilter || dateFilter ? 'Try changing your filters' : 'Get started by creating your first purchase order' }}
                        </p>
                        <div class="mt-6">
                            <Link :href="route('purchase-orders.create')" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                New Purchase Order
                            </Link>
                            <button v-if="search || statusFilter || dateFilter" @click="clearFilters" class="ml-3 inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Clear Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <ConfirmationModal :show="showDeleteModal" @close="showDeleteModal = false">
            <template #title>
                Delete Purchase Order
            </template>

            <template #content>
                Are you sure you want to delete Purchase Order <strong>{{ poToDelete?.po_number }}</strong>?
                <div v-if="poToDelete?.status === 'received'" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-md">
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-red-800">Warning!</p>
                            <p class="text-sm text-red-600">
                                This purchase order has been received. Deleting it will affect inventory records.
                            </p>
                        </div>
                    </div>
                </div>
                <div v-else-if="poToDelete?.status === 'ordered'" class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                    <div class="flex">
                        <svg class="h-5 w-5 text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-yellow-800">Note</p>
                            <p class="text-sm text-yellow-600">
                                This purchase order is marked as ordered. Make sure the supplier has been notified of cancellation.
                            </p>
                        </div>
                    </div>
                </div>
            </template>

            <template #footer>
                <SecondaryButton @click="showDeleteModal = false">
                    Cancel
                </SecondaryButton>

                <DangerButton
                    class="ml-3"
                    @click="deletePO"
                    :disabled="form.processing"
                >
                    Delete Purchase Order
                </DangerButton>
            </template>
        </ConfirmationModal>

        <!-- Cancel Confirmation Modal -->
        <ConfirmationModal :show="showCancelModal" @close="showCancelModal = false">
            <template #title>
                Cancel Purchase Order
            </template>

            <template #content>
                Are you sure you want to cancel Purchase Order <strong>{{ poToCancel?.po_number }}</strong>?
                <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                    <div class="flex">
                        <svg class="h-5 w-5 text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-yellow-800">Important!</p>
                            <p class="text-sm text-yellow-600">
                                <span v-if="poToCancel?.status === 'ordered'">
                                    This order has been placed with the supplier. Make sure to notify them before cancelling.
                                </span>
                                <span v-else-if="poToCancel?.status === 'partially_received'">
                                    Some items have been received. Only cancel if the supplier confirms they will not deliver remaining items.
                                </span>
                                <span v-else>
                                    This will prevent this purchase order from being processed further.
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </template>

            <template #footer>
                <SecondaryButton @click="showCancelModal = false">
                    Keep Order
                </SecondaryButton>

                <DangerButton
                    class="ml-3"
                    @click="cancelPO"
                    :disabled="form.processing"
                >
                    Cancel Purchase Order
                </DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    purchaseOrders: Object,
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            to_order: 0,
            awaiting_delivery: 0,
            received: 0
        })
    }
});

const search = ref('');
const statusFilter = ref('');
const dateFilter = ref('');
const showDeleteModal = ref(false);
const showCancelModal = ref(false);
const poToDelete = ref(null);
const poToCancel = ref(null);

const form = useForm({});

const filteredPurchaseOrders = computed(() => {
    let filtered = props.purchaseOrders.data || [];

    // Apply search filter
    if (search.value) {
        const searchTerm = search.value.toLowerCase();
        filtered = filtered.filter(po =>
            po.po_number.toLowerCase().includes(searchTerm) ||
            po.supplier?.name.toLowerCase().includes(searchTerm) ||
            po.supplier?.contact_person?.toLowerCase().includes(searchTerm) ||
            po.supplier?.phone?.includes(searchTerm)
        );
    }

    // Apply status filter
    if (statusFilter.value) {
        filtered = filtered.filter(po => po.status === statusFilter.value);
    }

    // Apply date filter
    if (dateFilter.value) {
        filtered = filtered.filter(po => po.order_date === dateFilter.value);
    }

    return filtered;
});

const clearFilters = () => {
    search.value = '';
    statusFilter.value = '';
    dateFilter.value = '';
};

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
    return expectedDate < today && po.status !== 'received';
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
        'pending': 'Pending Review',
        'approved': 'Approved (Ready to Order)',
        'ordered': 'Ordered (Placed with Supplier)',
        'partially_received': 'Partially Received',
        'received': 'Fully Received',
        'cancelled': 'Cancelled',
    };
    return statusMap[status] || status;
};

const canEdit = (po) => {
    return ['draft', 'pending'].includes(po.status);
};

const canReceive = (po) => {
    return ['ordered', 'partially_received'].includes(po.status);
};

const canCancel = (po) => {
    return !['received', 'cancelled'].includes(po.status);
};

const canDelete = (po) => {
    return ['draft', 'pending', 'cancelled'].includes(po.status);
};

const isFullyReceived = (po) => {
    // Check if all items are fully received
    if (!po.items || po.items.length === 0) return false;
    
    return po.items.every(item => {
        const receivedQty = parseFloat(item.received_quantity) || 0;
        const orderedQty = parseFloat(item.quantity) || 0;
        return receivedQty >= orderedQty;
    });
};

const markAsOrdered = (po) => {
    const message = po.status === 'approved' 
        ? `Mark Purchase Order ${po.po_number} as "Ordered"? This confirms you have placed this order with the supplier.`
        : `Approve and mark Purchase Order ${po.po_number} as "Ordered"? This will place this order with the supplier.`;
    
    if (confirm(message)) {
        const routeName = po.status === 'approved' 
            ? 'purchase-orders.order'
            : 'purchase-orders.approve-and-order';
        
        router.post(route(routeName, po.id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                // Show success message if needed
            }
        });
    }
};

const approveAndOrder = (po) => {
    if (confirm(`Approve and mark Purchase Order ${po.po_number} as "Ordered"? This will place this order with the supplier.`)) {
        router.post(route('purchase-orders.approve-and-order', po.id), {}, {
            preserveScroll: true,
        });
    }
};

const markAsFullyReceived = (po) => {
    if (confirm(`Mark Purchase Order ${po.po_number} as fully received? This will update inventory and complete the order.`)) {
        router.post(route('purchase-orders.receive-full', po.id), {}, {
            preserveScroll: true,
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

const deletePO = () => {
    if (!poToDelete.value) return;
    
    router.delete(route('purchase-orders.destroy', poToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            poToDelete.value = null;
        },
    });
};

const cancelPO = () => {
    if (!poToCancel.value) return;
    
    router.post(route('purchase-orders.cancel', poToCancel.value.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            showCancelModal.value = false;
            poToCancel.value = null;
        },
    });
};
</script>