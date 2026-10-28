<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Check, Clock, Tag } from 'lucide-vue-next';
import { money, slugOf, tr } from '@/lib/locale';
import type { Bundle, SharedProps } from '@/types';
import SmartImage from '@/Components/SmartImage.vue';

const props = defineProps<{ bundle: Bundle }>();

const page = usePage<SharedProps>();
const currency = computed(() => page.props.settings?.currency || 'USD');

const href = computed(() => `/offers/${slugOf(props.bundle.slug, props.bundle.id)}`);
const name = computed(() => tr(props.bundle.name));
const items = computed(() => props.bundle.products ?? []);

const original = computed(
    () =>
        props.bundle.original_total ??
        items.value.reduce((sum, p) => sum + Number(p.price) * Number(p.pivot?.qty ?? 1), 0),
);
const savings = computed(() => props.bundle.savings ?? Math.max(0, original.value - Number(props.bundle.bundle_price)));
const percent = computed(() => (original.value > 0 ? Math.round((savings.value / original.value) * 100) : 0));

const daysLeft = computed(() => {
    if (!props.bundle.ends_at) return null;
    const ms = new Date(props.bundle.ends_at).getTime() - Date.now();
    if (Number.isNaN(ms) || ms <= 0) return null;
    return Math.ceil(ms / 86_400_000);
});
</script>

<template>
    <Link :href="href" class="card card-hover group flex h-full flex-col overflow-hidden">
        <SmartImage :src="bundle.cover_url" :alt="name" :seed="name" ratio="16 / 10" class="[&_img]:group-hover:scale-105">
            <span v-if="percent" class="badge absolute start-3 top-3 gap-1.5 bg-secondary px-3 py-1.5 text-sm text-on-secondary shadow">
                <Tag class="size-3.5" />{{ $t('Save :percent%', { percent: String(percent) }) }}
            </span>
            <span v-if="daysLeft" class="badge absolute end-3 top-3 gap-1 bg-ink/80 text-white backdrop-blur">
                <Clock class="size-3" />{{ $t(':days days left', { days: String(daysLeft) }) }}
            </span>
        </SmartImage>

        <div class="flex flex-1 flex-col p-5">
            <h3 class="text-lg font-bold leading-snug text-ink group-hover:text-primary">{{ name }}</h3>
            <p v-if="bundle.description" class="mt-1 line-clamp-2 text-sm text-muted">{{ tr(bundle.description) }}</p>

            <ul v-if="items.length" class="mt-4 space-y-1.5 text-sm text-ink-soft">
                <li v-for="p in items.slice(0, 3)" :key="p.id" class="flex items-start gap-2">
                    <Check class="mt-0.5 size-4 shrink-0 text-success" />
                    <span class="line-clamp-1">
                        <span v-if="(p.pivot?.qty ?? 1) > 1" class="num font-semibold">{{ p.pivot?.qty }}× </span>{{ tr(p.name) }}
                    </span>
                </li>
                <li v-if="items.length > 3" class="ps-6 text-xs font-medium text-muted">
                    {{ $t('+ :count more items', { count: String(items.length - 3) }) }}
                </li>
            </ul>

            <div class="mt-auto flex items-end justify-between gap-3 border-t border-line pt-4">
                <div>
                    <p class="num text-2xl font-bold text-primary">{{ money(bundle.bundle_price, currency) }}</p>
                    <p v-if="savings > 0" class="num text-sm text-muted line-through">{{ money(original, currency) }}</p>
                </div>
                <span v-if="savings > 0" class="badge bg-success/10 text-success">
                    {{ $t('You save :amount', { amount: money(savings, currency) }) }}
                </span>
            </div>
        </div>
    </Link>
</template>
