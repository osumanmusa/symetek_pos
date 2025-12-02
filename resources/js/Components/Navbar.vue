<template>
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <!-- Left: Mobile menu button & title -->
                <div class="flex items-center">
                    <button @click="$emit('toggle-sidebar')" class="p-2 rounded-md text-gray-500 hover:text-gray-600 hover:bg-gray-100 lg:hidden">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path v-if="!sidebarOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <h1 class="ml-2 text-xl font-semibold text-gray-900">{{ title }}</h1>
                </div>

                <!-- Right: User menu & notifications -->
                <div class="flex items-center space-x-4">
                    <!-- Notifications -->
                    <button class="relative p-2 text-gray-500 hover:text-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>

                    <!-- User dropdown -->
                    <div class="relative">
                        <button @click="userMenuOpen = !userMenuOpen" class="flex items-center space-x-2 focus:outline-none">
                            <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                                {{ userInitials }}
                            </div>
                            <div class="hidden md:block text-left">
                                <p class="text-sm font-medium text-gray-900">{{ user.name }}</p>
                                <p class="text-xs text-gray-500">{{ user.roles[0] }}</p>
                            </div>
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Dropdown menu -->
                        <div v-if="userMenuOpen" @click.away="userMenuOpen = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                            <Link :href="route('profile.edit')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Your Profile
                            </Link>
                            <form @submit.prevent="logout">
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Sign out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';

defineProps({
    sidebarOpen: Boolean,
    title: {
        type: String,
        default: 'Dashboard'
    }
});

defineEmits(['toggle-sidebar']);

const page = usePage();
const user = page.props.auth.user;
const userMenuOpen = ref(false);

const userInitials = computed(() => {
    if (!user?.name) return 'U';
    return user.name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
});

const logout = () => {
    router.post(route('logout'));
};
</script>