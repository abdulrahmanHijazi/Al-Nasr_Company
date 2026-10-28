<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ArrowUpRight } from 'lucide-vue-next';
import { formatDate, slugOf, stripHtml, tr } from '@/lib/locale';
import type { PortfolioItem } from '@/types';
import SmartImage from '@/Components/SmartImage.vue';

const props = withDefaults(defineProps<{ item: PortfolioItem; ratio?: string }>(), { ratio: '4 / 3' });

const href = computed(() => `/portfolio/${slugOf(props.item.slug, props.item.id)}`);
const title = computed(() => tr(props.item.title));
</script>

<template>
    <Link :href="href" class="card card-hover group flex h-full flex-col overflow-hidden">
        <SmartImage :src="item.cover_url" :alt="title" :seed="title" :ratio="ratio" class="[&_img]:group-hover:scale-105">
            <span class="absolute end-3 top-3 grid size-9 place-items-center rounded-full bg-white/90 text-ink opacity-0 shadow transition group-hover:opacity-100">
                <ArrowUpRight class="rtl-flip size-4" />
            </span>
        </SmartImage>
        <div class="flex flex-1 flex-col p-5">
            <p v-if="item.client_name || item.completed_at" class="text-xs font-semibold uppercase tracking-wide text-primary rtl:tracking-normal">
                <template v-if="item.client_name">{{ tr(item.client_name) }}</template>
                <template v-if="item.client_name && item.completed_at"> · </template>
                <template v-if="item.completed_at">{{ formatDate(item.completed_at, { year: 'numeric' }) }}</template>
            </p>
            <h3 class="mt-1.5 text-lg font-bold leading-snug text-ink group-hover:text-primary">{{ title }}</h3>
            <p v-if="item.description" class="mt-2 line-clamp-2 text-sm text-muted">{{ stripHtml(tr(item.description)) }}</p>
        </div>
    </Link>
</template>
