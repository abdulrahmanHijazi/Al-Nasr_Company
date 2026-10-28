<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { slugOf, tr } from '@/lib/locale';
import type { Brand } from '@/types';
import SmartImage from '@/Components/SmartImage.vue';

const props = withDefaults(defineProps<{ brand: Brand; compact?: boolean }>(), { compact: false });

const href = computed(() => `/brands/${slugOf(props.brand.slug, props.brand.id)}`);
const name = computed(() => tr(props.brand.name));
</script>

<template>
    <Link :href="href" class="card card-hover group flex h-full flex-col items-center text-center" :class="compact ? 'p-4' : 'p-6'">
        <div class="grid w-full place-items-center" :class="compact ? 'h-14' : 'h-20'">
            <SmartImage
                v-if="brand.logo_url"
                :src="brand.logo_url"
                :alt="name"
                :seed="name"
                fit="contain"
                ratio="auto"
                class="h-full w-full !bg-transparent"
            />
            <span v-else class="text-xl font-bold tracking-tight text-ink-soft transition group-hover:text-primary">{{ name }}</span>
        </div>
        <template v-if="!compact">
            <h3 v-if="brand.logo_url" class="mt-3 font-semibold text-ink group-hover:text-primary">{{ name }}</h3>
            <p v-if="brand.description" class="mt-2 line-clamp-2 text-sm text-muted">{{ tr(brand.description) }}</p>
            <p v-if="brand.products_count !== undefined" class="mt-auto pt-4 text-xs font-semibold uppercase tracking-wide text-primary rtl:tracking-normal">
                {{ $t(':count products', { count: String(brand.products_count) }) }}
            </p>
        </template>
    </Link>
</template>
