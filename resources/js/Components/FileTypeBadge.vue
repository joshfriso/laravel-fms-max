<script setup>
import { computed } from 'vue';

const props = defineProps({ name: { type: String, required: true } });

const KNOWN_TYPES = {
    pdf: { label: 'PDF', class: 'badge-error' },
    doc: { label: 'DOC', class: 'badge-info' },
    docx: { label: 'DOC', class: 'badge-info' },
    xls: { label: 'XLS', class: 'badge-success' },
    xlsx: { label: 'XLS', class: 'badge-success' },
    jpg: { label: 'IMG', class: 'badge-secondary' },
    jpeg: { label: 'IMG', class: 'badge-secondary' },
    png: { label: 'IMG', class: 'badge-secondary' },
    txt: { label: 'TXT', class: 'badge-ghost' },
};

const type = computed(() => {
    const extension = props.name.split('.').pop()?.toLowerCase();

    return KNOWN_TYPES[extension] ?? { label: extension?.toUpperCase() || 'FILE', class: 'badge-ghost' };
});
</script>

<template>
    <span class="badge badge-sm" :class="type.class">{{ type.label }}</span>
</template>
