<template>
    <div v-if="show" class="fixed top-4 right-4 z-50 max-w-md">
        <div :class="[
            'rounded-lg shadow-lg p-4 mb-3 transform transition-all duration-300',
            type === 'success' ? 'bg-green-50 border border-green-200' :
            type === 'error' ? 'bg-red-50 border border-red-200' :
            type === 'warning' ? 'bg-yellow-50 border border-yellow-200' :
            'bg-blue-50 border border-blue-200'
        ]">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg :class="[
                        'w-5 h-5',
                        type === 'success' ? 'text-green-400' :
                        type === 'error' ? 'text-red-400' :
                        type === 'warning' ? 'text-yellow-400' :
                        'text-blue-400'
                    ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path v-if="type === 'success'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        <path v-if="type === 'error'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        <path v-if="type === 'warning'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        <path v-if="type === 'info'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h1m0-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <p :class="[
                        'text-sm font-medium',
                        type === 'success' ? 'text-green-800' :
                        type === 'error' ? 'text-red-800' :
                        type === 'warning' ? 'text-yellow-800' :
                        'text-blue-800'
                    ]">
                        {{ message }}
                    </p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button @click="close" class="inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
                        <span class="sr-only">Close</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    type: {
        type: String,
        default: 'success',
        validator: (value) => ['success', 'error', 'warning', 'info'].includes(value)
    },
    message: {
        type: String,
        required: true
    }
});

const show = ref(true);

const close = () => {
    show.value = false;
};

// Auto-close after 5 seconds
setTimeout(() => {
    show.value = false;
}, 5000);
</script>