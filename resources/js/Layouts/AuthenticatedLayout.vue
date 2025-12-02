<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

const showingNavigationDropdown = ref(false);

const page = usePage();
const user = computed(() => page.props.auth.user);

const can = (permission) => {
    return user.value?.permissions?.includes(permission);
};

const hasRole = (role) => {
    return user.value?.roles?.includes(role);
};
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <nav class="bg-white border-b border-gray-100">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800" />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    Dashboard
                                </NavLink>
                                
                                <!-- Admin Menu -->
                                <div v-if="can('view users') || can('view roles')" class="hidden sm:flex sm:items-center sm:ml-6">
                                    <div class="relative group">
                                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition">
                                            Admin
                                            <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                        <div class="absolute hidden group-hover:block mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10">
                                            <div class="py-1">
                                                <NavLink v-if="can('view users')" :href="route('admin.users.index')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    User Management
                                                </NavLink>
                                                <NavLink v-if="can('view roles')" :href="route('admin.roles.index')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    Role Management
                                                </NavLink>
                                                <NavLink v-if="hasRole('Super Admin')" :href="route('admin.dashboard')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    Admin Dashboard
                                                </NavLink>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- POS Menu Items (Add based on permissions) -->
                                <NavLink v-if="can('view products')" href="#" :active="route().current('products.index')">
                                    Products
                                </NavLink>
                                <NavLink v-if="can('view inventory')" href="#" :active="route().current('inventory.index')">
                                    Inventory
                                </NavLink>
                                <NavLink v-if="can('create sales')" href="#" :active="route().current('sales.create')">
                                    POS
                                </NavLink>
                                <NavLink v-if="can('view customers')" href="#" :active="route().current('customers.index')">
                                    Customers
                                </NavLink>
                            </div>
                        </div>

                        <!-- Settings Dropdown -->
                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                        <div>{{ user?.name }}</div>
                                        <div class="ml-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </template>

                                <template #content>
                                    <DropdownLink :href="route('profile.edit')"> Profile </DropdownLink>
                                    <DropdownLink :href="route('logout')" method="post" as="button">
                                        Log Out
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>

                        <!-- Hamburger -->
                        <div class="-mr-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex': !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex': showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
                    class="sm:hidden"
                >
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Dashboard
                        </ResponsiveNavLink>
                        
                        <!-- Responsive Admin Menu -->
                        <div v-if="can('view users') || can('view roles')" class="space-y-1">
                            <div class="px-4 pt-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                Admin
                            </div>
                            <ResponsiveNavLink v-if="can('view users')" :href="route('admin.users.index')" :active="route().current('admin.users.index')">
                                User Management
                            </ResponsiveNavLink>
                            <ResponsiveNavLink v-if="can('view roles')" :href="route('admin.roles.index')" :active="route().current('admin.roles.index')">
                                Role Management
                            </ResponsiveNavLink>
                            <ResponsiveNavLink v-if="hasRole('Super Admin')" :href="route('admin.dashboard')" :active="route().current('admin.dashboard')">
                                Admin Dashboard
                            </ResponsiveNavLink>
                        </div>

                        <!-- Responsive POS Menu -->
                        <ResponsiveNavLink v-if="can('view products')" href="#" :active="route().current('products.index')">
                            Products
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="can('view inventory')" href="#" :active="route().current('inventory.index')">
                            Inventory
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="can('create sales')" href="#" :active="route().current('sales.create')">
                            POS
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="can('view customers')" href="#" :active="route().current('customers.index')">
                            Customers
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="px-4">
                            <div class="font-medium text-base text-gray-800">
                                {{ user?.name }}
                            </div>
                            <div class="font-medium text-sm text-gray-500">
                                {{ user?.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')"> Profile </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white shadow" v-if="$slots.header">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>