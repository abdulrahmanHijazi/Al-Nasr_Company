<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';

export interface Crumb {
    label: string;
    href?: string;
}

defineProps<{
    title: string;
    eyebrow?: string;
    description?: string;
    crumbs?: Crumb[];
    /** optional banner image behind the header (category/brand covers) */
    image?: string | null;
}>();
</script>

<template>
    <section class="relative isolate overflow-hidden bg-header text-on-header">
        <img v-if="image" :src="image" alt="" class="absolute inset-0 -z-10 h-full w-full object-cover opacity-30" />
        <div class="bg-grid absolute inset-0 -z-10" aria-hidden="true" />
        <div class="absolute -end-24 -top-24 -z-10 size-72 rounded-full bg-secondary/20 blur-3xl" aria-hidden="true" />

        <div class="container-page py-10 sm:py-14">
            <nav v-if="crumbs?.length" :aria-label="$t('Breadcrumb')" class="mb-5">
                <ol class="flex flex-wrap items-center gap-1.5 text-sm text-white/65">
                    <li class="flex items-center gap-1.5">
                        <Link href="/" class="transition hover:text-white">{{ $t('Home') }}</Link>
                    </li>
                    <li v-for="(crumb, i) in crumbs" :key="i" class="flex items-center gap-1.5">
                        <ChevronRight class="rtl-flip size-3.5 opacity-60" />
                        <Link v-if="crumb.href" :href="crumb.href" class="transition hover:text-white">{{ crumb.label }}</Link>
                        <span v-else class="font-medium text-white" aria-current="page">{{ crumb.label }}</span>
                    </li>
                </ol>
            </nav>

            <p v-if="eyebrow" class="eyebrow !text-secondary">{{ eyebrow }}</p>
            <h1 class="mt-2 max-w-3xl text-3xl font-bold leading-tight tracking-tight sm:text-4xl">{{ title }}</h1>
            <p v-if="description" class="mt-3 max-w-2xl text-base leading-relaxed text-white/70 sm:text-lg">{{ description }}</p>
            <div class="mt-5 empty:hidden"><slot /></div>
        </div>
    </section>
</template>
