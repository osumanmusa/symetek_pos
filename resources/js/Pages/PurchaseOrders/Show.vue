<template>
    <Head :title="`Purchase Order - ${purchaseOrder.po_number}`" />
    
    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900">Purchase Order: {{ purchaseOrder.po_number }}</h2>
                    <div class="flex items-center space-x-4 mt-1">
                        <span :class="getStatusClasses(purchaseOrder.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                            {{ formatStatus(purchaseOrder.status) }}
                        </span>
                        <span class="text-sm text-gray-600">Created: {{ formatDate(purchaseOrder.created_at) }}</span>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route('purchase-orders.index')" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Back to List
                    </Link>
                    <Link v-if="canEdit(purchaseOrder)" :href="route('purchase-orders.edit', purchaseOrder.id)" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Edit
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Success/Error Messages -->
                <div v-if="$page.props.flash.success" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ $page.props.flash.success }}
                </div>
                
                <div v-if="$page.props.flash.error" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ $page.props.flash.error }}
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Column: Order Details -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Order Summary Card -->
                        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">Order Summary</h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-700 mb-2">Supplier Information</h4>
                                        <div class="space-y-1">
                                            <p class="text-sm">
                                                <span class="font-medium">Name:</span> {{ purchaseOrder.supplier?.name }}
                                            </p>
                                            <p class="text-sm">
                                                <span class="font-medium">Contact:</span> {{ purchaseOrder.supplier?.contact_person || 'N/A' }}
                                            </p>
                                            <p class="text-sm">
                                                <span class="font-medium">Phone:</span> {{ purchaseOrder.supplier?.phone || 'N/A' }}
                                            </p>
                                            <p class="text-sm">
                                                <span class="font-medium">Email:</span> {{ purchaseOrder.supplier?.email || 'N/A' }}
                                            </p>
                                            <p class="text-sm">
                                                <span class="font-medium">Address:</span> {{ purchaseOrder.supplier?.address || 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-700 mb-2">Order Information</h4>
                                        <div class="space-y-1">
                                            <p class="text-sm">
                                                <span class="font-medium">Order Date:</span> {{ formatDate(purchaseOrder.order_date) }}
                                            </p>
                                            <p class="text-sm">
                                                <span class="font-medium">Expected Delivery:</span> {{ purchaseOrder.expected_delivery_date ? formatDate(purchaseOrder.expected_delivery_date) : 'Not set' }}
                                            </p>
                                            <p class="text-sm" v-if="purchaseOrder.delivery_date">
                                                <span class="font-medium">Actual Delivery:</span> {{ formatDate(purchaseOrder.delivery_date) }}
                                            </p>
                                            <p class="text-sm">
                                                <span class="font-medium">Created By:</span> {{ purchaseOrder.user?.name }}
                                            </p>
                                            <p class="text-sm">
                                                <span class="font-medium">Payment Status:</span> 
                                                <span :class="getPaymentStatusClasses(purchaseOrder.payment_status)" class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                    {{ formatPaymentStatus(purchaseOrder.payment_status) }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div v-if="purchaseOrder.notes" class="mt-6">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Notes</h4>
                                    <p class="text-sm text-gray-600 bg-gray-50 p-3 rounded">{{ purchaseOrder.notes }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items Card -->
                        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-medium text-gray-900">Order Items</h3>
                                    <span class="text-sm text-gray-500">{{ purchaseOrder.items?.length || 0 }} items</span>
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
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="item in purchaseOrder.items" :key="item.id">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ item.product?.name }}
                                                        </div>
                                                        <div class="text-xs text-gray-500">
                                                            SKU: {{ item.product?.sku }} | Category: {{ item.product?.category?.name || 'Uncategorized' }}
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
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm" :class="item.received_quantity >= item.quantity ? 'text-green-600' : 'text-yellow-600'">
                                                    {{ item.received_quantity || 0 }} / {{ item.quantity }}
                                                </div>
                                                <div v-if="item.received_quantity < item.quantity" class="text-xs text-gray-500">
                                                    {{ item.quantity - (item.received_quantity || 0) }} remaining
                                                </div>
                                                <div v-if="item.damaged_quantity > 0" class="text-xs text-red-600">
                                                    {{ item.damaged_quantity }} damaged
                                                </div>
                                                <div v-if="item.returned_quantity > 0" class="text-xs text-yellow-600">
                                                    {{ item.returned_quantity }} returned
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="bg-gray-50">
                                        <tr>
                                            <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-900">Subtotal:</td>
                                            <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ $currency(purchaseOrder.subtotal) }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-900">Shipping:</td>
                                            <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ $currency(purchaseOrder.shipping_cost) }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-900">Tax:</td>
                                            <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ $currency(purchaseOrder.tax_amount) }}</td>
                                            <td></td>
                                        </tr>
                                        <tr v-if="purchaseOrder.discount_amount > 0">
                                            <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-900">Discount:</td>
                                            <td class="px-6 py-3 text-sm font-medium text-red-600">-{{ $currency(purchaseOrder.discount_amount) }}</td>
                                            <td></td>
                                        </tr>
                                        <tr class="border-t border-gray-200">
                                            <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-900">Total:</td>
                                            <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ $currency(purchaseOrder.total_amount) }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-900">Amount Paid:</td>
                                            <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ $currency(purchaseOrder.amount_paid) }}</td>
                                            <td></td>
                                        </tr>
                                        <tr class="border-t border-gray-200">
                                            <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-900">Balance Due:</td>
                                            <td class="px-6 py-3 text-sm font-medium" :class="purchaseOrder.balance_due > 0 ? 'text-red-600' : 'text-green-600'">
                                                {{ $currency(purchaseOrder.balance_due) }}
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Activity Log Card -->
                        <div v-if="purchaseOrder.activities && purchaseOrder.activities.length > 0" class="bg-white shadow-sm rounded-lg overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">Activity Log</h3>
                            </div>
                            <div class="p-6">
                                <div class="flow-root">
                                    <ul role="list" class="-mb-8">
                                        <li v-for="(activity, activityIdx) in purchaseOrder.activities" :key="activity.id">
                                            <div class="relative pb-8">
                                                <span v-if="activityIdx !== purchaseOrder.activities.length - 1" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                                <div class="relative flex space-x-3">
                                                    <div>
                                                        <span :class="getActivityIconClasses(activity.description)" class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white">
                                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                <path v-if="activity.description.includes('created')" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                                                                <path v-else-if="activity.description.includes('updated')" fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd" />
                                                                <path v-else-if="activity.description.includes('deleted')" fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                                <path v-else-if="activity.description.includes('ordered')" fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                                <path v-else-if="activity.description.includes('received')" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                                <path v-else-if="activity.description.includes('cancelled')" fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
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
                                                            <p v-if="activity.properties" class="mt-1 text-xs text-gray-500">
                                                                <span v-if="activity.properties.old_status && activity.properties.new_status">
                                                                    Status changed from <span class="font-medium">{{ formatStatus(activity.properties.old_status) }}</span> to <span class="font-medium">{{ formatStatus(activity.properties.new_status) }}</span>
                                                                </span>
                                                                <span v-else-if="activity.properties.items_count">
                                                                    {{ activity.properties.items_count }} items
                                                                </span>
                                                            </p>
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
                        <!-- Status Actions Card -->
                        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">Order Actions</h3>
                            </div>
                            <div class="p-6 space-y-3">
                                <!-- Status Info -->
                                <div class="mb-4">
                                    <div v-if="purchaseOrder.status === 'draft'" class="text-sm text-gray-600 bg-gray-50 p-3 rounded">
                                        <strong>Draft Status:</strong> This order is still in draft. Edit it and send for approval.
                                    </div>
                                    <div v-if="purchaseOrder.status === 'pending'" class="text-sm text-gray-600 bg-yellow-50 p-3 rounded">
                                        <strong>Pending Status:</strong> This order is pending approval. Once approved, you can mark it as ordered.
                                    </div>
                                    <div v-if="purchaseOrder.status === 'approved'" class="text-sm text-gray-600 bg-blue-50 p-3 rounded">
                                        <strong>Approved Status:</strong> This order is approved. Mark it as ordered to send to supplier.
                                    </div>
                                    <div v-if="purchaseOrder.status === 'ordered'" class="text-sm text-gray-600 bg-indigo-50 p-3 rounded">
                                        <strong>Ordered Status:</strong> This order has been sent to the supplier. Click "Receive Items" when goods arrive.
                                    </div>
                                    <div v-if="purchaseOrder.status === 'partially_received'" class="text-sm text-gray-600 bg-purple-50 p-3 rounded">
                                        <strong>Partially Received:</strong> Some items have been received. Continue receiving the remaining items.
                                    </div>
                                    <div v-if="purchaseOrder.status === 'received'" class="text-sm text-gray-600 bg-green-50 p-3 rounded">
                                        <strong>Fully Received:</strong> All items have been received from the supplier.
                                    </div>
                                    <div v-if="purchaseOrder.status === 'cancelled'" class="text-sm text-gray-600 bg-red-50 p-3 rounded">
                                        <strong>Cancelled:</strong> This order has been cancelled.
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <!-- Approve Button -->
                                <button
                                    v-if="purchaseOrder.status === 'pending'"
                                    @click="approveOrder"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Approve Order
                                </button>

                                <!-- Mark as Ordered Button (for approved status) -->
                                <button
                                    v-if="purchaseOrder.status === 'approved'"
                                    @click="markAsOrdered"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Mark as Ordered (Send to Supplier)
                                </button>

                                <!-- Approve & Order Button (for draft/pending) -->
                                <button
                                    v-if="['draft', 'pending'].includes(purchaseOrder.status)"
                                    @click="approveAndOrder"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Approve & Order (One Click)
                                </button>

                                <!-- Receive Items Button -->
                          <!-- In the Right Column Actions section of Show.vue -->
<!-- Add this as the first button in the Actions section -->
<Link
    v-if="['ordered', 'partially_received'].includes(purchaseOrder.status)"
    :href="route('purchase-orders.receive', purchaseOrder.id)"
    class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 shadow-md"
>
    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
    </svg>
    Receive Goods
</Link>

                                <!-- Mark as Fully Received Button -->
                                <button
                                    v-if="purchaseOrder.status === 'partially_received' && isFullyReceived"
                                    @click="markAsFullyReceived"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Mark as Fully Received
                                </button>

                                <!-- Cancel Button -->
                                <button
                                    v-if="!['received', 'cancelled'].includes(purchaseOrder.status)"
                                    @click="cancelOrder"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Cancel Order
                                </button>

                                <!-- Edit Button -->
                                <Link
                                    :href="route('purchase-orders.edit', purchaseOrder.id)"
                                    v-if="canEdit(purchaseOrder)"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit Order
                                </Link>
                            </div>
                        </div>

                        <!-- Order Statistics Card -->
                        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">Order Statistics</h3>
                            </div>
                            <div class="p-6 space-y-4">
                                <!-- Progress Bar -->
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm text-gray-600">Receiving Progress</span>
                                        <span class="text-sm font-medium">{{ receivedPercentage }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-green-600 h-2 rounded-full" :style="{ width: receivedPercentage + '%' }"></div>
                                    </div>
                                </div>

                                <!-- Stats Grid -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="text-center p-3 bg-blue-50 rounded-lg">
                                        <div class="text-2xl font-bold text-blue-600">{{ purchaseOrder.items?.length || 0 }}</div>
                                        <div class="text-xs text-blue-800">Total Items</div>
                                    </div>
                                    <div class="text-center p-3 bg-green-50 rounded-lg">
                                        <div class="text-2xl font-bold text-green-600">{{ receivedItemsCount }}</div>
                                        <div class="text-xs text-green-800">Fully Received</div>
                                    </div>
                                    <div class="text-center p-3 bg-yellow-50 rounded-lg">
                                        <div class="text-2xl font-bold text-yellow-600">{{ partiallyReceivedCount }}</div>
                                        <div class="text-xs text-yellow-800">Partially Received</div>
                                    </div>
                                    <div class="text-center p-3 bg-red-50 rounded-lg">
                                        <div class="text-2xl font-bold text-red-600">{{ notReceivedCount }}</div>
                                        <div class="text-xs text-red-800">Not Received</div>
                                    </div>
                                </div>

                                <!-- Timeline -->
                                <div class="pt-4 border-t border-gray-200">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Timeline</h4>
                                    <div class="space-y-2">
                                        <div class="flex items-center">
                                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                            <span class="text-xs text-gray-600">Created: {{ formatDateTime(purchaseOrder.created_at) }}</span>
                                        </div>
                                        <div v-if="purchaseOrder.order_date && purchaseOrder.order_date !== purchaseOrder.created_at" class="flex items-center">
                                            <div class="w-2 h-2 bg-indigo-500 rounded-full mr-2"></div>
                                            <span class="text-xs text-gray-600">Ordered: {{ formatDateTime(purchaseOrder.order_date) }}</span>
                                        </div>
                                        <div v-if="purchaseOrder.delivery_date" class="flex items-center">
                                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                            <span class="text-xs text-gray-600">Delivered: {{ formatDateTime(purchaseOrder.delivery_date) }}</span>
                                        </div>
                                        <div v-if="purchaseOrder.updated_at !== purchaseOrder.created_at" class="flex items-center">
                                            <div class="w-2 h-2 bg-blue-500 rounded-full mr-2"></div>
                                            <span class="text-xs text-gray-600">Last Updated: {{ formatDateTime(purchaseOrder.updated_at) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    purchaseOrder: Object,
});

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
        'ordered': 'bg-indigo-100 text-indigo-800',
        'partially_received': 'bg-purple-100 text-purple-800',
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

const getActivityIconClasses = (description) => {
    if (description.includes('created')) return 'bg-green-500';
    if (description.includes('updated')) return 'bg-blue-500';
    if (description.includes('deleted')) return 'bg-red-500';
    if (description.includes('ordered')) return 'bg-indigo-500';
    if (description.includes('received')) return 'bg-green-500';
    if (description.includes('cancelled')) return 'bg-red-500';
    if (description.includes('approved')) return 'bg-green-500';
    return 'bg-gray-500';
};

const formatStatus = (status) => {
    const statusMap = {
        'draft': 'Draft',
        'pending': 'Pending Review',
        'approved': 'Approved',
        'ordered': 'Ordered (Placed with Supplier)',
        'partially_received': 'Partially Received',
        'received': 'Fully Received',
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

const canEdit = (po) => {
    return ['draft', 'pending'].includes(po.status);
};

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

const isFullyReceived = computed(() => {
    if (!props.purchaseOrder.items) return false;
    return props.purchaseOrder.items.every(item => 
        (item.received_quantity || 0) >= item.quantity
    );
});

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
</script>