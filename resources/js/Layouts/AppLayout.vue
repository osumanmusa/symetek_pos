<!-- resources/js/Layouts/AppLayout.vue -->
<template>
    <div class="min-h-screen bg-gray-50 flex">
        <!-- Sidebar Component -->
        <Sidebar :open="sidebarOpen" @close="sidebarOpen = false" />
        
        <!-- Overlay for mobile -->
        <div v-if="sidebarOpen" @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden">
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col ">
            <!-- Navbar Component -->
            <Navbar @toggle-sidebar="sidebarOpen = !sidebarOpen" />
            
            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-4 sm:p-6 lg:p-8">
                <!-- Page Heading -->
                <div v-if="$slots.header" class="mb-6">
                    <slot name="header" />
                </div>
                
                <!-- Alerts/Session Messages -->
                <AlertMessages />
                
                <!-- Main Content Slot -->
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import Sidebar from '@/Layouts/Sidebar.vue';
import Navbar from '@/Layouts/Navbar.vue';
import AlertMessages from '@/Layouts/AlertMessages.vue';

const sidebarOpen = ref(false);

// Close sidebar on escape key
const handleEscape = (e) => {
    if (e.key === 'Escape' && sidebarOpen.value) {
        sidebarOpen.value = false;
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleEscape);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleEscape);
});
</script>