<template>
    <Head :title="`Permissions - ${role.name}`" />
    
    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900">Role Permissions</h2>
                    <div class="mt-1 flex items-center space-x-2">
                        <span class="text-sm text-gray-600">Managing permissions for role:</span>
                        <span class="px-3 py-1 text-sm font-semibold rounded-full" 
                              :class="getRoleBadgeClass(role.name)">
                            {{ role.name }}
                        </span>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <SecondaryButton @click="$inertia.visit(route('admin.roles.index'))">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Roles
                    </SecondaryButton>
                    <PrimaryButton @click="savePermissions" :disabled="form.processing">
                        <svg v-if="!form.processing" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg v-else class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ form.processing ? 'Saving...' : 'Save Permissions' }}
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Permissions Summary -->
                <div class="mb-6 bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Permissions Summary</h3>
                                <p class="mt-1 text-sm text-gray-600">
                                    {{ form.permissions.length }} of {{ totalPermissions }} permissions selected
                                    ({{ Math.round((form.permissions.length / totalPermissions) * 100) }}%)
                                </p>
                            </div>
                            <div class="flex space-x-2">
                                <button @click="selectAll" 
                                        class="px-3 py-1 text-xs bg-blue-100 text-blue-800 rounded hover:bg-blue-200">
                                    Select All
                                </button>
                                <button @click="deselectAll"
                                        class="px-3 py-1 text-xs bg-gray-100 text-gray-800 rounded hover:bg-gray-200">
                                    Deselect All
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Permissions by Category -->
                <div class="space-y-6">
                    <div v-for="(categoryPermissions, category) in permissions" :key="category" 
                         class="bg-white shadow-sm rounded-lg overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <h3 class="text-lg font-medium text-gray-900 capitalize">
                                        {{ category }} Permissions
                                    </h3>
                                    <span class="px-2 py-1 text-xs font-medium bg-gray-200 text-gray-800 rounded">
                                        {{ categoryPermissions.length }} permissions
                                    </span>
                                </div>
                                <button @click="toggleCategory(category)"
                                        class="px-3 py-1 text-xs bg-gray-100 text-gray-800 rounded hover:bg-gray-200">
                                    {{ isCategorySelected(category) ? 'Deselect All' : 'Select All' }}
                                </button>
                            </div>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ getCategoryDescription(category) }}
                            </p>
                        </div>
                        
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <!-- PERMISSION CARD - UPDATED FOR CLICKABLE ENTIRE CARD -->
                                <div v-for="permission in categoryPermissions" :key="permission.id"
                                     @click="togglePermission(permission.name)"
                                     class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer"
                                     :class="{ 
                                         'border-blue-300 bg-blue-50': form.permissions.includes(permission.name),
                                         'border-green-300 bg-green-50': form.permissions.includes(permission.name) && category === 'view',
                                         'border-yellow-300 bg-yellow-50': form.permissions.includes(permission.name) && category === 'create',
                                         'border-purple-300 bg-purple-50': form.permissions.includes(permission.name) && category === 'edit',
                                         'border-red-300 bg-red-50': form.permissions.includes(permission.name) && category === 'delete'
                                     }">
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5 mr-3">
                                            <div class="flex items-center justify-center w-5 h-5 border border-gray-300 rounded"
                                                 :class="{
                                                     'bg-blue-500 border-blue-500': form.permissions.includes(permission.name),
                                                     'bg-green-500 border-green-500': form.permissions.includes(permission.name) && category === 'view',
                                                     'bg-yellow-500 border-yellow-500': form.permissions.includes(permission.name) && category === 'create',
                                                     'bg-purple-500 border-purple-500': form.permissions.includes(permission.name) && category === 'edit',
                                                     'bg-red-500 border-red-500': form.permissions.includes(permission.name) && category === 'delete'
                                                 }">
                                                <svg v-if="form.permissions.includes(permission.name)" 
                                                     class="w-3 h-3 text-white" 
                                                     fill="none" 
                                                     stroke="currentColor" 
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between">
                                                <h4 class="text-sm font-medium text-gray-900">
                                                    {{ formatPermissionName(permission.name) }}
                                                </h4>
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                                      :class="getPermissionBadgeClass(category)">
                                                    {{ category }}
                                                </span>
                                            </div>
                                            <p class="mt-1 text-xs text-gray-500">
                                                {{ getPermissionDescription(permission.name) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Save Button Sticky -->
                <div class="sticky bottom-6 mt-8">
                    <div class="bg-white shadow-lg rounded-lg p-4 border border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ form.permissions.length }} permissions selected
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Changes will be applied to all users with the <span class="font-semibold">{{ role.name }}</span> role
                                </p>
                            </div>
                            <div class="flex space-x-3">
                                <SecondaryButton @click="$inertia.visit(route('admin.roles.index'))">
                                    Cancel
                                </SecondaryButton>
                                <PrimaryButton @click="savePermissions" :disabled="form.processing" class="min-w-[120px]">
                                    <svg v-if="!form.processing" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                                </PrimaryButton>
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
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    role: Object,
    permissions: Object,
    rolePermissions: Array,
});

const form = useForm({
    permissions: [...props.rolePermissions],
});

// Calculate total number of permissions
const totalPermissions = computed(() => {
    let total = 0;
    Object.values(props.permissions).forEach(category => {
        total += category.length;
    });
    return total;
});

const getRoleBadgeClass = (roleName) => {
    const classes = {
        'Super Admin': 'bg-purple-100 text-purple-800',
        'Admin': 'bg-blue-100 text-blue-800',
        'Manager': 'bg-green-100 text-green-800',
        'Cashier': 'bg-yellow-100 text-yellow-800',
        'default': 'bg-gray-100 text-gray-800',
    };
    return classes[roleName] || classes.default;
};

const getPermissionBadgeClass = (category) => {
    const classes = {
        'view': 'bg-green-100 text-green-800',
        'create': 'bg-yellow-100 text-yellow-800',
        'edit': 'bg-purple-100 text-purple-800',
        'delete': 'bg-red-100 text-red-800',
        'manage': 'bg-blue-100 text-blue-800',
        'adjust': 'bg-indigo-100 text-indigo-800',
        'apply': 'bg-pink-100 text-pink-800',
        'void': 'bg-red-100 text-red-800',
        'default': 'bg-gray-100 text-gray-800',
    };
    return classes[category] || classes.default;
};

const formatPermissionName = (permission) => {
    // Convert "view users" to "View Users"
    return permission
        .split(' ')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};

const getPermissionDescription = (permission) => {
    const descriptions = {
        'view dashboard': 'Access to the main dashboard',
        'view users': 'View list of all users',
        'create users': 'Create new user accounts',
        'edit users': 'Edit existing user information',
        'delete users': 'Delete user accounts',
        'view roles': 'View available roles',
        'create roles': 'Create new roles',
        'edit roles': 'Edit role information',
        'delete roles': 'Delete roles',
        'view products': 'Browse product catalog',
        'create products': 'Add new products',
        'edit products': 'Modify product details',
        'delete products': 'Remove products',
        'view categories': 'View product categories',
        'create categories': 'Create new categories',
        'edit categories': 'Edit category information',
        'delete categories': 'Delete categories',
        'view inventory': 'Check stock levels',
        'create inventory': 'Add new inventory items',
        'edit inventory': 'Update inventory information',
        'delete inventory': 'Remove inventory items',
        'adjust inventory': 'Adjust stock quantities',
        'view suppliers': 'View supplier list',
        'create suppliers': 'Add new suppliers',
        'edit suppliers': 'Edit supplier details',
        'delete suppliers': 'Delete suppliers',
        'view sales': 'View sales history',
        'create sales': 'Process new sales (POS)',
        'edit sales': 'Modify sales records',
        'delete sales': 'Delete sales transactions',
        'void sales': 'Void or refund sales',
        'apply discounts': 'Apply discounts to sales',
        'view customers': 'View customer database',
        'create customers': 'Add new customers',
        'edit customers': 'Edit customer information',
        'delete customers': 'Delete customer records',
        'view orders': 'View purchase orders',
        'create orders': 'Create new orders',
        'edit orders': 'Modify order details',
        'delete orders': 'Delete orders',
        'view sales reports': 'Generate sales reports',
        'view inventory reports': 'Generate inventory reports',
        'view financial reports': 'Generate financial reports',
        'view customer reports': 'Generate customer reports',
        'manage settings': 'Configure system settings',
        'manage taxes': 'Manage tax configurations',
        'manage discounts': 'Configure discount rules',
    };
    
    return descriptions[permission] || 'System permission';
};

const getCategoryDescription = (category) => {
    const descriptions = {
        'view': 'Permissions to view or read data',
        'create': 'Permissions to create new records',
        'edit': 'Permissions to edit existing records',
        'delete': 'Permissions to delete records',
        'manage': 'Permissions to manage configurations',
        'adjust': 'Permissions to adjust quantities',
        'apply': 'Permissions to apply changes',
        'void': 'Permissions to void transactions',
    };
    return descriptions[category] || 'System permissions';
};

// NEW: Toggle permission when clicking anywhere on the card
const togglePermission = (permissionName) => {
    const index = form.permissions.indexOf(permissionName);
    if (index === -1) {
        // Add permission
        form.permissions.push(permissionName);
    } else {
        // Remove permission
        form.permissions.splice(index, 1);
    }
};

const selectAll = () => {
    const allPermissions = [];
    Object.values(props.permissions).forEach(category => {
        category.forEach(permission => {
            allPermissions.push(permission.name);
        });
    });
    form.permissions = [...new Set(allPermissions)]; // Remove duplicates
};

const deselectAll = () => {
    form.permissions = [];
};

const toggleCategory = (category) => {
    const categoryPermissions = props.permissions[category] || [];
    const categoryPermissionNames = categoryPermissions.map(p => p.name);
    
    // Check if all category permissions are selected
    const allSelected = categoryPermissionNames.every(name => 
        form.permissions.includes(name)
    );
    
    if (allSelected) {
        // Deselect all in category
        form.permissions = form.permissions.filter(name => 
            !categoryPermissionNames.includes(name)
        );
    } else {
        // Select all in category
        const newPermissions = [...new Set([...form.permissions, ...categoryPermissionNames])];
        form.permissions = newPermissions;
    }
};

const isCategorySelected = (category) => {
    const categoryPermissions = props.permissions[category] || [];
    if (categoryPermissions.length === 0) return false;
    
    return categoryPermissions.every(permission => 
        form.permissions.includes(permission.name)
    );
};

const savePermissions = () => {
    form.put(route('admin.roles.permissions.update', props.role.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Success - handled by Inertia
        },
        onError: (errors) => {
            console.error('Error saving permissions:', errors);
        },
    });
};
</script>