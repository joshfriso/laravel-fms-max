<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import NavIcon from '@/Components/NavIcon.vue';

const page = usePage();
const currentTheme = ref(document.documentElement.dataset.theme || 'fileno');
const isDark = computed(() => currentTheme.value === 'fileno-dark');

function toggleTheme() {
    const theme = isDark.value ? 'fileno' : 'fileno-dark';
    currentTheme.value = theme;
    document.documentElement.dataset.theme = theme;
    window.localStorage.setItem('fms-theme', theme);
}
const navigation = computed(() => {
    const items = [
        { name: 'Dashboard', route: 'dashboard', icon: 'dashboard' },
        { name: 'Folder', route: 'folders.index', icon: 'folder' },
        { name: 'Dokumen', route: 'documents.index', icon: 'document' },
        { name: 'Departemen', route: 'departments.index', icon: 'department' },
    ];
    if (page.props.auth.user.role === 'administrator') {
        items.push(
            { name: 'Sampah', route: 'trash.index', icon: 'trash' },
            { name: 'Aktivitas', route: 'activity.index', icon: 'activity' },
        );
    }

    return items;
});
</script>

<template>
    <div class="min-h-screen bg-base-200">
        <header class="bg-neutral text-neutral-content">
            <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-8">
                <Link :href="route('dashboard')" class="text-xl font-bold">File Management</Link>

                <nav class="flex flex-wrap items-center gap-1" aria-label="Navigasi utama">
                    <Link v-for="item in navigation" :key="item.route" :href="route(item.route)"
                        class="flex items-center gap-2 rounded-field px-3 py-2 text-sm hover:bg-neutral-content/10"
                        :class="route().current(item.route) ? 'bg-primary text-primary-content' : 'text-neutral-content/80'">
                        <NavIcon :name="item.icon" />
                        <span class="hidden lg:inline">{{ item.name }}</span>
                    </Link>
                </nav>

                <div class="dropdown dropdown-end">
                    <button type="button" tabindex="0" class="flex items-center gap-2 rounded-field px-2 py-1.5 hover:bg-neutral-content/10">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-primary text-sm font-semibold text-primary-content">
                            {{ page.props.auth.user.name.slice(0, 1).toUpperCase() }}
                        </span>
                        <span class="hidden text-left sm:block">
                            <span class="block text-sm font-medium leading-tight">{{ page.props.auth.user.name }}</span>
                            <span class="block text-xs capitalize leading-tight text-neutral-content/60">{{ page.props.auth.user.role }}</span>
                        </span>
                    </button>
                    <ul tabindex="0" class="menu dropdown-content z-10 mt-2 w-48 rounded-box bg-base-100 p-2 text-base-content shadow-lg">
                        <li><button type="button" @click="toggleTheme"><span aria-hidden="true">{{ isDark ? '☀' : '☾' }}</span>{{ isDark ? 'Mode terang' : 'Mode gelap' }}</button></li>
                        <li><Link :href="route('profile.edit')"><NavIcon name="profile" />Profil</Link></li>
                        <li><Link :href="route('logout')" method="post" as="button"><NavIcon name="logout" />Keluar</Link></li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl p-4 sm:p-8">
            <div v-if="page.props.flash?.success" class="alert alert-success mb-5" role="status">{{ page.props.flash.success }}</div>
            <div v-if="Object.keys(page.props.errors || {}).length" class="alert alert-error mb-5" role="alert">
                <ul class="list-disc pl-5"><li v-for="(message, field) in page.props.errors" :key="field">{{ message }}</li></ul>
            </div>

            <div class="grid gap-5" :class="$slots.aside ? 'lg:grid-cols-3' : ''">
                <div class="space-y-5" :class="$slots.aside ? 'lg:col-span-2' : ''">
                    <slot />
                </div>
                <aside v-if="$slots.aside" class="space-y-5">
                    <slot name="aside" />
                </aside>
            </div>
        </main>
    </div>
</template>
