<!-- resources/js/Components/Pagination.vue -->
<template>
    <div v-if="links.length > 3">
        <div class="flex flex-wrap -mb-1">
            <template v-for="(link, index) in links" :key="`pagination-${index}`">
                <div v-if="link.url === null" 
                     class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded" 
                     v-html="link.label" />
                <Link v-else 
                      class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-white focus:border-blue-500 focus:text-blue-500" 
                      :class="{ 'bg-blue-500 text-white': link.active }" 
                      :href="link.url" 
                      v-html="link.label" />
            </template>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        default: () => [],
    },
});
</script>