<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ArrowUpRight } from 'lucide-vue-next';
import { slugOf, tr } from '@/lib/locale';
import type { Category } from '@/types';
import SmartImage from '@/Components/SmartImage.vue';

const props = defineProps<{ category: Category }>();

const href = computed(() => `/categories/${slugOf(props.category.slug, props.category.id)}`);
const name = computed(() => tr(props.category.name));
</script>

<template>
    <Link :href="href" class="group relative block overflow-hidden rounded-[var(--radius-card)] shadow-[var(--shadow-card)]">
        <SmartImage :src="category.image_url" :alt="name" :seed="name" ratio="5 / 4" class="[&_img]:group-hover:scale-105">
            <div class="absolute inset-0 bg-gradient-to-t from-header/90 via-header/30 to-transparent" />
            <div class="absolute inset-x-0 bottom-0 flex items-end justify-between gap-3 p-4 sm:p-5">
                <div class="min-w-0">
                    <h3 class="truncate text-lg font-bold text-white">{{ name }}</h3>
                    <p v-if="category.products_count !== undefined" class="text-sm text-white/75">
                        {{ $t(':count products', { count: String(category.products_count) }) }}
                    </p>
                </div>
                <span class="grid size-9 shrink-0 place-items-center rounded-full bg-white/15 text-white backdrop-blur transition group-hover:bg-secondary group-hover:text-on-secondary">
                    <ArrowUpRight class="rtl-flip size-4" />
                </span>
            </div>
        </SmartImage>
    </Link>
</template>
