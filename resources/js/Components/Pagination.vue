<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import type { Paginated } from '@/types';

const props = defineProps<{ paginator: Paginated<unknown>; path: string; query?: Record<string, unknown> }>();

function url(page: number): string {
    const params = new URLSearchParams();
    for (const [key, value] of Object.entries(props.query ?? {})) {
        if (value === undefined || value === null || value === '' || value === false) continue;
        if (Array.isArray(value)) value.forEach((v) => params.append(`${key}[]`, String(v)));
        else params.set(key, String(value));
    }
    if (page > 1) params.set('page', String(page));
    const qs = params.toString();
    return qs ? `${props.path}?${qs}` : props.path;
}

/** 1 … 4 5 [6] 7 8 … 20 */
const pages = computed<(number | '…')[]>(() => {
    const { current_page: current, last_page: last } = props.paginator;
    const set = new Set<number>([1, last, current, current - 1, current + 1, current - 2, current + 2]);
    const sorted = [...set].filter((n) => n >= 1 && n <= last).sort((a, b) => a - b);
    const out: (number | '…')[] = [];
    sorted.forEach((n, i) => {
        if (i > 0 && n - sorted[i - 1] > 1) out.push('…');
        out.push(n);
    });
    return out;
});

const base = 'grid size-10 place-items-center rounded-xl border text-sm font-semibold transition';
</script>

<template>
    <nav v-if="paginator.last_page > 1" :aria-label="$t('Pagination')" class="mt-10 flex flex-wrap items-center justify-center gap-2">
        <Link
            v-if="paginator.current_page > 1"
            :href="url(paginator.current_page - 1)"
            :class="[base, 'border-line bg-surface text-ink-soft hover:border-primary hover:text-primary']"
            :aria-label="$t('Previous')"
            preserve-scroll
        >
            <ChevronLeft class="rtl-flip size-4" />
        </Link>
        <template v-for="(p, i) in pages" :key="i">
            <span v-if="p === '…'" class="px-1 text-muted">…</span>
            <Link
                v-else
                :href="url(p)"
                :class="[base, p === paginator.current_page ? 'border-primary bg-primary text-on-primary' : 'border-line bg-surface text-ink-soft hover:border-primary hover:text-primary']"
                :aria-current="p === paginator.current_page ? 'page' : undefined"
            >
                <span class="num">{{ p }}</span>
            </Link>
        </template>
        <Link
            v-if="paginator.current_page < paginator.last_page"
            :href="url(paginator.current_page + 1)"
            :class="[base, 'border-line bg-surface text-ink-soft hover:border-primary hover:text-primary']"
            :aria-label="$t('Next')"
        >
            <ChevronRight class="rtl-flip size-4" />
        </Link>
    </nav>
</template>
