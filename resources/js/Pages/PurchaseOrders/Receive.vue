<!-- resources/js/Pages/PurchaseOrders/Receive.vue -->
<template>
    <Head :title="`Receive Goods - ${purchaseOrder.po_number}`" />
    
    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900">Receive Goods for Purchase Order: {{ purchaseOrder.po_number }}</h2>
                    <div class="flex items-center space-x-4 mt-1">
                        <span :class="getStatusClasses(purchaseOrder.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                            {{ formatStatus(purchaseOrder.status) }}
                        </span>
                        <span class="text-sm text-gray-600">Supplier: {{ purchaseOrder.supplier?.name }}</span>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route('purchase-orders.show', purchaseOrder.id)" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Back to Order
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

                <!-- Quick Action Bar -->
                <div class="mb-6 bg-white shadow-sm rounded-lg p-4">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-3 sm:space-y-0">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Receive Goods from Supplier</h3>
                            <p class="text-sm text-gray-600">Enter the quantities you have received for each item</p>
                        </div>
                        <div class="flex space-x-3">
                            <!-- Quick Action: Mark All as Fully Received -->
                            <button
                                v-if="canMarkAllAsReceived"
                                @click="markAllAsFullyReceived"
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Mark All as Received
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Receiving Form -->
                <form @submit.prevent="submit">
                    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Receiving Details</h3>
                        </div>
                        <div class="p-6">
                            <!-- Delivery Date -->
                            <div class="mb-6">
                                <label for="delivery_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Delivery Date *
                                </label>
                                <input
                                    type="date"
                                    id="delivery_date"
                                    v-model="form.delivery_date"
                                    :max="new Date().toISOString().split('T')[0]"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    required
                                />
                                <p class="mt-1 text-sm text-gray-500">The date when goods were actually delivered</p>
                                <div v-if="form.errors.delivery_date" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.delivery_date }}
                                </div>
                            </div>

                            <!-- Receiving Notes -->
                            <div class="mb-6">
                                <label for="receiving_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                    Receiving Notes (Optional)
                                </label>
                                <textarea
                                    id="receiving_notes"
                                    v-model="form.receiving_notes"
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    placeholder="Any notes about this delivery (condition, packaging, etc.)"
                                ></textarea>
                            </div>

                            <!-- Items Table -->
                            <div class="mb-6">
                                <h4 class="text-lg font-medium text-gray-900 mb-4">Items to Receive</h4>
                                <p class="text-sm text-gray-600 mb-4">Enter quantities received, damaged, or returned for each item.</p>
                                
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ordered</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Already Received</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remaining</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Good Received</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Damaged</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Returned</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="(item, index) in purchaseOrder.items" :key="item.id">
                                                <td class="px-4 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ item.product?.name }}
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        SKU: {{ item.product?.sku || 'N/A' }}
                                                    </div>
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        {{ item.quantity }} {{ item.product?.unit || 'pcs' }}
                                                    </div>
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        {{ item.received_quantity || 0 }}
                                                    </div>
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium" :class="getRemainingClass(item)">
                                                        {{ remainingQuantity(item) }}
                                                    </div>
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap">
                                                    <input
                                                        type="number"
                                                        v-model="form.items[index].received"
                                                        :max="remainingQuantity(item)"
                                                        min="0"
                                                        step="0.01"
                                                        class="w-24 px-2 py-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                        @input="validateItemQuantity(index)"
                                                    />
                                                    <div v-if="form.errors[`items.${index}.received`]" class="mt-1 text-xs text-red-600">
                                                        {{ form.errors[`items.${index}.received`] }}
                                                    </div>
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap">
                                                    <input
                                                        type="number"
                                                        v-model="form.items[index].damaged"
                                                        :max="remainingQuantity(item) - (form.items[index].received || 0)"
                                                        min="0"
                                                        step="0.01"
                                                        class="w-24 px-2 py-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                    />
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap">
                                                    <input
                                                        type="number"
                                                        v-model="form.items[index].returned"
                                                        :max="remainingQuantity(item) - (form.items[index].received || 0) - (form.items[index].damaged || 0)"
                                                        min="0"
                                                        step="0.01"
                                                        class="w-24 px-2 py-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                    />
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap">
                                                    <input
                                                        type="text"
                                                        v-model="form.items[index].notes"
                                                        placeholder="Item notes"
                                                        class="w-32 px-2 py-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                    />
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Summary Stats -->
                                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <div class="text-sm font-medium text-gray-700">Total Ordered</div>
                                        <div class="text-2xl font-bold text-gray-900">{{ totalOrdered }}</div>
                                    </div>
                                    <div class="bg-blue-50 p-4 rounded-lg">
                                        <div class="text-sm font-medium text-blue-700">To Be Received</div>
                                        <div class="text-2xl font-bold text-blue-900">{{ totalToReceive }}</div>
                                    </div>
                                    <div class="bg-green-50 p-4 rounded-lg">
                                        <div class="text-sm font-medium text-green-700">Already Received</div>
                                        <div class="text-2xl font-bold text-green-900">{{ totalAlreadyReceived }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="flex justify-end space-x-3">
                                <Link
                                    :href="route('purchase-orders.show', purchaseOrder.id)"
                                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing || !hasReceivingData"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span v-else class="flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Submit Receiving
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Instructions -->
                <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">How to Receive Goods</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    <li><strong>Good Received:</strong> Enter quantity that arrived in good condition (will be added to inventory)</li>
                                    <li><strong>Damaged:</strong> Enter quantity that arrived damaged (will not be added to inventory)</li>
                                    <li><strong>Returned:</strong> Enter quantity being returned to supplier (will not be added to inventory)</li>
                                    <li>Total of Good + Damaged + Returned cannot exceed remaining quantity</li>
                                    <li>Once all items are fully received, the order status will automatically update to "Fully Received"</li>
                                </ul>
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
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    purchaseOrder: Object,
});

// Initialize form with current items
const form = useForm({
    delivery_date: new Date().toISOString().split('T')[0],
    receiving_notes: '',
    items: props.purchaseOrder.items.map(item => ({
        id: item.id,
        received: 0,
        damaged: 0,
        returned: 0,
        notes: ''
    }))
});

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

const formatStatus = (status) => {
    const statusMap = {
        'draft': 'Draft',
        'pending': 'Pending Review',
        'approved': 'Approved',
        'ordered': 'Ordered',
        'partially_received': 'Partially Received',
        'received': 'Fully Received',
        'cancelled': 'Cancelled',
    };
    return statusMap[status] || status;
};

const remainingQuantity = (item) => {
    const received = parseFloat(item.received_quantity) || 0;
    const damaged = parseFloat(item.damaged_quantity) || 0;
    const returned = parseFloat(item.returned_quantity) || 0;
    const processed = received + damaged + returned;
    return Math.max(0, parseFloat(item.quantity) - processed);
};

const getRemainingClass = (item) => {
    const remaining = remainingQuantity(item);
    if (remaining === 0) return 'text-green-600';
    if (remaining === parseFloat(item.quantity)) return 'text-red-600';
    return 'text-yellow-600';
};

const validateItemQuantity = (index) => {
    const item = props.purchaseOrder.items[index];
    const remaining = remainingQuantity(item);
    const received = parseFloat(form.items[index].received) || 0;
    
    if (received > remaining) {
        form.items[index].received = remaining;
    }
};

const totalOrdered = computed(() => {
    return props.purchaseOrder.items.reduce((sum, item) => sum + parseFloat(item.quantity), 0);
});

const totalAlreadyReceived = computed(() => {
    return props.purchaseOrder.items.reduce((sum, item) => sum + (parseFloat(item.received_quantity) || 0), 0);
});

const totalToReceive = computed(() => {
    return totalOrdered.value - totalAlreadyReceived.value;
});

const canMarkAllAsReceived = computed(() => {
    // Check if there are items with remaining quantity
    return props.purchaseOrder.items.some(item => remainingQuantity(item) > 0);
});

const hasReceivingData = computed(() => {
    // Check if any receiving data has been entered
    return form.items.some(item => 
        (parseFloat(item.received) || 0) > 0 ||
        (parseFloat(item.damaged) || 0) > 0 ||
        (parseFloat(item.returned) || 0) > 0
    );
});

const markAllAsFullyReceived = () => {
    if (confirm('Mark all remaining quantities as fully received? This will fill in the remaining quantity for all items.')) {
        props.purchaseOrder.items.forEach((item, index) => {
            const remaining = remainingQuantity(item);
            if (remaining > 0) {
                form.items[index].received = remaining;
            }
        });
    }
};

const submit = () => {
    if (!hasReceivingData.value) {
        alert('Please enter receiving quantities for at least one item.');
        return;
    }

    if (confirm('Submit this receiving? This will update inventory and cannot be undone.')) {
        form.post(route('purchase-orders.process-receive', props.purchaseOrder.id));
    }
};
</script>