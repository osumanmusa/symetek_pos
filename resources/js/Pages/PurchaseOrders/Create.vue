<template>
    <Head title="Create Purchase Order" />
    
    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900">Create Purchase Order</h2>
                    <p class="mt-1 text-sm text-gray-600">Order products from your suppliers</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route('purchase-orders.index')" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Back to List
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="p-6">
                        <form @submit.prevent="submit">
                            <!-- Supplier and Dates -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                                <div>
                                    <InputLabel for="supplier_id" value="Supplier *" />
                                    <select
                                        id="supplier_id"
                                        v-model="form.supplier_id"
                                        class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                        required
                                        @change="onSupplierChange"
                                    >
                                        <option value="">Select Supplier</option>
                                        <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                                            {{ supplier.name }} - {{ supplier.contact_person || supplier.phone }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.supplier_id" />
                                </div>

                                <div>
                                    <InputLabel for="order_date" value="Order Date *" />
                                    <input
                                        id="order_date"
                                        type="date"
                                        v-model="form.order_date"
                                        class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.order_date" />
                                </div>

                                <div>
                                    <InputLabel for="expected_delivery_date" value="Expected Delivery Date" />
                                    <input
                                        id="expected_delivery_date"
                                        type="date"
                                        v-model="form.expected_delivery_date"
                                        :min="form.order_date"
                                        class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                    />
                                    <InputError class="mt-2" :message="form.errors.expected_delivery_date" />
                                </div>
                            </div>

                            <!-- Order Items -->
                            <div class="mb-8">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">Order Items</h3>
                                    <button
                                        type="button"
                                        @click="addItem"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    >
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        Add Item
                                    </button>
                                </div>

                                <div v-if="form.items.length === 0" class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No items added</h3>
                                    <p class="mt-1 text-sm text-gray-500">Get started by adding products to this order.</p>
                                </div>

                                <div v-else class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Cost</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Cost</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="(item, index) in form.items" :key="index">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <select
                                                        v-model="item.product_id"
                                                        @change="onProductChange(index)"
                                                        class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                                        required
                                                    >
                                                        <option value="">Select Product</option>
                                                        <option v-for="product in products" :key="product.id" :value="product.id">
                                                            {{ product.name }} - {{ $currency(product.cost_price) }} - Stock: {{ product.stock_quantity }}
                                                        </option>
                                                    </select>
                                                    <div v-if="item.product" class="mt-1 text-xs text-gray-500">
                                                        SKU: {{ item.product.sku }} | Category: {{ item.product.category?.name || 'Uncategorized' }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <input
                                                        type="number"
                                                        v-model="item.quantity"
                                                        @input="calculateItemTotal(index)"
                                                        min="0.01"
                                                        step="0.01"
                                                        class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                                        required
                                                    />
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <span class="mr-2">₵</span>
                                                        <input
                                                            type="number"
                                                            v-model="item.unit_cost"
                                                            @input="calculateItemTotal(index)"
                                                            min="0"
                                                            step="0.01"
                                                            class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                                            required
                                                        />
                                                    </div>
                                                    <div v-if="item.product" class="text-xs text-gray-500 mt-1">
                                                        Current: ₵{{ $formatPrice(item.product.cost_price) }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        ₵{{ $formatPrice(item.total_cost || 0) }}
                                                    </div>
                                                    <div v-if="item.product" class="text-xs text-gray-500">
                                                        Selling: ₵{{ $formatPrice(item.product.selling_price) }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <button
                                                        type="button"
                                                        @click="removeItem(index)"
                                                        class="text-red-600 hover:text-red-900"
                                                    >
                                                        Remove
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="bg-gray-50">
                                            <tr>
                                                <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-900">Subtotal:</td>
                                                <td class="px-6 py-3 text-sm font-medium text-gray-900">₵{{ $formatPrice(subtotal) }}</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                <div class="space-y-4">
                                    <div>
                                        <InputLabel for="shipping_address" value="Shipping Address" />
                                        <textarea
                                            id="shipping_address"
                                            v-model="form.shipping_address"
                                            rows="3"
                                            class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                            placeholder="Leave blank to use supplier's default address"
                                        ></textarea>
                                        <InputError class="mt-2" :message="form.errors.shipping_address" />
                                    </div>

                                    <div>
                                        <InputLabel for="notes" value="Notes" />
                                        <textarea
                                            id="notes"
                                            v-model="form.notes"
                                            rows="3"
                                            class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                            placeholder="Any special instructions or notes for this order..."
                                        ></textarea>
                                        <InputError class="mt-2" :message="form.errors.notes" />
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <InputLabel for="shipping_cost" value="Shipping Cost (₵)" />
                                        <div class="flex items-center mt-1">
                                            <span class="mr-2">₵</span>
                                            <input
                                                id="shipping_cost"
                                                type="number"
                                                v-model="form.shipping_cost"
                                                min="0"
                                                step="0.01"
                                                class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                            />
                                        </div>
                                        <InputError class="mt-2" :message="form.errors.shipping_cost" />
                                    </div>

                                    <div>
                                        <InputLabel for="tax_amount" value="Tax Amount (₵)" />
                                        <div class="flex items-center mt-1">
                                            <span class="mr-2">₵</span>
                                            <input
                                                id="tax_amount"
                                                type="number"
                                                v-model="form.tax_amount"
                                                min="0"
                                                step="0.01"
                                                class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                            />
                                        </div>
                                        <InputError class="mt-2" :message="form.errors.tax_amount" />
                                    </div>

                                    <div>
                                        <InputLabel for="discount_amount" value="Discount Amount (₵)" />
                                        <div class="flex items-center mt-1">
                                            <span class="mr-2">₵</span>
                                            <input
                                                id="discount_amount"
                                                type="number"
                                                v-model="form.discount_amount"
                                                min="0"
                                                step="0.01"
                                                class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                            />
                                        </div>
                                        <InputError class="mt-2" :message="form.errors.discount_amount" />
                                    </div>

                                    <!-- Order Summary -->
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <h4 class="text-sm font-medium text-gray-700 mb-2">Order Summary</h4>
                                        <div class="space-y-1 text-sm">
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Subtotal:</span>
                                                <span class="font-medium">₵{{ $formatPrice(subtotal) }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Shipping:</span>
                                                <span class="font-medium">₵{{ $formatPrice(form.shipping_cost || 0) }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Tax:</span>
                                                <span class="font-medium">₵{{ $formatPrice(form.tax_amount || 0) }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Discount:</span>
                                                <span class="font-medium text-red-600">-₵{{ $formatPrice(form.discount_amount || 0) }}</span>
                                            </div>
                                            <div class="flex justify-between border-t border-gray-200 pt-1 mt-1">
                                                <span class="text-gray-900 font-medium">Total:</span>
                                                <span class="text-gray-900 font-medium">₵{{ $formatPrice(total) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="flex justify-end space-x-3">
                                <Link
                                    :href="route('purchase-orders.index')"
                                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                >
                                    Cancel
                                </Link>
                                <PrimaryButton type="submit" :disabled="form.processing">
                                    Create Purchase Order
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    suppliers: Array,
    products: Array,
});

const form = useForm({
    supplier_id: '',
    order_date: new Date().toISOString().split('T')[0],
    expected_delivery_date: '',
    shipping_address: '',
    notes: '',
    shipping_cost: 0,
    tax_amount: 0,
    discount_amount: 0,
    items: [],
});

const addItem = () => {
    form.items.push({
        product_id: '',
        product: null,
        quantity: 1,
        unit_cost: 0,
        total_cost: 0,
    });
};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

const onSupplierChange = () => {
    const supplier = props.suppliers.find(s => s.id == form.supplier_id);
    if (supplier && supplier.address) {
        form.shipping_address = supplier.address;
    }
};

const onProductChange = (index) => {
    const product = props.products.find(p => p.id == form.items[index].product_id);
    if (product) {
        form.items[index].product = product;
        form.items[index].unit_cost = parseFloat(product.cost_price) || 0;
        calculateItemTotal(index);
    }
};

const calculateItemTotal = (index) => {
    const item = form.items[index];
    item.total_cost = (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_cost) || 0);
};

const subtotal = computed(() => {
    return form.items.reduce((sum, item) => sum + (parseFloat(item.total_cost) || 0), 0);
});

const total = computed(() => {
    return subtotal.value + 
           (parseFloat(form.shipping_cost) || 0) + 
           (parseFloat(form.tax_amount) || 0) - 
           (parseFloat(form.discount_amount) || 0);
});

const submit = () => {
    form.post(route('purchase-orders.store'));
};
</script>