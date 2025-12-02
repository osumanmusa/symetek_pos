<template>
    <Head title="Suppliers" />
    
    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900">Suppliers Management</h2>
                    <p class="mt-1 text-sm text-gray-600">Manage your product suppliers and vendors</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route('products.index')" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Products
                    </Link>
                    <PrimaryButton @click="showCreateModal = true">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Supplier
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters and Search -->
                <div class="mb-6 bg-white shadow-sm rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                type="text"
                                v-model="search"
                                placeholder="Search suppliers..."
                                class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            />
                        </div>
                        
                        <select v-model="statusFilter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        
                        <button @click="clearFilters" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200">
                            Clear Filters
                        </button>
                    </div>
                </div>

                <!-- Suppliers Table -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Supplier
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Contact Info
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Financials
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="supplier in filteredSuppliers" :key="supplier.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 flex-shrink-0 bg-green-100 rounded-lg flex items-center justify-center">
                                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                </svg>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ supplier.name }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    Code: {{ supplier.supplier_code }}
                                                </div>
                                                <div v-if="supplier.contact_person" class="text-xs text-gray-500">
                                                    Contact: {{ supplier.contact_person }}
                                                </div>
                                                <div v-if="supplier.address" class="text-xs text-gray-500 truncate max-w-xs">
                                                    {{ supplier.address }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <div v-if="supplier.email" class="flex items-center">
                                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                </svg>
                                                {{ supplier.email }}
                                            </div>
                                            <div v-if="supplier.phone" class="flex items-center mt-1">
                                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                                </svg>
                                                {{ supplier.phone }}
                                            </div>
                                            <div v-if="supplier.city" class="text-xs text-gray-500 mt-1">
                                                {{ supplier.city }}, {{ supplier.state }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm">
                                            <div class="text-gray-900">
                                                Limit: {{ $currency(supplier.credit_limit) }}
                                            </div>
                                            <div class="text-gray-500">
                                                Balance: {{ $currency(supplier.current_balance) }}
                                            </div>
                                            <div class="text-xs mt-1">
                                                <span class="px-2 py-1 rounded-full" 
                                                      :class="supplier.payment_terms === 'cash' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'">
                                                    {{ formatPaymentTerms(supplier.payment_terms) }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span v-if="supplier.is_active" 
                                              class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Active
                                        </span>
                                        <span v-else 
                                              class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Inactive
                                        </span>
                                        <div v-if="supplier.has_exceeded_credit_limit" class="mt-1 text-xs text-red-600 font-medium">
                                            Credit Limit Exceeded!
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button @click="editSupplier(supplier)" class="text-blue-600 hover:text-blue-900 mr-4">
                                            Edit
                                        </button>
                                        <button @click="confirmDelete(supplier)" class="text-red-600 hover:text-red-900">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-gray-200" v-if="suppliers.data && suppliers.data.length > 0">
                        <Pagination :links="suppliers.links" />
                    </div>

                    <!-- Empty State -->
                    <div v-if="suppliers.data && suppliers.data.length === 0" class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No suppliers</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by adding your first supplier.</p>
                        <div class="mt-6">
                            <PrimaryButton @click="showCreateModal = true">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                New Supplier
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Supplier Modal -->
        <Modal :show="showCreateModal || showEditModal" @close="closeModal" maxWidth="4xl">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    {{ editingSupplier ? 'Edit Supplier' : 'Create New Supplier' }}
                </h3>
                
                <form @submit.prevent="editingSupplier ? updateSupplier() : createSupplier()">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-4">
                            <div>
                                <InputLabel for="name" value="Supplier Name *" />
                                <TextInput
                                    id="name"
                                    type="text"
                                    v-model="form.name"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <div>
                                <InputLabel for="contact_person" value="Contact Person" />
                                <TextInput
                                    id="contact_person"
                                    type="text"
                                    v-model="form.contact_person"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.contact_person" />
                            </div>

                            <div>
                                <InputLabel for="email" value="Email Address" />
                                <TextInput
                                    id="email"
                                    type="email"
                                    v-model="form.email"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="phone" value="Phone Number" />
                                    <TextInput
                                        id="phone"
                                        type="text"
                                        v-model="form.phone"
                                        class="mt-1 block w-full"
                                    />
                                    <InputError class="mt-2" :message="form.errors.phone" />
                                </div>

                                <div>
                                    <InputLabel for="phone_2" value="Alternate Phone" />
                                    <TextInput
                                        id="phone_2"
                                        type="text"
                                        v-model="form.phone_2"
                                        class="mt-1 block w-full"
                                    />
                                    <InputError class="mt-2" :message="form.errors.phone_2" />
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-4">
                            <div>
                                <InputLabel for="address" value="Address" />
                                <textarea
                                    id="address"
                                    v-model="form.address"
                                    rows="2"
                                    class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.address" />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="city" value="City" />
                                    <TextInput
                                        id="city"
                                        type="text"
                                        v-model="form.city"
                                        class="mt-1 block w-full"
                                    />
                                    <InputError class="mt-2" :message="form.errors.city" />
                                </div>

                                <div>
                                    <InputLabel for="state" value="State/Region" />
                                    <TextInput
                                        id="state"
                                        type="text"
                                        v-model="form.state"
                                        class="mt-1 block w-full"
                                    />
                                    <InputError class="mt-2" :message="form.errors.state" />
                                </div>
                            </div>

                            <div>
                                <InputLabel for="tax_id" value="Tax ID/VAT Number" />
                                <TextInput
                                    id="tax_id"
                                    type="text"
                                    v-model="form.tax_id"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.tax_id" />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="payment_terms" value="Payment Terms" />
                                    <select
                                        id="payment_terms"
                                        v-model="form.payment_terms"
                                        class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                    >
                                        <option value="cash">Cash on Delivery</option>
                                        <option value="7_days">7 Days</option>
                                        <option value="14_days">14 Days</option>
                                        <option value="30_days" selected>30 Days</option>
                                        <option value="60_days">60 Days</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.payment_terms" />
                                </div>

                                <div>
                                    <InputLabel for="credit_limit" value="Credit Limit (₵)" />
                                    <TextInput
                                        id="credit_limit"
                                        type="number"
                                        step="0.01"
                                        v-model="form.credit_limit"
                                        class="mt-1 block w-full"
                                        min="0"
                                    />
                                    <InputError class="mt-2" :message="form.errors.credit_limit" />
                                </div>
                            </div>

                            <div>
                                <InputLabel for="notes" value="Notes" />
                                <textarea
                                    id="notes"
                                    v-model="form.notes"
                                    rows="2"
                                    class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.notes" />
                            </div>

                            <div>
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        v-model="form.is_active"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Active Supplier</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <SecondaryButton @click="closeModal" type="button">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing">
                            {{ editingSupplier ? 'Update' : 'Create' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <ConfirmationModal :show="showDeleteModal" @close="showDeleteModal = false">
            <template #title>
                Delete Supplier
            </template>

            <template #content>
                Are you sure you want to delete <strong>{{ supplierToDelete?.name }}</strong>?
                <div v-if="supplierToDelete?.purchase_orders_count > 0" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-md">
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-red-800">Warning!</p>
                            <p class="text-sm text-red-600">
                                This supplier has {{ supplierToDelete?.purchase_orders_count }} purchase orders. 
                                Deleting it will affect those records.
                            </p>
                        </div>
                    </div>
                </div>
                <div v-else class="mt-3 text-sm text-gray-600">
                    This supplier has no purchase orders.
                </div>
            </template>

            <template #footer>
                <SecondaryButton @click="showDeleteModal = false">
                    Cancel
                </SecondaryButton>

                <DangerButton
                    class="ml-3"
                    @click="deleteSupplier"
                    :disabled="form.processing"
                >
                    Delete Supplier
                </DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    suppliers: Object,
});

const search = ref('');
const statusFilter = ref('');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const editingSupplier = ref(null);
const supplierToDelete = ref(null);

const form = useForm({
    name: '',
    contact_person: '',
    email: '',
    phone: '',
    phone_2: '',
    address: '',
    city: '',
    state: '',
    country: 'Ghana',
    tax_id: '',
    payment_terms: '30_days',
    credit_limit: 0,
    notes: '',
    is_active: true,
});

const filteredSuppliers = computed(() => {
    let filtered = props.suppliers.data || [];

    // Apply search filter
    if (search.value) {
        const searchTerm = search.value.toLowerCase();
        filtered = filtered.filter(supplier =>
            supplier.name.toLowerCase().includes(searchTerm) ||
            supplier.supplier_code.toLowerCase().includes(searchTerm) ||
            (supplier.email && supplier.email.toLowerCase().includes(searchTerm)) ||
            (supplier.phone && supplier.phone.toLowerCase().includes(searchTerm)) ||
            (supplier.contact_person && supplier.contact_person.toLowerCase().includes(searchTerm))
        );
    }

    // Apply status filter
    if (statusFilter.value) {
        if (statusFilter.value === 'active') {
            filtered = filtered.filter(supplier => supplier.is_active);
        } else if (statusFilter.value === 'inactive') {
            filtered = filtered.filter(supplier => !supplier.is_active);
        }
    }

    return filtered;
});

const clearFilters = () => {
    search.value = '';
    statusFilter.value = '';
};

const formatPaymentTerms = (terms) => {
    const termsMap = {
        'cash': 'Cash',
        '7_days': '7 Days',
        '14_days': '14 Days',
        '30_days': '30 Days',
        '60_days': '60 Days',
    };
    return termsMap[terms] || terms;
};

const editSupplier = (supplier) => {
    editingSupplier.value = supplier;
    form.name = supplier.name;
    form.contact_person = supplier.contact_person || '';
    form.email = supplier.email || '';
    form.phone = supplier.phone || '';
    form.phone_2 = supplier.phone_2 || '';
    form.address = supplier.address || '';
    form.city = supplier.city || '';
    form.state = supplier.state || '';
    form.country = supplier.country || 'Ghana';
    form.tax_id = supplier.tax_id || '';
    form.payment_terms = supplier.payment_terms;
    form.credit_limit = parseFloat(supplier.credit_limit) || 0;
    form.notes = supplier.notes || '';
    form.is_active = supplier.is_active;
    showEditModal.value = true;
};

const confirmDelete = (supplier) => {
    supplierToDelete.value = supplier;
    showDeleteModal.value = true;
};

const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    form.reset();
    editingSupplier.value = null;
};

const createSupplier = () => {
    form.post(route('suppliers.store'), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            form.reset();
        },
    });
};

const updateSupplier = () => {
    form.put(route('suppliers.update', editingSupplier.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            form.reset();
        },
    });
};

const deleteSupplier = () => {
    router.delete(route('suppliers.destroy', supplierToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            supplierToDelete.value = null;
        },
    });
};
</script>