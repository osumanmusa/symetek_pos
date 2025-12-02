<template>
    <Head title="Roles" />
    
    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900">Roles & Permissions</h2>
                    <p class="mt-1 text-sm text-gray-600">Manage user roles and their permissions</p>
                </div>
                <PrimaryButton @click="showCreateModal = true">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Role
                </PrimaryButton>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Roles Grid -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                        <div v-for="role in roles" :key="role.id"
                            class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex justify-between items-start mb-3">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                    :class="getRoleBadgeClass(role.name)">
                                    {{ role.name }}
                                </span>
                                <span class="text-sm text-gray-500">
                                    {{ role.users_count }} users
                                </span>
                            </div>
                            
                            <div class="text-sm text-gray-600 mb-3">
                                <span class="font-medium">{{ role.permissions_count || 0 }}</span> permissions
                            </div>
                            
                            <!-- PERMISSIONS BUTTON - ADDED HERE -->
                            <div class="mb-4">
                                <Link :href="route('admin.roles.permissions', role.id)"
                                    class="inline-flex items-center w-full px-3 py-2 text-xs font-medium text-center text-green-700 bg-green-100 rounded-lg hover:bg-green-200 focus:ring-4 focus:outline-none focus:ring-green-300">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    Manage Permissions
                                </Link>
                            </div>
                            
                            <div class="flex justify-end space-x-2">
                                <button
                                    @click="editRole(role)"
                                    class="text-blue-600 hover:text-blue-900 text-sm"
                                >
                                    Edit
                                </button>
                                <button
                                    v-if="role.users_count === 0"
                                    @click="confirmDelete(role)"
                                    class="text-red-600 hover:text-red-900 text-sm"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="roles.length === 0" class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No roles</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating a new role.</p>
                        <div class="mt-6">
                            <PrimaryButton @click="showCreateModal = true">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                New Role
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Role Modal -->
        <Modal :show="showCreateModal || showEditModal" @close="closeModal">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    {{ editingRole ? 'Edit Role' : 'Create New Role' }}
                </h3>
                
                <form @submit.prevent="editingRole ? updateRole() : createRole()">
                    <div class="space-y-4">
                        <div>
                            <InputLabel for="name" value="Role Name" />
                            <TextInput
                                id="name"
                                type="text"
                                v-model="form.name"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <SecondaryButton @click="closeModal" type="button">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing">
                            {{ editingRole ? 'Update' : 'Create' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <ConfirmationModal :show="showDeleteModal" @close="showDeleteModal = false">
            <template #title>
                Delete Role
            </template>

            <template #content>
                Are you sure you want to delete <strong>{{ roleToDelete?.name }}</strong>? This action cannot be undone.
            </template>

            <template #footer>
                <SecondaryButton @click="showDeleteModal = false">
                    Cancel
                </SecondaryButton>

                <DangerButton
                    class="ml-3"
                    @click="deleteRole"
                    :disabled="form.processing"
                >
                    Delete Role
                </DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3'; // Added Link import
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    roles: Array,
});

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const editingRole = ref(null);
const roleToDelete = ref(null);

const form = useForm({
    name: '',
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

const editRole = (role) => {
    editingRole.value = role;
    form.name = role.name;
    showEditModal.value = true;
};

const confirmDelete = (role) => {
    roleToDelete.value = role;
    showDeleteModal.value = true;
};

const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    form.reset();
    editingRole.value = null;
};

const createRole = () => {
    form.post(route('admin.roles.store'), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            form.reset();
        },
    });
};

const updateRole = () => {
    form.put(route('admin.roles.update', editingRole.value), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            form.reset();
        },
    });
};

const deleteRole = () => {
    router.delete(route('admin.roles.destroy', roleToDelete.value), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            roleToDelete.value = null;
        },
    });
};
</script>