<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';

defineProps<{
    eyebrow?: string;
    title: string;
    subtitle?: string;
    href?: string;
    linkLabel?: string;
    dark?: boolean;
}>();
</script>

<template>
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4 sm:mb-10">
        <div class="max-w-2xl">
            <p v-if="eyebrow" class="eyebrow" :class="dark && '!text-secondary'">{{ eyebrow }}</p>
            <h2 class="mt-2 text-2xl font-bold leading-tight tracking-tight sm:text-3xl" :class="dark ? 'text-white' : 'text-ink'">
                {{ title }}
            </h2>
            <p v-if="subtitle" class="mt-2 text-base leading-relaxed" :class="dark ? 'text-white/70' : 'text-muted'">{{ subtitle }}</p>
        </div>
        <Link
            v-if="href"
            :href="href"
            class="group inline-flex items-center gap-1.5 text-sm font-semibold"
            :class="dark ? 'text-secondary' : 'text-primary'"
        >
            {{ linkLabel ?? $t('View all') }}
            <ArrowRight class="rtl-flip size-4 transition group-hover:translate-x-1 rtl:group-hover:-translate-x-1" />
        </Link>
    </div>
</template>
