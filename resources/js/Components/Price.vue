<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { discountPercent, money } from '@/lib/locale';
import type { SharedProps } from '@/types';

const props = withDefaults(
    defineProps<{
        amount: string | number;
        compare?: string | number | null;
        size?: 'sm' | 'md' | 'lg' | 'xl';
        showDiscount?: boolean;
    }>(),
    { size: 'md', showDiscount: false },
);

const page = usePage<SharedProps>();
const currency = computed(() => page.props.settings?.currency || 'USD');
const hasCompare = computed(() => Number(props.compare ?? 0) > Number(props.amount));
const percent = computed(() => discountPercent(props.amount, props.compare));

const sizes = {
    sm: 'text-base',
    md: 'text-lg',
    lg: 'text-2xl',
    xl: 'text-4xl',
} as const;
</script>

<template>
    <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
        <span class="num font-bold text-ink" :class="sizes[size]">{{ money(amount, currency) }}</span>
        <span v-if="hasCompare" class="num text-sm text-muted line-through">{{ money(compare, currency) }}</span>
        <span v-if="hasCompare && showDiscount && percent" class="badge num bg-secondary-100 text-secondary-600">−{{ percent }}%</span>
    </div>
</template>
