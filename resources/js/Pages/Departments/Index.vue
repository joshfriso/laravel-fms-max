<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

defineProps({ departments: Object, canManage: Boolean });
const form = useForm({ name: '' });
const createDepartment = () => form.post(route('departments.store'), { onSuccess: () => form.reset() });

function renameDepartment(department) {
    const name = window.prompt('Nama departemen baru', department.name)?.trim();
    if (name && name !== department.name) router.patch(route('departments.update', department.id), { name });
}

function deleteDepartment(department) {
    if (window.confirm(`Hapus departemen "${department.name}"?`)) {
        router.delete(route('departments.destroy', department.id));
    }
}
</script>

<template>
    <Head title="Departemen" />
    <AuthenticatedLayout>
        <div><h1 class="text-2xl font-semibold">Departemen</h1><p class="text-sm text-base-content/70">Metadata untuk setiap file.</p></div>

        <form v-if="canManage" class="card bg-base-100 shadow-sm" @submit.prevent="createDepartment">
            <div class="card-body gap-3 sm:flex-row sm:items-end">
                <label class="flex-1"><span class="mb-1 block text-sm">Departemen baru</span>
                    <input v-model="form.name" class="input w-full" type="text" required maxlength="255" placeholder="Nama departemen" />
                </label>
                <button class="btn btn-primary" type="submit" :disabled="form.processing">Tambah</button>
            </div>
        </form>

        <section class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Nama</th><th>Jumlah file</th><th v-if="canManage">Aksi</th></tr></thead>
                        <tbody>
                            <tr v-for="department in departments.data" :key="department.id">
                                <td>{{ department.name }}</td><td>{{ department.documents_count }}</td>
                                <td v-if="canManage" class="space-x-2">
                                    <button class="btn btn-sm" type="button" @click="renameDepartment(department)">Ubah nama</button>
                                    <button class="btn btn-error btn-sm" type="button" @click="deleteDepartment(department)">Hapus</button>
                                </td>
                            </tr>
                            <tr v-if="!departments.data.length"><td :colspan="canManage ? 3 : 2" class="text-center text-base-content/60">Belum ada departemen.</td></tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="departments.links.length > 3" class="flex flex-wrap gap-1">
                    <Link v-for="(link, index) in departments.links" :key="index" :href="link.url || '#'"
                        class="btn btn-sm" :class="link.active ? 'btn-primary' : ''" :aria-disabled="!link.url"
                        v-html="link.label" />
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
