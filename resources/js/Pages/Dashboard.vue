<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import FileTypeBadge from '@/Components/FileTypeBadge.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    totalFolders: Number,
    totalFiles: Number,
    totalDepartments: Number,
    recentFiles: Array,
    fileTypes: Array,
});

const page = usePage();
const search = ref('');
const recentlyEdited = props.recentFiles.slice(0, 3);
const maxFileTypeCount = Math.max(1, ...props.fileTypes.map((type) => type.count));

function submitSearch() {
    if (search.value.trim()) router.get(route('documents.index'), { search: search.value });
}
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">Welcome Back, {{ page.props.auth.user.name }}</h1>
                <p class="text-sm text-base-content/70">Ringkasan dokumen perusahaan.</p>
            </div>
            <form class="join" @submit.prevent="submitSearch">
                <input v-model="search" type="search" placeholder="Search your files" class="input join-item w-64" />
                <button class="btn btn-primary join-item" type="submit">Cari</button>
            </form>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div v-for="item in [
                { label: 'Total folder', value: totalFolders, href: route('folders.index') },
                { label: 'Total file', value: totalFiles, href: route('documents.index') },
                { label: 'Total departemen', value: totalDepartments, href: route('departments.index') },
            ]" :key="item.label" class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <p class="text-sm text-base-content/70">{{ item.label }}</p>
                    <p class="text-3xl font-semibold">{{ item.value }}</p>
                    <Link :href="item.href" class="link text-sm">Lihat</Link>
                </div>
            </div>
        </div>

        <section v-if="recentlyEdited.length" class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <h2 class="card-title">Recently Edited</h2>
                    <Link :href="route('documents.index')" class="link text-sm">View All</Link>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <Link v-for="file in recentlyEdited" :key="file.uuid" :href="route('documents.show', file.uuid)"
                        class="rounded-box border border-base-300 p-4 transition-colors hover:border-primary">
                        <div class="flex items-center justify-between">
                            <FileTypeBadge :name="file.original_name" />
                        </div>
                        <p class="mt-3 truncate font-medium">{{ file.title }}</p>
                        <p class="text-xs text-base-content/60">{{ file.uploader?.name }} · {{ new Date(file.created_at).toLocaleDateString('id-ID') }}</p>
                    </Link>
                </div>
            </div>
        </section>

        <section class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title">10 file terbaru</h2>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Nama file</th><th>Judul</th><th>Departemen</th><th>Diunggah oleh</th><th>Tanggal</th></tr></thead>
                        <tbody>
                            <tr v-for="file in recentFiles" :key="file.uuid">
                                <td class="flex items-center gap-2">
                                    <FileTypeBadge :name="file.original_name" />
                                    <Link :href="route('documents.show', file.uuid)" class="link">{{ file.original_name }}</Link>
                                </td>
                                <td>{{ file.title }}</td><td>{{ file.department?.name }}</td><td>{{ file.uploader?.name }}</td>
                                <td>{{ new Date(file.created_at).toLocaleDateString('id-ID') }}</td>
                            </tr>
                            <tr v-if="!recentFiles.length"><td colspan="5" class="text-center text-base-content/60">Belum ada file.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <template #aside>
            <div class="card bg-neutral text-neutral-content shadow-sm">
                <div class="card-body">
                    <h2 class="card-title">File Type</h2>
                    <ul class="space-y-4">
                        <li v-for="type in fileTypes" :key="type.label">
                            <div class="flex items-center justify-between text-sm">
                                <span>{{ type.label }}</span>
                                <span class="text-neutral-content/60">{{ type.count }}</span>
                            </div>
                            <progress class="progress progress-primary mt-1 w-full" :value="type.count" :max="maxFileTypeCount" />
                        </li>
                    </ul>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
