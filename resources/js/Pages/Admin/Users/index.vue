<!-- resources/js/Pages/Admin/Users/Index.vue - Update the template section -->
<template>
    <Head title="Users" />
    
    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900">User Management</h2>
                    <p class="mt-1 text-sm text-gray-600">Manage system users and their permissions</p>
                </div>
                <PrimaryButton @click="showCreateModal = true">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add User
                </PrimaryButton>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Users Table -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input
                                    type="text"
                                    v-model="search"
                                    placeholder="Search users..."
                                    class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-64 sm:text-sm"
                                />
                            </div>
                            <div class="flex items-center space-x-4">
                                          <select
                                    v-model="roleFilter"
                                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="">All Roles</option>
                                    <option v-for="role in roles" :key="role.id" :value="role.name">
                                        {{ role.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        User
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Role
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Email
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
                                <tr v-for="user in filteredUsers" :key="user.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 flex-shrink-0">
                                                <div class="h-10 w-10 rounded-full bg-blue-600 flex items-center justify-center">
                                                    <span class="font-semibold text-white">
                                                        {{ getUserInitials(user.name) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ user.name }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span v-for="role in user.roles" :key="role.id"
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                            :class="getRoleBadgeClass(role.name)">
                                            {{ role.name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ user.email }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDate(user.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button
                                            @click="editUser(user)"
                                            class="text-blue-600 hover:text-blue-900 mr-4"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            v-if="user.id !== $page.props.auth.user.id"
                                            @click="confirmDelete(user)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-gray-200" v-if="users.data && users.data.length > 0">
                        <Pagination :links="users.links" />
                    </div>

                    <!-- Empty State -->
                    <div v-if="users.data && users.data.length === 0" class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No users</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating a new user.</p>
                        <div class="mt-6">
                            <PrimaryButton @click="showCreateModal = true">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                New User
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit User Modal -->
        <Modal :show="showCreateModal || showEditModal" @close="closeModal">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    {{ editingUser ? 'Edit User' : 'Create New User' }}
                </h3>
                
                <form @submit.prevent="editingUser ? updateUser() : createUser()">
                    <div class="space-y-4">
                        <div>
                            <InputLabel for="name" value="Name" />
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
                            <InputLabel for="email" value="Email" />
                            <TextInput
                                id="email"
                                type="email"
                                v-model="form.email"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div>
                            <InputLabel for="role" value="Role" />
                                              <select
                                id="role"
                                v-model="form.role"
                                class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                required
                            >
                                <option value="">Select a role</option>
                                <option v-for="role in roles" :key="role.id" :value="role.name">
                                    {{ role.name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.role" />
                        </div>

                        <div>
                            <InputLabel for="password" :value="editingUser ? 'Password (leave blank to keep current)' : 'Password'" />
                            <TextInput
                                id="password"
                                type="password"
                                v-model="form.password"
                                class="mt-1 block w-full"
                                :required="!editingUser"
                            />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <div v-if="!editingUser || form.password">
                            <InputLabel for="password_confirmation" value="Confirm Password" />
                            <TextInput
                                id="password_confirmation"
                                type="password"
                                v-model="form.password_confirmation"
                                class="mt-1 block w-full"
                                :required="!editingUser || form.password"
                            />
                            <InputError class="mt-2" :message="form.errors.password_confirmation" />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <SecondaryButton @click="closeModal" type="button">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing">
                            {{ editingUser ? 'Update' : 'Create' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <ConfirmationModal :show="showDeleteModal" @close="showDeleteModal = false">
            <template #title>
                Delete User
            </template>

            <template #content>
                Are you sure you want to delete <strong>{{ userToDelete?.name }}</strong>? This action cannot be undone.
            </template>

            <template #footer>
                <SecondaryButton @click="showDeleteModal = false">
                    Cancel
                </SecondaryButton>

                <DangerButton
                    class="ml-3"
                    @click="deleteUser"
                    :disabled="form.processing"
                >
                    Delete User
                </DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
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

// REMOVE Heroicons imports
// import {
//     PlusIcon,
//     SearchIcon,
//     UserGroupIcon,
// } from '@heroicons/vue/outline';

const props = defineProps({
    users: Object,
    roles: Array
});


const page = usePage();
const search = ref('');
const roleFilter = ref('');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const editingUser = ref(null);
const userToDelete = ref(null);

const form = useForm({
    name: '',
    email: '',
    role: '',
    password: '',
    password_confirmation: '',
});
// In your Users Index.vue script

onMounted(async () => {
    try {
        // Make sure the route exists and returns JSON
        const response = await fetch(route('admin.roles.index'));
        const data = await response.json();
        roles.value = data.roles || [];
    } catch (error) {
        console.error('Failed to fetch roles:', error);
        // Fallback to empty array
        roles.value = [];
    }
});
const filteredUsers = computed(() => {
    let filtered = props.users.data || [];

    // Apply search filter
    if (search.value) {
        const searchTerm = search.value.toLowerCase();
        filtered = filtered.filter(user =>
            user.name.toLowerCase().includes(searchTerm) ||
            user.email.toLowerCase().includes(searchTerm)
        );
    }

    // Apply role filter
    if (roleFilter.value) {
        filtered = filtered.filter(user =>
            user.roles.some(role => role.name === roleFilter.value)
        );
    }

    return filtered;
});

const getUserInitials = (name) => {
    if (!name) return 'U';
    return name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
};

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

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

const editUser = (user) => {
    editingUser.value = user;
    form.name = user.name;
    form.email = user.email;
    form.role = user.roles[0]?.name || '';
    form.password = '';
    form.password_confirmation = '';
    showEditModal.value = true;
};

const confirmDelete = (user) => {
    userToDelete.value = user;
    showDeleteModal.value = true;
};

const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    form.reset();
    editingUser.value = null;
};

const createUser = () => {
    form.post(route('admin.users.store'), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            form.reset();
        },
    });
};

const updateUser = () => {
    form.put(route('admin.users.update', editingUser.value), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            form.reset();
        },
    });
};

const deleteUser = () => {
    router.delete(route('admin.users.destroy', userToDelete.value), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            userToDelete.value = null;
        },
    });
};
// ... rest of your script remains the same
</script>