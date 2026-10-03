<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen bg-base-200">
        <div class="hidden flex-1 flex-col justify-between bg-neutral p-10 text-neutral-content lg:flex">
            <Link href="/" class="flex items-center gap-3">
                <ApplicationLogo class="h-10 w-10 fill-current text-primary" />
                <span class="text-xl font-bold">File Management System</span>
            </Link>
            <div class="max-w-sm space-y-3">
                <h2 class="text-3xl font-semibold leading-tight">File Management System</h2>
                <p class="text-neutral-content/70">Kelola folder, dokumen, departemen, dan riwayat aktivitas.</p>
            </div>
            <p class="text-sm text-neutral-content/50">&copy; 2026 Josh Verstappen Friso</p>
        </div>

        <div class="flex flex-1 items-center justify-center p-6">
            <div class="w-full max-w-sm">
                <Link href="/" class="mb-8 flex items-center gap-3 lg:hidden">
                    <ApplicationLogo class="h-9 w-9 fill-current text-primary" />
                    <span class="text-lg font-bold">File Management System</span>
                </Link>
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <slot />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
