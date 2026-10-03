<script setup>
defineProps({
    message: {
        type: String,
    },
});
</script>

<template>
    <p v-show="message" class="border-s-2 border-error ps-3 text-sm text-base-content">{{ message }}</p>
</template>
