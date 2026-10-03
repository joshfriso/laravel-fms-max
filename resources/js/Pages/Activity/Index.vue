<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ logs: Object });
</script>

<template>
    <Head title="Aktivitas" />
    <AuthenticatedLayout>
        <div><h1 class="text-2xl font-semibold">Aktivitas</h1><p class="text-sm text-base-content/70">Riwayat perubahan folder, file, dan departemen.</p></div>

        <section class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Waktu</th><th>Pengguna</th><th>Aksi</th><th>Keterangan</th></tr></thead>
                        <tbody>
                            <tr v-for="log in logs.data" :key="log.id">
                                <td>{{ new Date(log.created_at).toLocaleString('id-ID') }}</td>
                                <td>{{ log.user?.name ?? 'Sistem' }}</td>
                                <td><span class="badge badge-ghost">{{ log.action }}</span></td>
                                <td>{{ log.description }}</td>
                            </tr>
                            <tr v-if="!logs.data.length"><td colspan="4" class="text-center text-base-content/60">Belum ada aktivitas.</td></tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="logs.links.length > 3" class="flex flex-wrap gap-1">
                    <Link v-for="(link, index) in logs.links" :key="index" :href="link.url || '#'"
                        class="btn btn-sm" :class="link.active ? 'btn-primary' : ''" :aria-disabled="!link.url"
                        v-html="link.label" />
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
