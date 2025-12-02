<template>
    <Head title="Categories" />
    
    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900">Categories Management</h2>
                    <p class="mt-1 text-sm text-gray-600">Organize your products into categories</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route('products.index')" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Products
                    </Link>
                    <PrimaryButton @click="showCreateModal = true">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Category
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
                                placeholder="Search categories..."
                                class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            />
                        </div>
                        
                        <select v-model="statusFilter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        
                        <select v-model="parentFilter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Parents</option>
                            <option value="null">No Parent (Top Level)</option>
                            <option v-for="parent in parentCategories" :key="parent.id" :value="parent.id">
                                {{ parent.name }}
                            </option>
                        </select>
                    </div>
                    <div class="mt-3 flex space-x-2">
                        <button @click="clearFilters" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200">
                            Clear Filters
                        </button>
                    </div>
                </div>

                <!-- Categories Table -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Category
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Parent
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Products/Subcategories
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Created
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="category in filteredCategories" :key="category.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 flex-shrink-0 bg-blue-100 rounded-lg flex items-center justify-center">
                                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                </svg>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ category.name }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    Slug: {{ category.slug }}
                                                </div>
                                                <div v-if="category.description" class="text-xs text-gray-500 truncate max-w-xs">
                                                    {{ category.description }}
                                                </div>
                                                <div v-if="category.children && category.children.length > 0" class="mt-1">
                                                    <div class="flex flex-wrap gap-1">
                                                        <span v-for="child in category.children.slice(0, 2)" :key="child.id" 
                                                              class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                            {{ child.name }}
                                                            <span class="ml-1 text-blue-600">{{ child.products_count || 0 }}</span>
                                                        </span>
                                                        <span v-if="category.children.length > 2" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                            +{{ category.children.length - 2 }} more
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span v-if="category.parent" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ category.parent.name }}
                                        </span>
                                        <span v-else class="text-xs text-gray-400 italic">Top Level</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ category.products_count || 0 }} products
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ category.children?.length || 0 }} sub-categories
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span v-if="category.is_active" 
                                              class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Active
                                        </span>
                                        <span v-else 
                                              class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Inactive
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDate(category.created_at) }}
                                    </td>
                                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
    <div class="flex space-x-2">
        <!-- Edit Button with outline style -->
        <button 
            @click="editCategory(category)"
            class="inline-flex items-center px-3 py-1.5 border border-blue-300 text-xs font-medium rounded text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit
        </button>
        
        <!-- Delete Button with outline style -->
        <button 
            @click="confirmDelete(category)"
            class="inline-flex items-center px-3 py-1.5 border border-red-300 text-xs font-medium rounded text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
        >
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Delete
        </button>
    </div>
</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-gray-200" v-if="categories.data && categories.data.length > 0">
                        <Pagination :links="categories.links" />
                    </div>

                    <!-- Empty State -->
                    <div v-if="categories.data && categories.data.length === 0" class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No categories</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating your first category.</p>
                        <div class="mt-6">
                            <PrimaryButton @click="showCreateModal = true">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                New Category
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Category Modal -->
        <Modal :show="showCreateModal || showEditModal" @close="closeModal" maxWidth="3xl">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    {{ editingCategory ? 'Edit Category' : 'Create New Category' }}
                </h3>
                
                <form @submit.prevent="editingCategory ? updateCategory() : createCategory()">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-4">
                            <div>
                                <InputLabel for="name" value="Category Name *" />
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
                                <InputLabel for="description" value="Description" />
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="4"
                                    class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.description" />
                            </div>

                            <div>
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        v-model="form.is_active"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Active</span>
                                </label>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-4">
                            <div>
                                <InputLabel for="parent_id" value="Parent Category" />
                                <select
                                    id="parent_id"
                                    v-model="form.parent_id"
                                    class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                >
                                    <option value="">No Parent (Top Level)</option>
                                    <option v-for="parent in availableParents" :key="parent.id" :value="parent.id">
                                        {{ parent.name }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.parent_id" />
                            </div>

                            <!-- Preview Section -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Preview</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Name:</span>
                                        <span class="font-medium">{{ form.name || '—' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Slug:</span>
                                        <span class="font-medium text-blue-600">{{ generateSlug(form.name) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Parent:</span>
                                        <span class="font-medium">
                                            {{ getParentName(form.parent_id) || 'Top Level' }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Status:</span>
                                        <span class="font-medium" :class="form.is_active ? 'text-green-600' : 'text-red-600'">
                                            {{ form.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <SecondaryButton @click="closeModal" type="button">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing">
                            {{ editingCategory ? 'Update' : 'Create' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <ConfirmationModal :show="showDeleteModal" @close="showDeleteModal = false">
            <template #title>
                Delete Category
            </template>

            <template #content>
                Are you sure you want to delete <strong>{{ categoryToDelete?.name }}</strong>?
                <div v-if="categoryToDelete?.products_count > 0" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-md">
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-red-800">Warning!</p>
                            <p class="text-sm text-red-600">
                                This category has {{ categoryToDelete?.products_count }} products. 
                                Deleting it will make those products uncategorized.
                            </p>
                        </div>
                    </div>
                </div>
                <div v-else-if="categoryToDelete?.children?.length > 0" class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                    <div class="flex">
                        <svg class="h-5 w-5 text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-yellow-800">Note:</p>
                            <p class="text-sm text-yellow-600">
                                This category has {{ categoryToDelete?.children?.length }} sub-categories. 
                                They will become top-level categories after deletion.
                            </p>
                        </div>
                    </div>
                </div>
                <div v-else class="mt-3 text-sm text-gray-600">
                    This category has no products or sub-categories.
                </div>
            </template>

            <template #footer>
                <SecondaryButton @click="showDeleteModal = false">
                    Cancel
                </SecondaryButton>

                <DangerButton
                    class="ml-3"
                    @click="deleteCategory"
                    :disabled="form.processing"
                >
                    Delete Category
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
    categories: Object,
    parentCategories: Array,
});

const search = ref('');
const statusFilter = ref('');
const parentFilter = ref('');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const editingCategory = ref(null);
const categoryToDelete = ref(null);

const form = useForm({
    name: '',
    description: '',
    parent_id: '',
    is_active: true,
});

// Get available parents for editing (exclude current category and its children)
const availableParents = computed(() => {
    let categories = props.parentCategories || [];
    
    // If editing, exclude current category
    if (editingCategory.value) {
        categories = categories.filter(cat => 
            cat.id !== editingCategory.value.id
        );
    }
    
    return categories;
});

const filteredCategories = computed(() => {
    let filtered = props.categories.data || [];

    // Apply search filter
    if (search.value) {
        const searchTerm = search.value.toLowerCase();
        filtered = filtered.filter(category =>
            category.name.toLowerCase().includes(searchTerm) ||
            category.slug.toLowerCase().includes(searchTerm) ||
            (category.description && category.description.toLowerCase().includes(searchTerm))
        );
    }

    // Apply status filter
    if (statusFilter.value) {
        if (statusFilter.value === 'active') {
            filtered = filtered.filter(category => category.is_active);
        } else if (statusFilter.value === 'inactive') {
            filtered = filtered.filter(category => !category.is_active);
        }
    }

    // Apply parent filter
    if (parentFilter.value) {
        if (parentFilter.value === 'null') {
            filtered = filtered.filter(category => !category.parent_id);
        } else {
            filtered = filtered.filter(category => category.parent_id == parentFilter.value);
        }
    }

    return filtered;
});

const clearFilters = () => {
    search.value = '';
    statusFilter.value = '';
    parentFilter.value = '';
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-GH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const generateSlug = (name) => {
    if (!name) return '';
    return name
        .toLowerCase()
        .replace(/[^\w\s]/g, '')
        .replace(/\s+/g, '-');
};

const getParentName = (parentId) => {
    if (!parentId) return '';
    const parent = props.parentCategories?.find(cat => cat.id == parentId);
    return parent?.name || '';
};

const editCategory = (category) => {
    editingCategory.value = category;
    form.name = category.name;
    form.description = category.description || '';
    form.parent_id = category.parent_id || '';
    form.is_active = category.is_active;
    showEditModal.value = true;
};

const confirmDelete = (category) => {
    categoryToDelete.value = category;
    showDeleteModal.value = true;
};

const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    form.reset();
    editingCategory.value = null;
};

const createCategory = () => {
    form.post(route('categories.store'), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            form.reset();
        },
    });
};

const updateCategory = () => {
    form.put(route('categories.update', editingCategory.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            form.reset();
        },
    });
};

const deleteCategory = () => {
    router.delete(route('categories.destroy', categoryToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            categoryToDelete.value = null;
        },
    });
};
</script>