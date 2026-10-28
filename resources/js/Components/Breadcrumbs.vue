<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';

export interface Crumb {
    label: string;
    href?: string;
}

defineProps<{ crumbs: Crumb[] }>();
</script>

<template>
    <nav :aria-label="$t('Breadcrumb')" class="border-b border-line bg-surface">
        <ol class="container-page flex flex-wrap items-center gap-1.5 py-3.5 text-sm text-muted">
            <li><Link href="/" class="transition hover:text-primary">{{ $t('Home') }}</Link></li>
            <li v-for="(crumb, i) in crumbs" :key="i" class="flex items-center gap-1.5">
                <ChevronRight class="rtl-flip size-3.5 opacity-60" />
                <Link v-if="crumb.href" :href="crumb.href" class="transition hover:text-primary">{{ crumb.label }}</Link>
                <span v-else class="line-clamp-1 font-medium text-ink" aria-current="page">{{ crumb.label }}</span>
            </li>
        </ol>
    </nav>
</template>
