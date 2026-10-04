<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import FileTypeBadge from '@/Components/FileTypeBadge.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    documents: Object,
    filters: Object,
    departments: Array,
    folders: Array,
    canManage: Boolean,
});

const search = ref(props.filters.search || '');
const departmentId = ref(props.filters.department_id || '');
const upload = useForm({ title: '', department_id: '', folder_id: '', file: null });
const uploadInput = ref(null);
const isDraggingOver = ref(false);

function pickFile(file) {
    upload.file = file;
    if (!upload.title) upload.title = file.name.replace(/\.[^.]+$/, '');
}

function onDrop(event) {
    isDraggingOver.value = false;
    const file = event.dataTransfer.files[0];
    if (file) pickFile(file);
}

function applyFilters() {
    router.get(route('documents.index'), {
        search: search.value || undefined,
        department_id: departmentId.value || undefined,
    }, { preserveState: true, replace: true });
}

function submitUpload() {
    upload.post(route('documents.store'), {
        forceFormData: true,
        onSuccess: () => {
            upload.reset();
            if (uploadInput.value) uploadInput.value.value = '';
        },
    });
}

function deleteDocument(file) {
    if (window.confirm(`Hapus file "${file.original_name}"?`)) {
        router.delete(route('documents.destroy', file.uuid));
    }
}
</script>

<template>
    <Head title="Dokumen" />
    <AuthenticatedLayout>
        <div><h1 class="text-2xl font-semibold">Dokumen</h1><p class="text-sm text-base-content/70">Cari, unduh, dan kelola file perusahaan.</p></div>

        <form v-if="canManage" class="card bg-base-100 shadow-sm" @submit.prevent="submitUpload">
            <div class="card-body">
                <h2 class="card-title">Upload file</h2>
                <p v-if="!departments.length || !folders.length" class="text-sm text-base-content/70">Buat departemen dan folder sebelum upload file.</p>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label><span class="mb-1 block text-sm">Judul</span><input v-model="upload.title" class="input w-full" type="text" required maxlength="255" /></label>
                    <label><span class="mb-1 block text-sm">Departemen</span>
                        <select v-model="upload.department_id" class="select w-full" required><option value="" disabled>Pilih departemen</option>
                            <option v-for="department in departments" :key="department.id" :value="department.id">{{ department.name }}</option>
                        </select>
                    </label>
                    <label><span class="mb-1 block text-sm">Folder</span>
                        <select v-model="upload.folder_id" class="select w-full" required><option value="" disabled>Pilih folder</option>
                            <option v-for="folder in folders" :key="folder.id" :value="folder.id">{{ folder.name }}</option>
                        </select>
                    </label>
                </div>

                <label class="flex cursor-pointer flex-col items-center gap-2 rounded-box border-2 border-dashed p-8 text-center transition-colors"
                    :class="isDraggingOver ? 'border-primary bg-primary/5' : 'border-base-300'"
                    @dragover.prevent="isDraggingOver = true" @dragleave.prevent="isDraggingOver = false" @drop.prevent="onDrop">
                    <span class="text-sm font-medium">{{ upload.file ? upload.file.name : 'Seret file ke sini, atau klik untuk pilih' }}</span>
                    <span class="text-xs text-base-content/60">PDF, gambar, Office, atau teks — maks. 20 MB</span>
                    <input ref="uploadInput" class="hidden" type="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.txt"
                        @change="pickFile($event.target.files[0])" />
                </label>

                <div class="card-actions justify-end"><button type="submit" class="btn btn-primary" :disabled="upload.processing || !upload.file || !departments.length || !folders.length">Upload</button></div>
            </div>
        </form>

        <section class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <form class="flex flex-wrap gap-2" @submit.prevent="applyFilters">
                    <input v-model="search" class="input min-w-48 flex-1" type="search" placeholder="Nama file, judul, atau departemen" />
                    <select v-model="departmentId" class="select"><option value="">Semua departemen</option>
                        <option v-for="department in departments" :key="department.id" :value="department.id">{{ department.name }}</option>
                    </select>
                    <button class="btn" type="submit">Cari</button>
                </form>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Nama file</th><th>Judul</th><th>Folder</th><th>Departemen</th><th>Upload</th><th></th></tr></thead>
                        <tbody>
                            <tr v-for="file in documents.data" :key="file.uuid">
                                <td class="flex items-center gap-2">
                                    <FileTypeBadge :name="file.original_name" />
                                    <Link :href="route('documents.show', file.uuid)" class="link font-medium">{{ file.original_name }}</Link>
                                </td>
                                <td>{{ file.title }}</td><td>{{ file.folder?.name }}</td><td>{{ file.department?.name }}</td>
                                <td>{{ new Date(file.created_at).toLocaleDateString('id-ID') }}</td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <a :href="route('documents.download', file.uuid)" class="btn btn-secondary btn-sm">Unduh</a>
                                        <button v-if="canManage" type="button" class="btn btn-error btn-sm" @click="deleteDocument(file)">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!documents.data.length"><td colspan="6" class="text-center text-base-content/60">Tidak ada file yang cocok.</td></tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="documents.links.length > 3" class="flex flex-wrap gap-1">
                    <Link v-for="(link, index) in documents.links" :key="index" :href="link.url || '#'"
                        class="btn btn-sm" :class="link.active ? 'btn-primary' : ''" :aria-disabled="!link.url"
                        v-html="link.label" />
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
