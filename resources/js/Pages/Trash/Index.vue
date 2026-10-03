<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({ folders: Array, documents: Array, departments: Array });

function restore(type, id) {
    router.post(route('trash.restore', [type, id]));
}

function forceDelete(type, id, label) {
    if (window.confirm(`Hapus permanen "${label}"? Tindakan ini tidak bisa dibatalkan.`)) {
        router.delete(route('trash.force-delete', [type, id]));
    }
}
</script>

<template>
    <Head title="Sampah" />
    <AuthenticatedLayout>
        <div><h1 class="text-2xl font-semibold">Sampah</h1><p class="text-sm text-base-content/70">Item yang dihapus bisa dipulihkan atau dihapus permanen.</p></div>

        <section v-for="group in [
            { type: 'folders', title: 'Folder', items: folders },
            { type: 'documents', title: 'File', items: documents, label: 'original_name' },
            { type: 'departments', title: 'Departemen', items: departments },
        ]" :key="group.type" class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title">{{ group.title }}</h2>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Nama</th><th>Dihapus</th><th>Aksi</th></tr></thead>
                        <tbody>
                            <tr v-for="item in group.items" :key="item.id">
                                <td>{{ item[group.label || 'name'] }}</td>
                                <td>{{ new Date(item.deleted_at).toLocaleString('id-ID') }}</td>
                                <td class="space-x-2">
                                    <button class="btn btn-sm" type="button" @click="restore(group.type, item.id)">Pulihkan</button>
                                    <button class="btn btn-error btn-sm" type="button"
                                        @click="forceDelete(group.type, item.id, item[group.label || 'name'])">Hapus permanen</button>
                                </td>
                            </tr>
                            <tr v-if="!group.items.length"><td colspan="3" class="text-center text-base-content/60">Kosong.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
