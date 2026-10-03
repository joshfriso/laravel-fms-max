<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import FileTypeBadge from '@/Components/FileTypeBadge.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    currentFolder: Object,
    breadcrumbs: Array,
    folders: Array,
    documents: Object,
    canManage: Boolean,
});

const form = useForm({ name: '', parent_id: props.currentFolder?.id ?? null });
const createFolder = () => form.post(route('folders.store'), { onSuccess: () => form.reset('name') });

function renameFolder(folder) {
    const name = window.prompt('Nama folder baru', folder.name)?.trim();
    if (name && name !== folder.name) {
        router.patch(route('folders.update', folder.id), { name, parent_id: folder.parent_id });
    }
}

function deleteFolder(folder) {
    if (window.confirm(`Hapus folder "${folder.name}"? Folder harus kosong.`)) {
        router.delete(route('folders.destroy', folder.id));
    }
}
</script>

<template>
    <Head title="Folder" />
    <AuthenticatedLayout>
        <div>
            <h1 class="text-2xl font-semibold">Folder</h1>
            <div class="breadcrumbs text-sm">
                <ul>
                    <li><Link :href="route('folders.index')">Root</Link></li>
                    <li v-for="part in breadcrumbs" :key="part.id"><Link :href="route('folders.show', part.id)">{{ part.name }}</Link></li>
                </ul>
            </div>
        </div>

        <form v-if="canManage" class="card bg-base-100 shadow-sm" @submit.prevent="createFolder">
            <div class="card-body gap-3 sm:flex-row sm:items-end">
                <label class="form-control flex-1">
                    <span class="mb-1 block text-sm">Folder baru {{ currentFolder ? `di ${currentFolder.name}` : 'di root' }}</span>
                    <input v-model="form.name" class="input w-full" type="text" required maxlength="255" placeholder="Nama folder" />
                </label>
                <button class="btn btn-primary" type="submit" :disabled="form.processing">Buat folder</button>
            </div>
        </form>

        <section class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title">Subfolder</h2>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Nama</th><th>Dibuat</th><th v-if="canManage">Aksi</th></tr></thead>
                        <tbody>
                            <tr v-for="folder in folders" :key="folder.id">
                                <td><Link :href="route('folders.show', folder.id)" class="link font-medium">{{ folder.name }}</Link></td>
                                <td>{{ new Date(folder.created_at).toLocaleDateString('id-ID') }}</td>
                                <td v-if="canManage" class="space-x-2">
                                    <button type="button" class="btn btn-sm" @click="renameFolder(folder)">Ubah nama</button>
                                    <button type="button" class="btn btn-error btn-sm" @click="deleteFolder(folder)">Hapus</button>
                                </td>
                            </tr>
                            <tr v-if="!folders.length"><td :colspan="canManage ? 3 : 2" class="text-center text-base-content/60">Belum ada subfolder.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section v-if="currentFolder" class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="card-title">File di folder ini</h2>
                    <Link v-if="canManage" :href="route('documents.index')" class="btn btn-sm">Upload file</Link>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Nama file</th><th>Judul</th><th>Departemen</th><th>Tanggal</th></tr></thead>
                        <tbody>
                            <tr v-for="file in documents?.data || []" :key="file.uuid">
                                <td class="flex items-center gap-2">
                                    <FileTypeBadge :name="file.original_name" />
                                    <Link :href="route('documents.show', file.uuid)" class="link">{{ file.original_name }}</Link>
                                </td>
                                <td>{{ file.title }}</td><td>{{ file.department?.name }}</td>
                                <td>{{ new Date(file.created_at).toLocaleDateString('id-ID') }}</td>
                            </tr>
                            <tr v-if="!documents?.data?.length"><td colspan="4" class="text-center text-base-content/60">Belum ada file.</td></tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="documents?.links?.length > 3" class="flex flex-wrap gap-1">
                    <Link v-for="(link, index) in documents.links" :key="index" :href="link.url || '#'"
                        class="btn btn-sm" :class="link.active ? 'btn-primary' : ''" :aria-disabled="!link.url"
                        v-html="link.label" />
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
