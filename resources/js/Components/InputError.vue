<!-- resources/js/Components/InputError.vue -->
<template>
    <p v-show="message" class="text-sm text-red-600">
        {{ message }}
    </p>
</template>

<script setup>
defineProps({
    message: {
        type: String,
        default: '',
    },
});
</script>