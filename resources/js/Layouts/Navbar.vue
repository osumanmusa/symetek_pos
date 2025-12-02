<!-- resources/js/Components/Layout/Navbar.vue -->
<template>
    <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Left: Sidebar toggle and breadcrumbs -->
                <div class="flex items-center">
                    <!-- Mobile menu button -->
                    <button @click="$emit('toggle-sidebar')" 
                        class="lg:hidden p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <span class="sr-only">Open sidebar</span>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    
                    <!-- Breadcrumbs -->
                    <nav class="hidden lg:flex items-center space-x-4 ml-4">
                        <Link :href="route('dashboard')" 
                              class="text-sm font-medium text-gray-500 hover:text-gray-700">
                            Dashboard
                        </Link>
                        <svg v-if="breadcrumbs.length > 0" class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span v-for="(crumb, index) in breadcrumbs" :key="index" class="flex items-center">
                            <Link v-if="index < breadcrumbs.length - 1" 
                                  :href="crumb.url"
                                  class="text-sm font-medium text-gray-500 hover:text-gray-700">
                                {{ crumb.title }}
                            </Link>
                            <span v-else class="text-sm font-medium text-gray-900">
                                {{ crumb.title }}
                            </span>
                            <svg v-if="index < breadcrumbs.length - 1" 
                                 class="w-5 h-5 text-gray-400 ml-4" 
                                 fill="currentColor" 
                                 viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </nav>
                </div>

                <!-- Right: User menu and notifications -->
                <div class="flex items-center space-x-4">
                    <!-- Notifications -->
                    <button @click="notificationsOpen = !notificationsOpen"
                        class="p-2 rounded-full text-gray-500 hover:text-gray-700 hover:bg-gray-100 relative">
                        <span class="sr-only">View notifications</span>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span v-if="unreadNotifications > 0" 
                              class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                            {{ unreadNotifications }}
                        </span>
                    </button>
                    
                    <!-- Quick Actions -->
                    <div class="relative">
                        <button @click="quickActionsOpen = !quickActionsOpen"
                            class="p-2 rounded-full text-gray-500 hover:text-gray-700 hover:bg-gray-100">
                            <span class="sr-only">Quick actions</span>
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </button>
                        
                        <!-- Quick Actions Dropdown -->
                        <div v-if="quickActionsOpen" 
                             class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                            <Link :href="route('sales.create')" 
                                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                  @click="closeDropdowns">
                                New Sale
                            </Link>
                            <Link v-if="can('view products')" 
                                  :href="route('products.create')"
                                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                  @click="closeDropdowns">
                                Add Product
                            </Link>
                            <Link v-if="can('view customers')" 
                                  :href="route('customers.create')"
                                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                  @click="closeDropdowns">
                                Add Customer
                            </Link>
                        </div>
                    </div>

                    <!-- User menu -->
                    <div class="relative">
                        <button @click="userMenuOpen = !userMenuOpen"
                            class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100">
                            <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center">
                                <span class="text-sm font-semibold text-white">{{ userInitials }}</span>
                            </div>
                            <div class="hidden lg:block text-left">
                                <p class="text-sm font-medium text-gray-900">{{ user.name }}</p>
                                <p class="text-xs text-gray-500">{{ user.email }}</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        
                        <!-- User dropdown -->
                        <div v-if="userMenuOpen" 
                             class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                            <Link :href="route('profile.edit')" 
                                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                  @click="closeDropdowns">
                                Your Profile
                            </Link>
                            <Link :href="route('settings')" 
                                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                  @click="closeDropdowns">
                                Settings
                            </Link>
                            <div class="border-t border-gray-100"></div>
                            <form @submit.prevent="logout">
                                <button type="submit"
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                    @click="closeDropdowns">
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
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

defineEmits(['toggle-sidebar']);

const userMenuOpen = ref(false);
const quickActionsOpen = ref(false);
const notificationsOpen = ref(false);

const page = usePage();
const user = computed(() => page.props.auth.user);

const userInitials = computed(() => {
    if (!user.value?.name) return 'U';
    return user.value.name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
});

const unreadNotifications = computed(() => {
    return page.props.notifications?.unread_count || 0;
});

// Breadcrumb logic
const breadcrumbs = computed(() => {
    const currentUrl = page.url;
    const crumbs = [];
    
    if (currentUrl === '/dashboard') {
        return crumbs;
    }
    
    // Parse current URL for additional crumbs
    const path = currentUrl.replace('/admin', '');
    const segments = path.split('/').filter(seg => seg && seg !== 'dashboard');
    
    let url = '/dashboard';
    segments.forEach((segment, index) => {
        url += '/' + segment;
        const title = segment
            .replace(/-/g, ' ')
            .replace(/\b\w/g, l => l.toUpperCase());
        crumbs.push({ title, url });
    });
    
    return crumbs;
});

const can = (permission) => {
    return user.value?.permissions?.includes(permission);
};

const closeDropdowns = () => {
    userMenuOpen.value = false;
    quickActionsOpen.value = false;
    notificationsOpen.value = false;
};

const logout = () => {
    router.post(route('logout'));
};

// Close dropdowns on escape key
const handleEscape = (e) => {
    if (e.key === 'Escape') {
        closeDropdowns();
    }
};

// Close dropdowns when clicking outside
const handleClickOutside = (e) => {
    if (!e.target.closest('.relative')) {
        closeDropdowns();
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleEscape);
    window.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleEscape);
    window.removeEventListener('click', handleClickOutside);
});
</script>