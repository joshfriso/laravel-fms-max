<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({ document: Object, canPreview: Boolean, canManage: Boolean, departments: Array, folders: Array });
const form = useForm({
    title: props.document.title,
    department_id: props.document.department_id,
    folder_id: props.document.folder_id,
});
const previewUrl = route('documents.preview', props.document.uuid);

function deleteDocument() {
    if (window.confirm(`Hapus file "${props.document.original_name}"?`)) {
        router.delete(route('documents.destroy', props.document.uuid));
    }
}
</script>

<template>
    <Head :title="document.title" />
    <AuthenticatedLayout>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div><Link :href="route('documents.index')" class="link text-sm">← Semua dokumen</Link>
                <h1 class="text-2xl font-semibold">{{ document.title }}</h1>
            </div>
            <a :href="route('documents.download', document.uuid)" class="btn btn-secondary">Unduh file</a>
        </div>

        <section v-if="canPreview" class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title">Pratinjau</h2>
                <img v-if="document.mime_type.startsWith('image/')" :src="previewUrl"
                    :alt="document.title" class="max-h-[32rem] rounded-field object-contain" />
                <object v-else :data="previewUrl" :type="document.mime_type"
                    class="h-[32rem] w-full rounded-field border border-base-300">
                    <div class="flex h-full flex-col items-center justify-center gap-3 p-6 text-center">
                        <p class="text-sm text-base-content/70">Pratinjau tidak terbuka di browser ini.</p>
                        <a :href="previewUrl" target="_blank" rel="noopener" class="btn btn-sm btn-outline">Buka pratinjau</a>
                    </div>
                </object>
            </div>
        </section>

        <section class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title">Detail file</h2>
                <dl class="grid gap-4 sm:grid-cols-2">
                    <div><dt class="text-sm text-base-content/60">Nama file</dt><dd>{{ document.original_name }}</dd></div>
                    <div><dt class="text-sm text-base-content/60">Judul</dt><dd>{{ document.title }}</dd></div>
                    <div><dt class="text-sm text-base-content/60">Folder</dt><dd><Link :href="route('folders.show', document.folder_id)" class="link">{{ document.folder?.name }}</Link></dd></div>
                    <div><dt class="text-sm text-base-content/60">Departemen</dt><dd>{{ document.department?.name }}</dd></div>
                    <div><dt class="text-sm text-base-content/60">Uploaded by</dt><dd>{{ document.uploader?.name }}</dd></div>
                    <div><dt class="text-sm text-base-content/60">Upload date</dt><dd>{{ new Date(document.created_at).toLocaleString('id-ID') }}</dd></div>
                </dl>
            </div>
        </section>

        <form v-if="canManage" class="card bg-base-100 shadow-sm" @submit.prevent="form.patch(route('documents.update', document.uuid))">
            <div class="card-body">
                <h2 class="card-title">Ubah informasi file</h2>
                <div class="grid gap-3 sm:grid-cols-3">
                    <label><span class="mb-1 block text-sm">Judul</span><input v-model="form.title" class="input w-full" required maxlength="255" /></label>
                    <label><span class="mb-1 block text-sm">Departemen</span><select v-model="form.department_id" class="select w-full" required>
                        <option v-for="department in departments" :key="department.id" :value="department.id">{{ department.name }}</option>
                    </select></label>
                    <label><span class="mb-1 block text-sm">Folder</span><select v-model="form.folder_id" class="select w-full" required>
                        <option v-for="folder in folders" :key="folder.id" :value="folder.id">{{ folder.name }}</option>
                    </select></label>
                </div>
                <div class="card-actions justify-between">
                    <button class="btn btn-error" type="button" @click="deleteDocument">Hapus file</button>
                    <button class="btn btn-primary" type="submit" :disabled="form.processing">Simpan</button>
                </div>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
